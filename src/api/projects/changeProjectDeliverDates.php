<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DATES") or !isset($_POST['projects_id'])) die("404");

$type = $_POST["type"];

$newDates = [
    "projects_dates_deliver_start" => date("Y-m-d H:i:s", strtotime(str_replace(".", "", $_POST['projects_dates_deliver_start']))),
    "projects_dates_deliver_end" => date("Y-m-d H:i:s", strtotime(str_replace(".", "", $_POST['projects_dates_deliver_end'])))
];

if (empty($newDates)) {
    die("Failed to set project dates.");
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", [
    "projects_id",
    "projects_dates_deliver_start",
    "projects_dates_deliver_end",
    "projects_dates_tech_start",
    "projects_dates_tech_end",
    "projects_dates_stageStructure_start",
    "projects_dates_stageStructure_end",
    "projects_dates_stage_start",
    "projects_dates_stage_end",
    "projects_dates_finances_days",
    "projects_dates_finances_weeks"
]);
if (!$project) finish(false);

$projectFinanceHelper = new projectFinance();
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);
if ($project['projects_dates_finances_days'] !== NULL and $project['projects_dates_finances_weeks'] !== NULL) {
    // Custom price maths is set, so changing the dates will have no impact on pricing anyway
    $priceMathsOld = $projectFinanceHelper->durationMaths($project['projects_id']);
    $priceMathsNew = $priceMathsOld;
} else {
    // We need to calculate the price maths because custom price maths is not set
    $priceMathsOld = $projectFinanceHelper->durationMaths($project['projects_id']);

    // Determine which date range is being updated and calculate the new price maths accordingly
    if ($type == "Technikai eszközök") {
        $priceMathsNew = $projectFinanceHelper->durationMathsByDates($newDates['projects_dates_tech_start'], $newDates['projects_dates_tech_end']);
    } elseif ($type == "Színpadi tartószerkezeti eszközök") {
        $priceMathsNew = $projectFinanceHelper->durationMathsByDates($newDates['projects_dates_stageStructure_start'], $newDates['projects_dates_stageStructure_end']);
    } elseif ($type == "Színpadi eszközök") {
        $priceMathsNew = $projectFinanceHelper->durationMathsByDates($newDates['projects_dates_stage_start'], $newDates['projects_dates_stage_end']);
    } else {
        // Default case if none of the specific date ranges match
        $priceMathsNew = $projectFinanceHelper->durationMathsByDates($newDates['projects_dates_deliver_start'], $newDates['projects_dates_deliver_end']);
    }
}

// We're changing dates so we need to find clashes in the new dates
$DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
$DBLIB->where("assetsAssignments.projects_id", $project['projects_id']);
$DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");

$DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
$DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");


