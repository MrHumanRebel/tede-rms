<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") || !isset($_POST['projects_id']) || !isset($_POST['comment_id'])) die("404");

// Verify the project exists
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);
if (!$project) die("404");

// Verify the comment exists
$DBLIB->where("comments.comment_id", $_POST['comment_id']);
$DBLIB->where("comments.projects_id", $_POST['projects_id']);
$comment = $DBLIB->getone("comments", ["comment_id", "projects_id"]);
if (!$comment) die("404");

// Delete the comment
$DBLIB->where("comment_id", $comment['comment_id']);
$delete = $DBLIB->delete("comments");
if (!$delete) die("500");

// Log the action
$bCMS->auditLog("DELETE_QUICKCOMMENT", "projects", "Deleted comment: " . $bCMS->cleanString($_POST['comment_id']), $AUTH->data['users_userid'], null, $_POST['projects_id']);

// Return success response
finish(true);
