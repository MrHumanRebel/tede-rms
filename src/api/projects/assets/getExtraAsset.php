<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

// Default response structure
$response = ['assets' => []];

// A projekt ID lekérése a POST adatokból
$projectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;

if ($projectId > 0) {
    // Lekérdezés a tempStockAssets táblában lévő eszközökhöz
    $DBLIB->where("tempStockAssets.tempStock_project_id", $projectId);

    // Csatlakoztatjuk az assetTypes táblát
    $DBLIB->join("assetTypes", "tempStockAssets.tempStock_assets_id = assetTypes.assetTypes_id", "LEFT");

    // Csatlakoztatjuk az manufacturers táblát
    $DBLIB->join("manufacturers", "manufacturers.manufacturers_id = assetTypes.manufacturers_id", "LEFT");

    // Lekérjük az adatokat
    $assets = $DBLIB->get("tempStockAssets", null, [
        "tempStockAssets.tempStock_assets_id AS asset_id",
        "tempStockAssets.tempStock_assets_quantity AS quantity",
        "assetTypes.assetTypes_name AS type_name",
        "manufacturers.manufacturers_name AS manufacturer_name",
        "tempStockAssets.tempStock_assets_quantity * assetTypes.assetTypes_mass AS sum_mass"
    ]);

    // Debugging: Check if assets are returned
    error_log(print_r($assets, true));  // Log the result

    // Ha vannak eredmények, visszaküldjük őket
    if ($assets) {
        $response['assets'] = $assets; // Assets hozzáadása a válaszhoz
    }
    echo json_encode($response);
} else {
    echo json_encode(['assets' => []]);  // In case no valid project_id is provided
}
