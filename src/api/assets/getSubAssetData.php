<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$DBLIB->join("assetTypes", "assetSubAssets.assetSubAssets_sub_asset_id = assetTypes.assetTypes_id", "LEFT");
$DBLIB->where("assetSubAssets.assetSubAssets_id", $_POST['assets_id']);
$subAssets = $DBLIB->get("assetSubAssets");

$response = [
    "hasSubAssets" => !empty($subAssets),
    "countSubAssets" => count($subAssets),
    "subAssets" => []
];

if (!empty($subAssets)) {
    foreach ($subAssets as $subAsset) {
        $response["subAssets"][] = [
            "origin" => (int) $subAsset['assetSubAssets_id'], // Ensure correct reference
            "id" => (int) $subAsset['assetSubAssets_sub_asset_id'],
            "quantity" => isset($subAsset['assetSubAssets_sub_asset_quantity']) ? (int) $subAsset['assetSubAssets_sub_asset_quantity'] : 0
        ];
    }
}

if (empty($response["subAssets"])) {
    finish(false, ["code" => "LIST-ASSETTYPES-FAIL", "message" => "Could not find matching sub-assets"]);
} else {
    finish(true, null, $response);
}
