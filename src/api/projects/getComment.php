<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['projects_id']) or !isset($_POST['comment_id'])) die("404");

// Projekt lekérdezése
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);
if (!$project) die("404");

// Audit log lekérdezése egyedi komment alapján (uniqueID)
$DBLIB->where("auditLog.auditLog_deleted", 0);
$DBLIB->where("auditLog.projects_id", $project['projects_id']);
$DBLIB->where("auditLog.auditLog_actionType", "QUICKCOMMENT");
$DBLIB->where("auditLog.auditLog_targetID", $_POST['comment_id']);  // Szűrés uniqueID alapján
$DBLIB->join("users", "auditLog.users_userid=users.users_userid", "LEFT");
$auditLog = $DBLIB->get("auditLog", null, ["auditLog.auditLog_actionData"]);

if (!$auditLog) die("404"); // Ha nincs komment, 404-es hibával tér vissza

// Csak az első kommentet adja vissza
$comment = $auditLog[0];

// Visszaadás a komment szöveggel
finish(true, null, [
    'comment_text' => $comment['auditLog_actionData'] ?? '', // Ha a komment szövege null, üres mező
]);
