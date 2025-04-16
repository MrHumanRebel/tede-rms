<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

// Check if the user has the permission to edit the crew assignment
if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_CREW:EDIT")) {
    finish(false, ["error" => "PERMISSION_ERROR", "message" => "Nincs jogosultságod a módosításhoz"]);
}

$response = intval($_POST['response']);
$userId = intval($_POST['userId']);
$projectId = intval($_POST['projectId']);

// Validate response value (must be 1 for accepted or 2 for rejected)
if ($response !== 1 && $response !== 2) {
    finish(false, ["error" => "INVALID_RESPONSE", "message" => "Érvénytelen válasz érték"]);
}

// Get the crew assignment data from the database
$DBLIB->where("crewAssignments.projects_id", $projectId);
$DBLIB->where("crewAssignments.users_userid", $userId);
$DBLIB->where("crewAssignments.crewAssignments_deleted", 0);
$DBLIB->join("projects", "crewAssignments.projects_id=projects.projects_id", "LEFT");
$DBLIB->where("projects.projects_deleted", 0);
$assignment = $DBLIB->getOne("crewAssignments", [
    "crewAssignments.crewAssignments_id",
    "crewAssignments.crewAssignments_response",
    "crewAssignments.users_userid",
    "crewAssignments.projects_id"
]);

if (!$assignment) {
    finish(false, ["error" => "NO_ASSIGNMENT", "message" => "Nincs ilyen hozzárendelés"]);
}

// Check if the user is authorized to update the assignment
if ($assignment["users_userid"] !== $AUTH->data['users_userid']) {
    finish(false, ["error" => "PERMISSION_ERROR", "message" => "Csak a saját hozzárendelésedet módosíthatod"]);
}

// Update the crew assignment response
$DBLIB->where("crewAssignments_id", $assignment['crewAssignments_id']);
$success = $DBLIB->update("crewAssignments", [
    "crewAssignments_response" => $response
]);

if (!$success) {
    finish(false, ["error" => "UPDATE_FAILED", "message" => "Nem sikerült frissíteni a hozzárendelést"]);
}

// Audit log
$bCMS->auditLog("UPDATE-CREW-RESPONSE", "crewAssignments", $assignment['crewAssignments_id'], $AUTH->data['users_userid'], null, $assignment['projects_id']);

// Respond with a success message
finish(true, ["message" => "Sikeres frissítés"]);
