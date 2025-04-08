<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("PROJECTS:DELETE") or !isset($_POST['projects_id'])) die("404");

// Get all project IDs including subprojects
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("(projects.projects_id = ? OR projects.projects_parent_project_id = ?)", [$_POST['projects_id'], $_POST['projects_id']]);
$projects = $DBLIB->get("projects", null, "projects.projects_id");

if (!$projects) finish(false);

// Extract project IDs
$projectIds = array_column($projects, "projects_id");

// Delete crew assignments for all related projects
$DBLIB->where("projects_id", $projectIds, "IN");
$DBLIB->delete("crewAssignments");

// Mark the projects as deleted
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("(projects.projects_id = ? OR projects.projects_parent_project_id = ?)", [$_POST['projects_id'], $_POST['projects_id']]);
$project = $DBLIB->update("projects", ["projects.projects_deleted" => 1], 1);
if (!$project) finish(false);

$bCMS->auditLog("DELETE", "projects", "Deleted the project and its subprojects", $AUTH->data['users_userid'], null, $_POST['projects_id']);
finish(true);


/**
 *  @OA\Post(
 *      path="/instances/delete.php",
 *      summary="Soft Delete Instance",
 *      description="Soft Delete an Instance
 Requires Server permission INSTANCES:DELETE",
 *      operationId="softDeleteInstance",
 *      tags={"instances"},
 *      @OA\Response(
 *          response="200",
 *          description="Success",
 *          @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema(ref="#/components/schemas/SimpleResponse"),
 *         ),
 *      ),
 *      @OA\Parameter(
 *          name="instances_id",
 *          in="query",
 *          description="Id of instance to delete",
 *          required="true",
 *          @OA\Schema(
 *              type="number",
 *          ),
 *      ),
 *  )
 */
