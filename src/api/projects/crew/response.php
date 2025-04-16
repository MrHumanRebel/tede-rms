<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

// Check if the user has the permission to edit the crew assignment
if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT")) finish(false, ["error" => "PERMISSION_ERROR", "message" => "Nincs jogosultságod a módosításhoz"]);

// Check if the required parameters are set
if (!isset($_POST['assignmentId']) || !isset($_POST['response']) || !isset($_POST['userId']) || !isset($_POST['projectId'])) {
    finish(false, ["error" => "INVALID_PARAMETERS", "message" => "Hiányzó paraméterek"]);
}

$assignmentId = intval($_POST['assignmentId']);
$response = intval($_POST['response']);
$userId = intval($_POST['userId']);
$projectId = intval($_POST['projectId']);

// Validate response value (must be 1 for accepted or 2 for rejected)
if ($response !== 1 && $response !== 2) {
    finish(false, ["error" => "INVALID_RESPONSE", "message" => "Érvénytelen válasz érték"]);
}

// Get the crew assignment data from the database
$DBLIB->where("crewAssignments.crewAssignments_id", $assignmentId);
$DBLIB->where("crewAssignments.projects_id", $projectId);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$assignment = $DBLIB->getone("crewAssignments", ["crewAssignments.crewAssignments_id", "crewAssignments.crewAssignments_response", "crewAssignments.users_userid", "crewAssignments.projects_id"]);

if (!$assignment) {
    finish(false, ["error" => "NO_ASSIGNMENT", "message" => "Nincs ilyen hozzárendelés"]);
}

// Update the crew assignment response
$DBLIB->where("crewAssignments_id", $assignment['crewAssignments_id']);
if (!$DBLIB->update("crewAssignments", ["crewAssignments_response" => $response])) {
    finish(false, ["error" => "UPDATE_FAIL", "message" => "Nem sikerült frissíteni a választ"]);
}

// Optionally, log the change in the audit log
$bCMS->auditLog("UPDATE-CREW-RESPONSE", "crewAssignments", $assignment['crewAssignments_id'], $AUTH->data['users_userid'], null, $assignment['projects_id']);

// Respond with a success message
finish(true, ["message" => "Sikeres frissítés"]);
