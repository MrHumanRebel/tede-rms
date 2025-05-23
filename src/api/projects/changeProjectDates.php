<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:EDIT:DATES") or !isset($_POST['projects_id']) || !isset($_POST['projects_dates_use_start']) || !isset($_POST['projects_dates_use_end'])) {
    die("404");
}

// Parse the date in "YYYY. MM. DD. HH:mm" format to a valid MySQL datetime format
$start_date = DateTime::createFromFormat('Y. m. d. H:i', $_POST['projects_dates_use_start']);
$end_date = DateTime::createFromFormat('Y. m. d. H:i', $_POST['projects_dates_use_end']);

// Check if the dates are valid
if (!$start_date || !$end_date) {
    die("Invalid date format");
}

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->update("projects", [
    "projects.projects_dates_use_start" => $start_date->format("Y-m-d H:i:s"),
    "projects.projects_dates_use_end" => $end_date->format("Y-m-d H:i:s")
]);

if (!$project) {
    finish(false);
}

$bCMS->auditLog("CHANGE-DATE", "projects", "A kezdő dátumot erre állította: " . $start_date->format("Y-m-d H:i") . "\nA befejező dátumot erre állította: " . $end_date->format("Y-m-d H:i"), $AUTH->data['users_userid'], null, $_POST['projects_id']);
finish(true);

/** @OA\Post(
 *     path="/projects/changeProjectDates.php", 
 *     summary="Change Project Dates", 
 *     description="Change the start and end dates of a project  
Requires Instance Permission PROJECTS:EDIT:DATES
", 
 *     operationId="changeProjectDates", 
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
 *         name="projects_dates_use_start",
 *         in="query",
 *         description="Start Date/Time",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 *     @OA\Parameter(
 *         name="projects_dates_use_end",
 *         in="query",
 *         description="End Date/Time",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */