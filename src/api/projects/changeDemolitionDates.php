<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DATES") or !isset($_POST['projects_id'])) {
    die("404");
}

// Parse the date in "YYYY. MM. DD." format to a valid MySQL datetime format
$demolition_date = DateTime::createFromFormat('Y. m. d.', $_POST['projects_dates_demolition']);

// Check if the date is valid
if (!$demolition_date) {
    die("Invalid date format");
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", [
    "projects.projects_dates_demolition" => $demolition_date->format("Y-m-d")
]);

if (!$project) {
    finish(false);
}

$bCMS->auditLog("CHANGE-DATE", "projects", "A bontási dátumot erre állította: " . $demolition_date->format("Y-m-d"), $AUTH->data['users_userid'], null, $_POST['projects_id']);
finish(true);
