<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Check permissions and validate inputs
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") || !isset($_POST['projects_id']) || !isset($_POST['comment_id'])) {
    die("404");
}

// Verify the project exists
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id"]);

if (!$project) {
    die("404");
}

// Find the corresponding audit log entry for the comment
$DBLIB->where("action", "QUICKCOMMENT");
$DBLIB->where("projects_id", $_POST['projects_id']);
$DBLIB->where("message", $_POST['comment_id']); // Assuming the message contains the comment text
$logComment = $DBLIB->getone("audit_log", ["log_id", "message", "projects_id"]);

if (!$logComment) {
    die("404"); // Comment not found in logs
}

// Delete the comment from the log
$DBLIB->where("log_id", $logComment['log_id']);
$delete = $DBLIB->delete("audit_log");

if (!$delete) {
    die("500"); // Error while deleting from logs
}

// Log the action
$bCMS->auditLog("DELETE_QUICKCOMMENT", "projects", "Deleted comment from audit logs: " . $bCMS->cleanString($_POST['comment_id']), $AUTH->data['users_userid'], null, $_POST['projects_id']);

// Return success response
finish(true);
