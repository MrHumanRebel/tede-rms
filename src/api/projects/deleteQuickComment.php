<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:VIEW") || !isset($_POST['projects_id']) || !isset($_POST['comment_id'])) die("404");

$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $_POST['projects_id']);
$project = $DBLIB->getone("projects", ["projects.projects_id"]);
if (!$project) die("404");

$DBLIB->where("comments.comment_id", $_POST['comment_id']);
$DBLIB->where("comments.projects_id", $_POST['projects_id']);
if (!$DBLIB->delete("comments")) die("500");

$bCMS->auditLog("DELETE_QUICKCOMMENT", "projects", "Deleted comment: " . $bCMS->cleanString($_POST['comment_id']), $AUTH->data['users_userid'], null, $_POST['projects_id']);

finish(true, null, ["projects_id" => $project]);
