<?php
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) require_once __DIR__ . '/../apiHeadSecure.php'; //Only if it wasn't included from somewhere else
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;
use Money\Formatter\IntlMoneyFormatter;


function calculateDiscountPercentage($asset)
{
    if ($asset['price']->getAmount() > 0) {
        return ($asset['price']->subtract($asset['discountPrice'])->getAmount() / $asset['price']->getAmount()) * 100;
    } else {
        return 0;
    }
}

$array = [];
if (isset($_POST['formData'])) {
    foreach ($_POST['formData'] as $item) {
        $array[$item['name']] = $item['value'];
        $_GET[$item['name']] = $item['value'];
    }
}
if (isset($_POST['id'])) $_GET['id'] = $_POST['id'];
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) finish(false);

//The project itself
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_GET['id']);
$DBLIB->join("projectsTypes", "projects.projectsTypes_id=projectsTypes.projectsTypes_id", "LEFT");
$DBLIB->join("clients", "projects.clients_id=clients.clients_id", "LEFT");
$DBLIB->join("users", "projects.projects_manager=users.users_userid", "LEFT");
$DBLIB->join("locations","locations.locations_id=projects.locations_id","LEFT");
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$PAGEDATA['project'] = $DBLIB->getone("projects", ["projects.*", "projectsTypes.*", "clients.clients_id", "clients_name","clients_website","clients_email","clients_notes","clients_address","clients_phone","users.users_userid", "users.users_name1", "users.users_name2", "users.users_email","locations.locations_name","locations.locations_address","projectsStatuses.projectsStatuses_id", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour","projectsStatuses.projectsStatuses_backgroundColour","projectsStatuses.projectsStatuses_assetsReleased","projectsStatuses.projectsStatuses_description"]);
if (!$PAGEDATA['project']) $PAGEDATA['USE_TWIG_404'] ? die($TWIG->render('404.twig', $PAGEDATA)) : die("404");

//subprojects
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_parent_project_id", $_GET['id']);
$DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
$PAGEDATA['project']['subProjects'] = $DBLIB->get("projects", null, ["projects.*", "projectsStatuses.projectsStatuses_name", "projectsStatuses.projectsStatuses_foregroundColour","projectsStatuses.projectsStatuses_backgroundColour"]);

//Finances

//Payments and also
function projectFinancials($project) {
    global $DBLIB,$AUTH,$bCMS;
    $projectFinanceHelper = new projectFinance();
    $return = [];

    //create a formatter for money
    $numberFormatter = new \NumberFormatter('hu_HU', \NumberFormatter::CURRENCY);
    $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

    $DBLIB->where("payments.payments_deleted", 0);
    $DBLIB->orderBy("payments.payments_date", "ASC");
    $DBLIB->where("payments.projects_id", $project['projects_id']);
    $payments = $DBLIB->get("payments");
    $return['payments'] = [
        "received" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))],
        "sales" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))],
        "subHire" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))],
        "staff" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))],
        "truck" => ["ledger" => [], "total" => new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']))] // Új ledger hozzáadva
    ];
    foreach ($payments as $payment) {
        $payment['files'] = $bCMS->s3List(14, $payment['payments_id']);
        $key = false;
        switch ($payment['payments_type']) {
            case 1:
                $key = "received";
                break;
            case 2:
                $key = 'sales';
                break;
            case 3:
                $key = 'subHire';
                break;
            case 4:
                $key = 'staff';
                break;
            case 5:
                $key = 'staff';
                break;
            case 6:
                $key = 'truck';
                break;
        }
        if ($key) {
            $payment['payments_amount'] = new Money($payment['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
            $payment['payments_amountTotal'] = $payment['payments_amount']->multiply($payment['payments_quantity']);
            $return['payments'][$key]['total'] = $payment['payments_amountTotal']->add($return['payments'][$key]['total']);
            $return['payments'][$key]['ledger'][] = $payment;
        } else throw new Exception("Unknown payment type found");
    }

    //Assets
    $DBLIB->where("projects_id", $project['projects_id']);
    $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
    $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
    $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id=assetTypes.manufacturers_id", "LEFT");
    $DBLIB->join("assetCategories", "assetTypes.assetCategories_id=assetCategories.assetCategories_id", "LEFT");
    $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");
    $DBLIB->join("assetsAssignmentsStatus", "assetsAssignments.assetsAssignmentsStatus_id=assetsAssignmentsStatus.assetsAssignmentsStatus_id", "LEFT");

    $DBLIB->join("tempStockAssets", "tempStockAssets.tempStock_assets_id = assetTypes.assetTypes_id AND tempStockAssets.tempStock_project_id = {$project['projects_id']}", "LEFT");

    $DBLIB->orderBy("assetCategories.assetCategories_rank", "ASC");
    $DBLIB->orderBy("assetTypes.assetTypes_id", "ASC");
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $DBLIB->where("assets.assets_deleted", 0);
    $assets = $DBLIB->get("assetsAssignments", null, ["assetCategories.assetCategories_rank", "assetsAssignmentsStatus.assetsAssignmentsStatus_id", "assetsAssignmentsStatus.assetsAssignmentsStatus_order", "assetsAssignmentsStatus.assetsAssignmentsStatus_name", "assetsAssignments.*", "manufacturers.manufacturers_name", "assetTypes.*", "assets.*", "assetCategories.assetCategories_name", "assetCategories.assetCategories_fontAwesome", "assetCategoriesGroups.assetCategoriesGroups_name", "assets.instances_id", "tempStockAssets.tempStock_assets_quantity"]);



    $return['extra_assets'] = 0;
    $return['assetsAssigned'] = [];
    $return['assetsAssignedSUB'] = [];
    $return['mass'] = 0.0; //TODO evaluate whether using floats for mass is a good idea....
    $return['value'] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
    $return['prices'] = [
        "subTotal" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "subTotalSound" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "subTotalLight" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "subTotalProjection" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "subTotalStageStructure" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "subTotalStage" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "subTotalSafety" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),

        // Discounted prices for each category
        "soundDiscountPrice" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "lightDiscountPrice" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "projectionDiscountPrice" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "stageStructureDiscountPrice" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "stageDiscountPrice" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "safetyDiscountPrice" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),

        // Discount percentages for each category
        "soundDiscountPercentage" => 0,
        "lightDiscountPercentage" => 0,
        "projectionDiscountPercentage" => 0,
        "stageStructureDiscountPercentage" => 0,
        "stageDiscountPercentage" => 0,
        "safetyDiscountPercentage" => 0,

        // General discounts and total
        "discounts" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency'])),
        "total" => new Money(0, new Currency($AUTH->data['instance']['instances_config_currency']))
    ];

    $return['priceMaths'] = $projectFinanceHelper->durationMaths($project['projects_id']);

    foreach ($assets as $asset) {

        # Count how many items are assigned to this project from the same type
        $asset['count'] = count(array_filter($assets, function ($a) use ($asset) {
            return $a['assetTypes_id'] == $asset['assetTypes_id'] && $a['assetsAssignments_assignedAsSubAsset'] == 0;
        }));

        $asset['assignedAsSubAsset'] = $asset['assetsAssignments_assignedAsSubAsset'];
        $asset['extra_assets'] = $asset['tempStock_assets_quantity'];

        $return['mass'] += ($asset['assets_mass'] == null ? $asset['assetTypes_mass'] : $asset['assets_mass']);
        $asset['value'] = new Money(($asset['assets_value'] != null ? $asset['assets_value'] : $asset['assetTypes_value']), new Currency($AUTH->data['instance']['instances_config_currency']));
        $return['value'] = $return['value']->add($asset['value']);

        if ($asset['assetsAssignments_customPrice'] == null) {
            //The actual pricing calculator
            $asset['price'] = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $asset['price'] = $asset['price']->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($return['priceMaths']['days']));
            $asset['price'] = $asset['price']->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($return['priceMaths']['weeks']));
            // Elmentjük a listaár/db-ot is
            $asset['listPricePerItem'] = $asset['price'];
        } else {
            $asset['price'] = new Money($asset['assetsAssignments_customPrice'], new Currency($AUTH->data['instance']['instances_config_currency']));
            // Elmentjük a listaár/db-ot is
            $asset['listPricePerItem'] = $asset['price'];
        }

        // Elmentjük a kedvezmény mértékét is
        $asset['discountPercentage'] = $asset['assetsAssignments_discount'] * 100;

        if ($asset['assetsAssignments_discount'] > 0) $asset['discountPrice'] = $asset['price']->multiply(1 - ($asset['assetsAssignments_discount'] / 100));
        else $asset['discountPrice'] = $asset['price'];

        $return['prices']['discounts'] = $return['prices']['discounts']->add($asset['price']->subtract($asset['discountPrice']));

        // Ha az extra_assets nem nulla, akkor annyi darabszor adjuk hozzá az árat, amennyi az extra_assets értéke
        if ($asset['extra_assets'] != 0) {
            $return['prices']['discounts'] = $return['prices']['discounts']->add(($asset['price']->subtract($asset['discountPrice']))->multiply($asset['extra_assets']));
        }

        $groupName = $asset['assetCategoriesGroups_name'];

        if (!$asset['assignedAsSubAsset']) {
            switch ($groupName) {
                case 'Hangtechnika':
                    $return['prices']['subTotalSound'] = $asset['price']->add($return['prices']['subTotalSound']);
                    $return['prices']['soundDiscountPrice'] = $asset['price']->add($return['prices']['soundDiscountPrice']->add($asset['discountPrice']->subtract($asset['price'])));
                    // Ha az extra_assets nem nulla, akkor annyi darabszor adjuk hozzá az árat
                    if ($asset['extra_assets'] != 0 && $asset['count'] > 0) {
                        $unitPrice = $asset['price']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $unitDiscountPrice = $asset['discountPrice']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $return['prices']['subTotalSound'] = $return['prices']['subTotalSound']->add($unitPrice);
                        $return['prices']['soundDiscountPrice'] = $return['prices']['soundDiscountPrice']->add($unitDiscountPrice);
                    }
                    $return['prices']['soundDiscountPercentage'] = calculateDiscountPercentage($asset);
                    break;

                case 'Fénytechnika':
                    $return['prices']['subTotalLight'] = $asset['price']->add($return['prices']['subTotalLight']);
                    $return['prices']['lightDiscountPrice'] = $asset['price']->add($return['prices']['lightDiscountPrice']->add($asset['discountPrice']->subtract($asset['price'])));
                    if ($asset['extra_assets'] != 0 && $asset['count'] > 0) {
                        $unitPrice = $asset['price']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $unitDiscountPrice = $asset['discountPrice']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $return['prices']['subTotalLight'] = $return['prices']['subTotalLight']->add($unitPrice);
                        $return['prices']['lightDiscountPrice'] = $return['prices']['lightDiscountPrice']->add($unitDiscountPrice);
                    }
                    $return['prices']['lightDiscountPercentage'] = calculateDiscountPercentage($asset);
                    break;

                case 'Vetítéstechnika':
                    $return['prices']['subTotalProjection'] = $asset['price']->add($return['prices']['subTotalProjection']);
                    $return['prices']['projectionDiscountPrice'] = $asset['price']->add($return['prices']['projectionDiscountPrice']->add($asset['discountPrice']->subtract($asset['price'])));
                    if ($asset['extra_assets'] != 0 && $asset['count'] > 0) {
                        $unitPrice = $asset['price']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $unitDiscountPrice = $asset['discountPrice']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $return['prices']['subTotalProjection'] = $return['prices']['subTotalProjection']->add($unitPrice);
                        $return['prices']['projectionDiscountPrice'] = $return['prices']['projectionDiscountPrice']->add($unitDiscountPrice);
                    }
                    $return['prices']['projectionDiscountPercentage'] = calculateDiscountPercentage($asset);
                    break;

                case 'Színpadi tartószerkezet':
                    $return['prices']['subTotalStageStructure'] = $asset['price']->add($return['prices']['subTotalStageStructure']);
                    $return['prices']['stageStructureDiscountPrice'] = $asset['price']->add($return['prices']['stageStructureDiscountPrice']->add($asset['discountPrice']->subtract($asset['price'])));
                    if ($asset['extra_assets'] != 0 && $asset['count'] > 0) {
                        $unitPrice = $asset['price']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $unitDiscountPrice = $asset['discountPrice']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $return['prices']['subTotalStageStructure'] = $return['prices']['subTotalStageStructure']->add($unitPrice);
                        $return['prices']['stageStructureDiscountPrice'] = $return['prices']['stageStructureDiscountPrice']->add($unitDiscountPrice);
                    }
                    $return['prices']['stageStructureDiscountPercentage'] = calculateDiscountPercentage($asset);
                    break;

                case 'Színpadtechnika':
                    $return['prices']['subTotalStage'] = $asset['price']->add($return['prices']['subTotalStage']);
                    $return['prices']['stageDiscountPrice'] = $asset['price']->add($return['prices']['stageDiscountPrice']->add($asset['discountPrice']->subtract($asset['price'])));
                    if ($asset['extra_assets'] != 0 && $asset['count'] > 0) {
                        $unitPrice = $asset['price']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $unitDiscountPrice = $asset['discountPrice']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $return['prices']['subTotalStage'] = $return['prices']['subTotalStage']->add($unitPrice);
                        $return['prices']['stageDiscountPrice'] = $return['prices']['stageDiscountPrice']->add($unitDiscountPrice);
                    }
                    $return['prices']['stageDiscountPercentage'] = calculateDiscountPercentage($asset);
                    break;

                case 'Biztonságtechnika':
                    $return['prices']['subTotalSafety'] = $asset['price']->add($return['prices']['subTotalSafety']);
                    $return['prices']['safetyDiscountPrice'] = $asset['price']->add($return['prices']['safetyDiscountPrice']->add($asset['discountPrice']->subtract($asset['price'])));
                    if ($asset['extra_assets'] != 0 && $asset['count'] > 0) {
                        $unitPrice = $asset['price']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $unitDiscountPrice = $asset['discountPrice']->multiply($asset['extra_assets'])->divide($asset['count'], PHP_ROUND_HALF_UP);
                        $return['prices']['subTotalSafety'] = $return['prices']['subTotalSafety']->add($unitPrice);
                        $return['prices']['safetyDiscountPrice'] = $return['prices']['safetyDiscountPrice']->add($unitDiscountPrice);
                    }
                    $return['prices']['safetyDiscountPercentage'] = calculateDiscountPercentage($asset);
                    break;

                default:
                    break;
            }
        }

        if (!$asset['assignedAsSubAsset']) {
            $return['prices']['subTotal'] = $asset['price']->add($return['prices']['subTotal']);
            if ($asset['extra_assets'] != 0) {
                $return['prices']['subTotal'] = $return['prices']['subTotal']->add($asset['price']->multiply($asset['extra_assets']));
            }
        }

        if (!$asset['assignedAsSubAsset']) {
            $return['prices']['total'] = $return['prices']['total']->add($asset['discountPrice']);
            if ($asset['extra_assets'] != 0) {
                $return['prices']['total'] = $return['prices']['total']->add($asset['discountPrice']->multiply($asset['extra_assets']));
            }
        }

        //Formatted values for each asset
        $asset['formattedValue'] = $moneyFormatter->format($asset['value']);
        $asset['formattedPrice'] = $moneyFormatter->format($asset['price']);
        $asset['formattedDiscountPrice'] = $moneyFormatter->format($asset['discountPrice']);
        $asset['formattedMass'] = number_format(($asset['assets_mass'] == null ? $asset['assetTypes_mass'] : $asset['assets_mass']), 2, '.', '') . "kg";

        $asset['flagsblocks'] = assetFlagsAndBlocks($asset['assets_id']);
        $asset['assetTypes_definableFields_ARRAY'] = array_filter(explode(",", $asset['assetTypes_definableFields']));
        $asset['latestScan'] = assetLatestScan($asset['assets_id']);

        if ($asset['instances_id'] != $project['instances_id']) {
            if (!isset($return['assetsAssignedSUB'][$asset['instances_id']]['assets'])) $return['assetsAssignedSUB'][$asset['instances_id']]['assets'] = [];
            if (!isset($return['assetsAssignedSUB'][$asset['instances_id']]['assets'][$asset['assetTypes_id']])) $return['assetsAssignedSUB'][$asset['instances_id']]['assets'][$asset['assetTypes_id']]['assets'] = [];
            $return['assetsAssignedSUB'][$asset['instances_id']]['assets'][$asset['assetTypes_id']]['assets'][] = $asset;
        } else {
            if (!isset($return['assetsAssigned'][$asset['assetTypes_id']])) $return['assetsAssigned'][$asset['assetTypes_id']]['assets'] = [];
            $return['assetsAssigned'][$asset['assetTypes_id']]['assets'][] = $asset;
        }
    }
    foreach ($return['assetsAssigned'] as $key => $type) {
        if (!isset($return['assetsAssigned'][$key]['totals'])) $return['assetsAssigned'][$key]['totals'] = ["status" => null,"discountPrice"=>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),"price"=>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),"mass"=>0.0];
        foreach ($type['assets'] as $asset) {
            if ($return['assetsAssigned'][$key]['totals']['status'] == null) $return['assetsAssigned'][$key]['totals']['status'] = $asset['assetsAssignmentsStatus_name'];
            elseif ($return['assetsAssigned'][$key]['totals']['status'] != $asset['assetsAssignmentsStatus_name']) $return['assetsAssigned'][$key]['totals']['status'] = false; //They aren't all the same
            $return['assetsAssigned'][$key]['totals']['discountPrice'] = $return['assetsAssigned'][$key]['totals']['discountPrice']->add($asset['discountPrice']);

            $baseItemCount = $asset['count'] ?? 1;
            $extraCount = $asset['extra_assets'] ?? 0;

            $priceWithExtras = ($asset['price']->divide($baseItemCount))->multiply($extraCount);

            $return['assetsAssigned'][$key]['totals']['price'] = $return['assetsAssigned'][$key]['totals']['price']->add($asset['price']);
            $return['assetsAssigned'][$key]['totals']['price'] = $return['assetsAssigned'][$key]['totals']['price']->add($priceWithExtras);

            $return['assetsAssigned'][$key]['totals']['mass'] += ($asset['assets_mass'] == null ? $asset['assetTypes_mass'] : $asset['assets_mass']);
        }
        //formatted Totals
        $return['assetsAssigned'][$key]['totals']['formattedDiscountPrice'] = $moneyFormatter->format($return['assetsAssigned'][$key]['totals']['discountPrice']);
        $return['assetsAssigned'][$key]['totals']['formattedPrice'] = $moneyFormatter->format($return['assetsAssigned'][$key]['totals']['price']);
        $return['assetsAssigned'][$key]['totals']['formattedMass'] = number_format($return['assetsAssigned'][$key]['totals']['mass'], 2, '.', '') . "kg";


        $return['assetsAssigned'][$key]['totals']['priceWithExtras'] = $return['assetsAssigned'][$key]['totals']['price'];
        $return['assetsAssigned'][$key]['totals']['formattedPriceWithExtras'] = $moneyFormatter->format($return['assetsAssigned'][$key]['totals']['priceWithExtras']);
    }
    foreach ($return['assetsAssignedSUB'] as $instanceid => $instance) {
        if (!isset($return['assetsAssignedSUB'][$instanceid]['instance'])) {
            $DBLIB->where("instances_id",$instanceid);
            $return['assetsAssignedSUB'][$instanceid]['instance'] = $DBLIB->getone("instances",["instances_id","instances_name"]);
        }
        foreach ($return['assetsAssignedSUB'][$instanceid]['assets'] as $key => $type) {
            if (!isset($return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals'])) $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals'] = ["status" => null,"discountPrice"=>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),"price"=>new Money(null, new Currency($AUTH->data['instance']['instances_config_currency'])),"mass"=>0.0];
            foreach ($type['assets'] as $asset) {
                if ($return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['status'] == null) $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['status'] = $asset['assetsAssignmentsStatus_name'];
                elseif ($return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['status'] != $asset['assetsAssignmentsStatus_name']) $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['status'] = false; //They aren't all the same
                $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['discountPrice'] = $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['discountPrice']->add($asset['discountPrice']);

                $baseItemCount = $asset['count'] ?? 1;
                $extraCount = $asset['extra_assets'] ?? 0;

                $priceWithExtras = ($asset['price']->divide($baseItemCount))->multiply($extraCount);

                $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['price'] = $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['price']->add($asset['price']);
                $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['price'] = $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['price']->add($priceWithExtras);

                $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['mass'] += ($asset['assets_mass'] == null ? $asset['assetTypes_mass'] : $asset['assets_mass']);
            }
            //Formatted totals
            $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['formattedDiscountPrice'] = $moneyFormatter->format($return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['discountPrice']);
            $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['formattedPrice'] = $moneyFormatter->format($return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['price']);
            $return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['formattedMass'] = number_format($return['assetsAssignedSUB'][$instanceid]['assets'][$key]['totals']['mass'], 2, '.', '') . "kg";
        }
    }

    $return['payments']['subTotal'] = $return['prices']['total']->add($return['payments']['sales']['total'], $return['payments']['subHire']['total'], $return['payments']['staff']['total'], $return['payments']['truck']['total']);
    $return['payments']['total'] = $return['payments']['subTotal']->subtract($return['payments']['received']['total']);

    //add formatted values to everything
    $return['formattedValue'] = $moneyFormatter->format($return['value']);
    $return['formattedPrices'] = ["subTotal" => $moneyFormatter->format($return['prices']['subTotal']), "discounts" => $moneyFormatter->format($return['prices']['discounts']), "total" => $moneyFormatter->format($return['prices']['total'])];
    $return['formattedMass'] = number_format($return['mass'], 2, '.', '') . "kg";
    //format payments
    foreach ($return['payments'] as $key => $value) {
        if ($key == "subTotal") {
            $return['payments']['formattedSubTotal'] = $moneyFormatter->format($return['payments']["subTotal"]);
        } elseif ($key == "total"){
            $return['payments']['formattedTotal'] = $moneyFormatter->format($return['payments']["total"]);
        } else {
            $return['payments'][$key]['formattedTotal'] = $moneyFormatter->format($return['payments'][$key]['total']);
        }
    }
    
    return $return;
}
$PAGEDATA['FINANCIALS'] = projectFinancials($PAGEDATA['project']);
$DBLIB->where("projects_id",$PAGEDATA['project']['projects_id']);
$DBLIB->orderBy("projectsFinanceCache_timestamp", "DESC");
$projectFinanceCache = $DBLIB->getone("projectsFinanceCache");
$projectFinancesCacheMismatch = false;
if (!$projectFinanceCache) {
    //Insert a new project finance cache for this project as it doesn't seem to have one hmmm
    $projectFinanceCacheInsert = [
        "projects_id" => $PAGEDATA['project']['projects_id'],
        "projectsFinanceCache_timestamp" => date("Y-m-d H:i:s"),
        "projectsFinanceCache_equipmentSubTotal" =>$PAGEDATA['FINANCIALS']['prices']['subTotal']->getAmount(),
        "projectsFinanceCache_equiptmentDiscounts" =>$PAGEDATA['FINANCIALS']['prices']['discounts']->getAmount(),
        "projectsFinanceCache_equiptmentTotal" =>$PAGEDATA['FINANCIALS']['prices']['total']->getAmount(),
        "projectsFinanceCache_salesTotal" =>$PAGEDATA['FINANCIALS']['payments']['sales']['total']->getAmount(),
        "projectsFinanceCache_staffTotal" =>$PAGEDATA['FINANCIALS']['payments']['staff']['total']->getAmount(),
        "projectsFinanceCache_truckTotal" => $PAGEDATA['FINANCIALS']['payments']['truck']['total']->getAmount(),
        "projectsFinanceCache_externalHiresTotal" => $PAGEDATA['FINANCIALS']['payments']['subHire']['total']->getAmount(),
        "projectsFinanceCache_paymentsReceived" =>$PAGEDATA['FINANCIALS']['payments']['received']['total']->getAmount(),
        "projectsFinanceCache_grandTotal" =>$PAGEDATA['FINANCIALS']['payments']['total']->getAmount(),
        "projectsFinanceCache_mass"=>$PAGEDATA['FINANCIALS']['mass'],
        "projectsFinanceCache_value"=>$PAGEDATA['FINANCIALS']['value']->getAmount(),
    ];
    $DBLIB->insert("projectsFinanceCache", $projectFinanceCacheInsert); //Add a cache for the finance of the project
//Just check the cache while we're here - shouldn't ever be thrown!
} elseif ($projectFinanceCache["projectsFinanceCache_equipmentSubTotal"] != $PAGEDATA['FINANCIALS']['prices']['subTotal']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_equiptmentDiscounts"] != $PAGEDATA['FINANCIALS']['prices']['discounts']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_equiptmentTotal"] != $PAGEDATA['FINANCIALS']['prices']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_salesTotal"] != $PAGEDATA['FINANCIALS']['payments']['sales']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_staffTotal"] != $PAGEDATA['FINANCIALS']['payments']['staff']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_truckTotal"] != $PAGEDATA['FINANCIALS']['payments']['truck']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_externalHiresTotal"] !=  $PAGEDATA['FINANCIALS']['payments']['subHire']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_paymentsReceived"] != $PAGEDATA['FINANCIALS']['payments']['received']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_grandTotal"] != $PAGEDATA['FINANCIALS']['payments']['total']->getAmount()) $projectFinancesCacheMismatch = true;
elseif ($projectFinanceCache["projectsFinanceCache_value"] != $PAGEDATA['FINANCIALS']['value']->getAmount()) $projectFinancesCacheMismatch = true;
elseif (round($projectFinanceCache["projectsFinanceCache_mass"]*100000) != round($PAGEDATA['FINANCIALS']['mass']*100000)) $projectFinancesCacheMismatch = true;

