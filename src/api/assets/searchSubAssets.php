<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Az összes eszközt lekérjük, a szükséges feltételekkel
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");

// Nincs keresési szűrés, csak rendezzük név szerint
$DBLIB->orderBy("assetTypes_name", "ASC");

// Lekérjük az összes eszközt
$assets = $DBLIB->get("assets", null, ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id"]);

if (!$assets) {
    finish(false, ["code" => "LIST-ASSETS-FAIL", "message" => "Could not search"]);
}

// Duplikációk kiszűrése assetTypes_id alapján
$uniqueAssets = [];
foreach ($assets as $asset) {
    if (!isset($uniqueAssets[$asset['assetTypes_id']])) {
        $asset['tag'] = $asset['assets_tag']; // Az eszközök visszaadása szűrés és vonalkód nélkül
        $uniqueAssets[$asset['assetTypes_id']] = $asset;
    }
}

// A válasz létrehozása (csak az egyedi elemeket adjuk vissza)
finish(true, null, array_values($uniqueAssets));
