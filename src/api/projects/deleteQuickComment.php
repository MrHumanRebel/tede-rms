<?php
require_once __DIR__ . '/../apiHeadSecure.php';

// Check permissions and parameters
if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") or !isset($_POST['projects_id']) or !isset($_POST['comment_id'])) {
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
$DBLIB->where("auditLog.auditLog_type", "QUICKCOMMENT");
$DBLIB->where("auditLog.projects_id", $_POST['projects_id']);
$comment = $DBLIB->getone("auditLog", ["auditLog_targetID"]);
if (!$comment) die("404");

// Delete the comment from the auditLog using the targetID
$DBLIB->where("auditLog_targetID", $_POST['comment_id']);
$DBLIB->where("auditLog.auditLog_type", "QUICKCOMMENT");
$DBLIB->where("auditLog.projects_id", $_POST['projects_id']);
if ($DBLIB->delete("auditLog")) {
    // Log the deletion in the audit log
    $bCMS->auditLog("DELETEQUICKCOMMENT", "auditLog", "Comment deleted: " . $_POST['comment_id'], $AUTH->data['users_userid'], null, $_POST['projects_id'], $_POST['comment_id']);
    finish(true, "Comment deleted successfully", ["projects_id" => $project['projects_id']]);
} else {
    die("Error deleting comment");
}
?>