if ($projectFinancesCacheMismatch) {
    //So there's a serious project finance mismatch - you can force a rebuild of the cache by passing a get parameter - otherwise throw an error so support are notified
    $projectFinanceCacheInsert = [
        "projects_id" => $PAGEDATA['project']['projects_id'],
        "projectsFinanceCache_timestamp" => date("Y-m-d H:i:s"),
        "projectsFinanceCache_equipmentSubTotal" =>$PAGEDATA['FINANCIALS']['prices']['subTotal']->getAmount(),
        "projectsFinanceCache_equiptmentDiscounts" =>$PAGEDATA['FINANCIALS']['prices']['discounts']->getAmount(),
        "projectsFinanceCache_equiptmentTotal" =>$PAGEDATA['FINANCIALS']['prices']['total']->getAmount(),
        "projectsFinanceCache_salesTotal" =>$PAGEDATA['FINANCIALS']['payments']['sales']['total']->getAmount(),
        "projectsFinanceCache_staffTotal" =>$PAGEDATA['FINANCIALS']['payments']['staff']['total']->getAmount(),
        "projectsFinanceCache_truckTotal" => $PAGEDATA['FINANCIALS']['payments']['truck']['total']->getAmount(),
        "projectsFinanceCache_externalHiresTotal" => $PAGEDATA['FINANCIALS']['payments']['subHire']['total']->getAmount(),
        "projectsFinanceCache_paymentsReceived" =>$PAGEDATA['FINANCIALS']['payments']['received']['total']->getAmount(),
        "projectsFinanceCache_grandTotal" =>$PAGEDATA['FINANCIALS']['payments']['total']->getAmount(),
        "projectsFinanceCache_mass"=>$PAGEDATA['FINANCIALS']['mass'],
        "projectsFinanceCache_value"=>$PAGEDATA['FINANCIALS']['value']->getAmount(),
    ];
    $newCache = $DBLIB->insert("projectsFinanceCache", $projectFinanceCacheInsert); //Add a cache for the finance of the project
    if(!$newCache) throw new \Exception('Cache reload error');
    else trigger_error("Project finance cache mismatch " . json_encode($projectFinanceCacheInsert) . " vs " . json_encode($projectFinanceCache), E_USER_WARNING);
}



