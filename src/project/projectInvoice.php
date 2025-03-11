<?php
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));
require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from

$PAGEDATA['GET'] = $_GET;
$PAGEDATA['GET']['generate'] = true;

$isQuote = $_GET['quote'] === "true";  // If 'true', then it's a quote
$isTruck = $_GET['quote'] === "truck"; // If 'truck', then it's a truck

// Set typeId based on quote type
if ($isQuote) {
    $typeId = 21; // Quote type
} elseif ($isTruck) {
    $typeId = 22; // Truck type
} else {
    $typeId = 20; // Default type (invoice)
}

$PAGEDATA['GET']['quote'] = $_GET['quote']; // Save the original quote value
$PAGEDATA['GET']['draft'] = $_GET['draft'] == "true"; // Check if it's a draft

// Get file number for the type
$DBLIB->where("s3files_meta_type", $typeId);
$DBLIB->where("instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_meta_subType", $_GET['id']);
$count = $DBLIB->getValue("s3files", "count(*)");
$fileNumber = $count ? ($count + 1) : 1;
$PAGEDATA['fileNumber'] = $fileNumber;

// Set the instance logo if available
if ($PAGEDATA['USERDATA']['instance']['instances_logo'] && $PAGEDATA['GET']['instancelogo']) {
    $PAGEDATA['INSTANCELOGO'] = $bCMS->s3DataUri($PAGEDATA['USERDATA']['instance']['instances_logo']);
} else {
    $PAGEDATA['INSTANCELOGO'] = false;
}

echo $TWIG->render('project/pdf.twig', $PAGEDATA);
