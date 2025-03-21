<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Check if id is provided in POST request
if (!isset($_POST['id'])) {
    finish(false, 'Asset ID is missing.', null);
    exit;
}

$assetId = $_POST['id'];

// Query the assetTypes table for the name using the provided id
$DBLIB->where('assetTypes_id', $assetId);
$assetType = $DBLIB->getOne('assetTypes', ['assetTypes_name']);  // Fetch only the name field

$response = [];

if (!empty($assetType)) {
    // Successfully found the asset name
    $response['assetName'] = $assetType['assetTypes_name'];
} else {
    // Asset ID not found
    $response['message'] = 'Asset not found for ID: ' . $assetId;
    // Optionally log this error for further debugging
    error_log("Asset not found for ID: " . $assetId);
}

finish(true, null, $response);
