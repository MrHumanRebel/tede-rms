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

// Check if the comment exists
$DBLIB->where("comments.comment_id", $_POST['comment_id']);
$DBLIB->where("comments.projects_id", $_POST['projects_id']);
$comment = $DBLIB->getone("comments", ["comments.comment_id"]);
if (!$comment) die("404");

// Delete the comment
$DBLIB->where("comment_id", $_POST['comment_id']);
$DBLIB->where("projects_id", $_POST['projects_id']);
if ($DBLIB->delete("comments")) {
    // Log the deletion
    $bCMS->auditLog("QUICKCOMMENT DELETE", "projects", "Deleted comment ID: " . $_POST['comment_id'], $AUTH->data['users_userid'], null, $_POST['projects_id'], null);
    finish(true, "Comment deleted successfully", ["projects_id" => $_POST['projects_id']]);
} else {
    finish(false, "Failed to delete comment");
}
?>
