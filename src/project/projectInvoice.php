<?php
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));
require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from

$PAGEDATA['GET'] = $_GET;
$PAGEDATA['GET']['generate'] = true;

$isQuote = $_GET['quote'] === "true"; // Ha 'true', akkor árajánlat
$isTruck = $_GET['quote'] === "truck"; // Ha 'truck', akkor teherautó
$isInvoice = $_GET['quote'] === "false"; // Ha 'false', akkor számla

// Állítsuk be a $typeId értékét
if ($isQuote) {
    $typeId = 21; // Árajánlat
} elseif ($isTruck) {
    $typeId = 22; // Teherautó
} elseif ($isInvoice) {
    $typeId = 20; // Számla
}

$PAGEDATA['GET']['draft'] = $_GET['draft'] == "true";

$DBLIB->where("s3files_meta_type", $typeId);
$DBLIB->where("instances_id",$AUTH->data['instance']['instances_id']);
$DBLIB->where("s3files_meta_subType",$_GET['id']);
$count = $DBLIB->getValue ("s3files", "count(*)");
if ($count) $fileNumber = ($count+1);
else $fileNumber = 1;
$PAGEDATA['fileNumber'] = $fileNumber;

if ($PAGEDATA['USERDATA']['instance']['instances_logo'] and $PAGEDATA['GET']['instancelogo']) {
    $PAGEDATA['INSTANCELOGO'] = $bCMS->s3DataUri($PAGEDATA['USERDATA']['instance']['instances_logo']);
} else $PAGEDATA['INSTANCELOGO'] = false;

echo $TWIG->render('project/pdf.twig', $PAGEDATA);