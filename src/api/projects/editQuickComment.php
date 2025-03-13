<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Check permissions and parameters
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['projects_id']) or !isset($_POST['comment_id']) or !isset($_POST['new_comment_text'])) {
    die("404");
}

// Validate the project exists
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);
if (!$project) die("404");

// Check if the comment exists in the auditLog
$DBLIB->where("auditLog.auditLog_targetID", $_POST['comment_id']);
$DBLIB->where("auditLog.projects_id", $_POST['projects_id']);
$comment = $DBLIB->getone("auditLog", ["auditLog_targetID"]);
if (!$comment) die("404");

// Update the comment in the auditLog
$DBLIB->where("auditLog_targetID", $_POST['comment_id']);
$DBLIB->where("auditLog.projects_id", $_POST['projects_id']);

$update_data = [
    "auditLog_text" => $_POST['new_comment_text'], // New comment text
    "auditLog_updated" => date("Y-m-d H:i:s") // Timestamp of update
];

if ($DBLIB->update("auditLog", $update_data)) {
    // Log the edit in the audit log
    $bCMS->auditLog("EDITQUICKCOMMENT", "auditLog", "Comment edited: " . $_POST['comment_id'], $AUTH->data['users_userid'], null, $_POST['projects_id'], $_POST['comment_id']);
    finish(true, "Comment updated successfully", ["projects_id" => $project['projects_id']]);
} else {
    die("Error updating comment");
}
?>