usort($PAGEDATA['FINANCIALS']['payments']['subHire']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['sales']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['truck']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});
usort($PAGEDATA['FINANCIALS']['payments']['staff']['ledger'], function ($a, $b) {
    // Sort sub-hires in order of supplier so you can do supplier headings
    return $a['payments_supplier'] <=> $b['payments_supplier'];
});

//Notes
$DBLIB->where("projectsNotes_deleted", 0);
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->orderBy("projectsNotes_id", "ASC");
$PAGEDATA['project']['notes'] = $DBLIB->get("projectsNotes");

//Crew
$DBLIB->where("projects_id", $PAGEDATA['project']['projects_id']);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("users", "crewAssignments.users_userid=users.users_userid", "LEFT");
$DBLIB->orderBy("crewAssignments.crewAssignments_rank", "ASC");
$DBLIB->orderBy("crewAssignments.crewAssignments_id", "ASC");
$PAGEDATA['project']['crewAssignments'] = $DBLIB->get("crewAssignments", null, ["crewAssignments.*", "users.users_name1", "users.users_name2", "users.users_email", "users_social_mobilephone"]);

//Files
$PAGEDATA['files'] = $bCMS->s3List(7, $PAGEDATA['project']['projects_id']);

$PAGEDATA['invoices'] = $bCMS->s3List(20, $PAGEDATA['project']['projects_id'], 's3files_meta_uploaded', 'DESC');
$PAGEDATA['quotes'] = $bCMS->s3List(21, $PAGEDATA['project']['projects_id'], 's3files_meta_uploaded', 'DESC');
$PAGEDATA['trucks'] = $bCMS->s3List(22, $PAGEDATA['project']['projects_id'], 's3files_meta_uploaded', 'DESC');

$DBLIB->orderBy("assetsAssignmentsStatus_order","ASC");
$DBLIB->where("assetsAssignmentsStatus_deleted", 0);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$PAGEDATA['assetsAssignmentsStatus'] = $DBLIB->get("assetsAssignmentsStatus");


$DATA = [
    "project" => $PAGEDATA['project'],
    "files" => $PAGEDATA['files'],
    "assetsAssignmentsStatus" => $PAGEDATA['assetsAssignmentsStatus'],
    'FINANCIALS' => $PAGEDATA['FINANCIALS']
]; //Data that's safe to return to the app


if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) finish(true, null, $DATA);

/** @OA\Post(
 *     path="/projects/data.php", 
 *     summary="Data", 
 *     description="Get the data of a project  
Requires Instance Permission PROJECTS:VIEW
", 
 *     operationId="data", 
 *     tags={"projects"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="Form Data",
 *         required="false", 
 *         @OA\Schema(
 *             type="object", 
 *             ),
 *     ), 
 *     @OA\Parameter(
 *         name="id",
 *         in="query",
 *         description="Project ID",
 *         required="false", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 * )
 */