<?php
require_once __DIR__ . '/../common/headSecure.php';
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_GET['id'])) die($TWIG->render('404.twig', $PAGEDATA));
require_once __DIR__ . '/../api/projects/data.php'; //Where most of the data comes from



$PAGEDATA['GET'] = $_GET;
$PAGEDATA['GET']['generate'] = true;

$isQuote = $_GET['quote'] == "true";
$isTruck = $_GET['quote'] == "truck";
$isInvoice = $_GET['quote'] == "false";

$PAGEDATA['GET']['quote'] = $isQuote ? "true" : ($isTruck ? "truck" : ($isInvoice ? "false" : ""));

$typeId = $isQuote ? 21 : ($isTruck ? 22 : 20);

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

// Lekérdezzük az asset hozzárendeléseket
$DBLIB->where("projects_id", $_GET['id']);
$assetsAssignments = $DBLIB->get("assetsAssignments", null, ["assets_id", "assetsAssignments_assignedAsSubAsset"]);
$PAGEDATA['assetsAssignments'] = $assetsAssignments;

$assetsAssignmentsMap = [];
foreach ($assetsAssignments as $assignment) {
    $assetsAssignmentsMap[$assignment['assets_id']] = (bool) $assignment['assetsAssignments_assignedAsSubAsset'];
}
$PAGEDATA['assetsAssignmentsMap'] = $assetsAssignmentsMap;

echo $TWIG->render('project/pdf.twig', $PAGEDATA);