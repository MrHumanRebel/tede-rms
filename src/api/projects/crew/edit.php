<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT")) finish(false, ["error" => "PERMISSION_ERROR", "message" => "Nincs kód a frissítéshez"]);
if (!isset($_POST['crewAssignments_id'])) finish(false, ["error" => "NO_ASSIGNMENT_ID", "message" => "Nincs hozzárendelési ID megadva"]);

$DBLIB->where("crewAssignments_id", $_POST['crewAssignments_id']);
$DBLIB->where("crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$assignment = $DBLIB->getone("crewAssignments", ["crewAssignments.crewAssignments_id", "crewAssignments.users_userid", "crewAssignments.crewAssignments_role", "projects.projects_id", "projects.projects_name"]);
if (!$assignment) finish(false, ["error" => "NO_ASSIGNMENT", "message" => "Nincs hozzárendelés azonosítóval"]);
else {
    $bCMS->auditLog("EDIT-CREW", "crewAssignments", $assignment['crewAssignments_id'], $AUTH->data['users_userid'],null, $assignment['projects_id']);
    if (isset($_POST['crewAssignments_role'])) {
        if ($assignment["users_userid"] and $_POST['crewAssignments_role'] != $assignment['crewAssignments_role']) { //Csak akkor küldjünk e-mailt, ha a szerep neve megváltozott, nem csak a szerep kommentje
            notify(20, $assignment["users_userid"], $AUTH->data['instance']['instances_id'], 
                str_replace(
                    ['á', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű'], 
                    ['a', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'u'], 
                    $AUTH->data['users_name1'] . " " . $AUTH->data['users_name2'] . " megvaltoztatta a szereped a projektben (" . $assignment['projects_name'] . ") es mostantol " . $bCMS->sanitizeString($_POST['crewAssignments_role'])
                )
            );
        }
        $DBLIB->where("crewAssignments_id", $assignment['crewAssignments_id']);
        if (!($DBLIB->update("crewAssignments", ["crewAssignments_role" => $_POST['crewAssignments_role']]))) finish(false, ["code"=> "ROLE_UPDATE_FAIL", "message" => "Nem sikerült a szerep frissítése"]);
    }
    if (isset($_POST['crewAssignments_comment'])) {
        $DBLIB->where("crewAssignments_id", $assignment['crewAssignments_id']);
        if (!($DBLIB->update("crewAssignments", ["crewAssignments_comment" => $_POST['crewAssignments_comment']]))) finish(false, ["code"=> "COMMENT_UPDATE_FAIL", "message" => "Nem sikerült a komment frissítése"]); 
    }
    finish(true);
}


/** @OA\Post(
 *     path="/projects/crew/edit.php", 
 *     summary="Edit Crew Assignment", 
 *     description="Edit a crew assignment  
Requires Instance Permission PROJECTS:PROJECT_CREW:EDIT
", 
 *     operationId="editCrewAssignment", 
 *     tags={"crew"}, 
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
 *         name="crewAssignments_id",
 *         in="query",
 *         description="Crew Assignment ID",
 *         required="true", 
 *         @OA\Schema(
 *             type="number"), 
 *         ), 
 *     @OA\Parameter(
 *         name="crewAssignments_role",
 *         in="query",
 *         description="Crew Assignment Role",
 *         required="true", 
 *         @OA\Schema(
 *             type="string"), 
 *         ), 
 * )
 */