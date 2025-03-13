<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Jogosultság és paraméterek ellenőrzése
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") || !isset($_POST['projects_id']) || !isset($_POST['comment_id'])) {
    die(json_encode(["error" => "Hozzáférés megtagadva vagy hiányzó paraméterek"]));
}

// Projekt létezésének ellenőrzése
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getOne("projects", ["projects.projects_id"]);
if (!$project) die(json_encode(["error" => "A projekt nem található"]));

// Komment lekérdezése az auditLog-ból
$DBLIB->where("auditLog.auditLog_targetID", $_POST['comment_id']);
$DBLIB->where("auditLog.projects_id", $_POST['projects_id']);
$comment = $DBLIB->getOne("auditLog", ["auditLog.auditLog_text"]);

if (!$comment) {
    die(json_encode(["error" => "A komment nem található"]));
}

// Komment visszaküldése
echo json_encode(["comment_text" => $comment['auditLog.auditLog_text']]);
?>
