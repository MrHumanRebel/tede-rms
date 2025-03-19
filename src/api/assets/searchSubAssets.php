<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Az összes eszközt visszaadjuk, nem szűrünk semmire
$DBLIB->where("assets.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->join("assetTypes", "assets.assetTypes_id=assetTypes.assetTypes_id", "LEFT");
$DBLIB->where("assets_deleted", 0);
$DBLIB->where("(assets.assets_endDate IS NULL OR assets.assets_endDate >= CURRENT_TIMESTAMP())");

// Nincs keresési szűrés, csak rendezzük név szerint
$DBLIB->orderBy("assetTypes_name", "ASC");

// Lekérjük az összes eszközt
$assets = $DBLIB->get("assets", null, ["assets.assets_id", "assets.assets_tag", "assetTypes.assetTypes_name", "assetTypes.assetTypes_id"]);

if (!$assets) finish(false, ["code" => "LIST-ASSETS-FAIL", "message" => "Could not search"]);

$assetsReturn = [];
foreach ($assets as $asset) {
    // Az eszközök visszaadása szűrés és vonalkód nélkül
    $asset['tag'] = $asset['assets_tag'];

    $assetsReturn[] = $asset;
}

finish(true, null, $assetsReturn);
