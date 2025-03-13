<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['comment_id']) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);

if (!$project) die("404");

// Ellenőrizd, hogy létezik-e a megadott komment az audit logban
$DBLIB->where("auditLog_targetID", $_POST['comment_id']);
$comment = $DBLIB->getone("auditLog", ["auditLog_targetID", "auditLog_actionType", "auditLog_actionData"]);

if (!$comment || $comment['auditLog_actionType'] !== 'QUICKCOMMENT') {
    die("404");
}

// Komment törlése az audit logból
$DBLIB->where("auditLog_targetID", $_POST['comment_id']);
if (!$DBLIB->delete("auditLog")) {
    die("Nem sikerült törölni a kommentet.");
}

// A törlést rögzítjük az audit logban
$bCMS->auditLog("DELETEQUICKCOMMENT", "projects", "Komment törölve: " . $comment['auditLog_actionData'], $AUTH->data['users_userid'], null, $_POST['projects_id']);

finish(true, null, ["projects_id" => $project['projects_id']]);
?>