$assets = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assets_id", "assetsAssignments.assetsAssignments_id", "assetsAssignments_customPrice", "assetsAssignments_discount", "assetTypes_weekRate", "assetTypes_dayRate", "assets_dayRate", "assets_weekRate"]);
if ($assets) {
    $unavailableAssets = [];
    foreach ($assets as $asset) {
        $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
        $DBLIB->join("assets", "assetsAssignments.assets_id=assets.assets_id", "LEFT");
        $DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");

        $DBLIB->join("assetCategories", "assetCategories.assetCategories_id=assetTypes.assetCategories_id", "LEFT");
        $DBLIB->join("assetCategoriesGroups", "assetCategoriesGroups.assetCategoriesGroups_id=assetCategories.assetCategoriesGroups_id", "LEFT");

        $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
        $DBLIB->where("assetsAssignments.assets_id", $asset['assets_id']);
        $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
        $DBLIB->where("projects.projects_deleted", 0);
        $DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
        $DBLIB->where("(projects.projects_id != " .  $project['projects_id'] . ")"); // Avoid finding the current project

        $groupName = $asset['assetCategoriesGroups_name'];

        switch ($groupName) {
            case "Hangtechnika":
            case "Fénytechnika":
            case "Vetítéstechnika":
                $DBLIB->where("((projects_dates_tech_start >= '" . $newDates["projects_dates_deliver_start"]  . "' AND projects_dates_tech_start <= '" . $newDates["projects_dates_tech_end"] . "') OR (projects_dates_tech_end >= '" . $newDates["projects_dates_deliver_start"] . "' AND projects_dates_tech_end <= '" . $newDates["projects_dates_tech_end"] . "') OR (projects_dates_tech_end >= '" . $newDates["projects_dates_tech_end"] . "' AND projects_dates_tech_start <= '" . $newDates["projects_dates_deliver_start"] . "'))");
                break;

            case "Színpadi tartószerkezet":
                $DBLIB->where("((projects_dates_stageStructure_start >= '" . $newDates["projects_dates_deliver_start"]  . "' AND projects_dates_stageStructure_start <= '" . $newDates["projects_dates_stageStructure_end"] . "') OR (projects_dates_stageStructure_end >= '" . $newDates["projects_dates_deliver_start"] . "' AND projects_dates_stageStructure_end <= '" . $newDates["projects_dates_stageStructure_end"] . "') OR (projects_dates_stageStructure_end >= '" . $newDates["projects_dates_stageStructure_end"] . "' AND projects_dates_stageStructure_start <= '" . $newDates["projects_dates_deliver_start"] . "'))");
                break;

            case "Színpadtechnika":
                $DBLIB->where("((projects_dates_stage_start >= '" . $newDates["projects_dates_deliver_start"]  . "' AND projects_dates_stage_start <= '" . $newDates["projects_dates_stage_end"] . "') OR (projects_dates_stage_end >= '" . $newDates["projects_dates_deliver_start"] . "' AND projects_dates_stage_end <= '" . $newDates["projects_dates_stage_end"] . "') OR (projects_dates_stage_end >= '" . $newDates["projects_dates_stage_end"] . "' AND projects_dates_stage_start <= '" . $newDates["projects_dates_deliver_start"] . "'))");
                break;
            case "Biztonságtechnika":
            default:
                $DBLIB->where("((projects_dates_deliver_start >= '" . $newDates["projects_dates_deliver_start"]  . "' AND projects_dates_deliver_start <= '" . $newDates["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $newDates["projects_dates_deliver_start"] . "' AND projects_dates_deliver_end <= '" . $newDates["projects_dates_deliver_end"] . "') OR (projects_dates_deliver_end >= '" . $newDates["projects_dates_deliver_end"] . "' AND projects_dates_deliver_start <= '" . $newDates["projects_dates_deliver_start"] . "'))");
                break;
        }

        $assignment = $DBLIB->getone("assetsAssignments", null, ["assetsAssignments.assetsAssignments_id", "assetsAssignments.assets_id", "assetsAssignments.projects_id", "assetTypes.assetTypes_name", "projects.projects_name", "assets.assets_tag"]);
        if ($assignment) {
            $assignment['old_assetsAssignments_id'] = $asset['assetsAssignments_id'];
            $unavailableAssets[] = $assignment;
        }
    }
    if (count($unavailableAssets) > 0) {
        finish(true, null, ["changed" => false, "assets" => $unavailableAssets]);
    } else {
        foreach ($assets as $asset) {
            // This change is going to go ahead so re-calculate finance
            if ($asset['assetsAssignments_customPrice'] != null) continue; // There is a custom price set - so this asset is date agnostic anyway

            $priceOriginal = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $priceOriginal = $priceOriginal->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsOld['days']));
            $priceOriginal = $priceOriginal->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsOld['weeks']));
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $priceOriginal, true);

            $price = new Money(null, new Currency($AUTH->data['instance']['instances_config_currency']));
            $price = $price->add((new Money(($asset['assets_dayRate'] !== null ? $asset['assets_dayRate'] : $asset['assetTypes_dayRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsNew['days']));
            $price = $price->add((new Money(($asset['assets_weekRate'] !== null ? $asset['assets_weekRate'] : $asset['assetTypes_weekRate']), new Currency($AUTH->data['instance']['instances_config_currency'])))->multiply($priceMathsNew['weeks']));
            $projectFinanceCacher->adjust('projectsFinanceCache_equipmentSubTotal', $price, false);

            if ($asset['assetsAssignments_discount'] > 0) {
                // Remove old discount
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $priceOriginal->subtract($priceOriginal->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), true);
                // Set a new discount
                $projectFinanceCacher->adjust('projectsFinanceCache_equiptmentDiscounts', $price->subtract($price->multiply(1 - ($asset['assetsAssignments_discount'] / 100))), false);
            }
        }
    }
}

if ($projectFinanceCacher->save()) {
    $DBLIB->where("projects.projects_id", $project['projects_id']);
    // Update project with the new dates in the required format

    $updateFields = [];

    switch ($type) {
        case "Technikai eszközök":
            $updateFields["projects_dates_tech_start"] = $newDates["projects_dates_deliver_start"];
            $updateFields["projects_dates_tech_end"] = $newDates["projects_dates_deliver_end"];
            break;

        case "Színpadi tartószerkezeti eszközök":
            $updateFields["projects_dates_stageStructure_start"] = $newDates["projects_dates_deliver_start"];
            $updateFields["projects_dates_stageStructure_end"] = $newDates["projects_dates_deliver_end"];
            break;

        case "Színpadi eszközök":
            $updateFields["projects_dates_stage_start"] = $newDates["projects_dates_deliver_start"];
            $updateFields["projects_dates_stage_end"] = $newDates["projects_dates_deliver_end"];
            break;
        default:
            $updateFields["projects_dates_deliver_start"] = $newDates["projects_dates_deliver_start"];
            $updateFields["projects_dates_deliver_end"] = $newDates["projects_dates_deliver_end"];
            break;
    }

    if (!empty($updateFields)) {
        $projectUpdate = $DBLIB->update("projects", $updateFields);
    }

    if (!$projectUpdate) finish(false);

    $logMessage = "";

    if (!empty($updateFields["projects_dates_tech_start"])) {
        $logMessage .= "A technikai eszközök dátumait módosították: " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_tech_start"])) .
            " - " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_tech_end"])) . "\n";
    } elseif (!empty($updateFields["projects_dates_stageStructure_start"])) {
        $logMessage .= "A színpadi tartószerkezeti eszközök dátumait módosították: " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_stageStructure_start"])) .
            " - " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_stageStructure_end"])) . "\n";
    } elseif (!empty($updateFields["projects_dates_stage_start"])) {
        $logMessage .= "A színpadi eszközök dátumait módosították: " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_stage_start"])) .
            " - " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_stage_end"])) . "\n";
    } else {
        $logMessage .= "A szállítási dátumokat módosították: " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_deliver_start"])) .
            " - " .
            date("Y-m-d H:i", strtotime($updateFields["projects_dates_deliver_end"])) . "\n";
    }

    if ($logMessage) {
        $bCMS->auditLog(
            "CHANGE-DATE",
            "projects",
            rtrim($logMessage), // eltávolítjuk a végső sortörést
            $AUTH->data['users_userid'],
            null,
            $_POST['projects_id']
        );
    }

    finish(true, null, ["changed" => true]);
} else {
    finish(false, ["message" => "Nem lehet módosítani a pénzügyeket a dátumok változtatásához"]);
}


/** @OA\Post(
 *     path="/projects/changeProjectDeliverDates.php", 
 *     summary="Change Project Deliver Dates", 
 *     description="Change the start and end deliver dates of a project  
Requires Instance Permission PROJECTS:EDIT:DATES
", 
 *     operationId="changeProjectDeliverDates", 
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
 *         name="projects_id",
 *         in="query",
 *         description="Project ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_deliver_start",
 *         in="query",
 *         description="Start Date/Time",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_deliver_end",
 *         in="query",
 *         description="End Date/Time",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */