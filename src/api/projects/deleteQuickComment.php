<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['comment_id']) or !isset($_POST['projects_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);

if (!$project) die("404");

// Check if comment exists
$DBLIB->where("auditLog_id", $_POST['comment_id']);
$comment = $DBLIB->getone("auditLog", ["auditLog_id", "auditLog_actionType", "auditLog_actionData"]);

if (!$comment || $comment['auditLog_actionType'] !== 'QUICKCOMMENT') {
    die("404");
}

// Delete comment from audit log
$DBLIB->where("auditLog_id", $_POST['comment_id']);
if (!$DBLIB->delete("auditLog")) {
    die("Could not delete comment");
}

// Log the deletion in audit log
$bCMS->auditLog("DELETEQUICKCOMMENT", "projects", "Comment deleted: " . $comment['auditLog_actionData'], $AUTH->data['users_userid'], null, $_POST['projects_id']);

finish(true, null, ["projects_id" => $project['projects_id']]);
?>
