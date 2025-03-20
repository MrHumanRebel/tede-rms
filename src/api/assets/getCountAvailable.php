<?php
require_once __DIR__ . '/../apiHeadSecure.php';


$dates = explode(" - ", $_GET['dates']);
if (count($dates) == 2) {
    $dateStart = $dates[0];
    $dateEnd = $dates[1];
} else {
    $dateStart = false;
    $dateEnd = false;
}

$results = [
    'count' => 0,
    'countBlocked' => 0,
    'countAvailable' => 0,
    'availableAssets' => [],
    'blockedAssets' => []
];

$RETURN = [
    "PROJECT" => [
        "ID" => false,
        "NAME" => false
    ]
];

//Evaluate dates or project
if ($_POST['project_id'] and $AUTH->instancePermissionCheck("PROJECTS:PROJECT_ASSETS:CREATE:ASSIGN_AND_UNASSIGN")) {
    $DBLIB->where("projects_id", $_POST['project_id']);
    $DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
    $DBLIB->where("projects.projects_deleted", 0);
    $DBLIB->where("projects.projects_dates_deliver_start", NULL, "IS NOT");
    $DBLIB->where("projects.projects_dates_deliver_end", NULL, "IS NOT");
    $thisProject = $DBLIB->getone("projects", ["projects_name", "projects_dates_deliver_start", "projects_dates_deliver_end"]);
    if (!$thisProject) {
        $dateStart = false;
        $dateEnd = false;
    } else {
        $dateStart = strtotime($thisProject['projects_dates_deliver_start']);
        $dateEnd = strtotime($thisProject['projects_dates_deliver_end']);
        $RETURN['PROJECT']['ID'] = $_POST['project_id'];
        $RETURN['PROJECT']['NAME'] = $thisProject['projects_name'];
    }
} elseif ($dateStart and $dateEnd) {
    $dateStart = strtotime($dateStart);
    $dateEnd = strtotime($dateEnd);
    if ($dateEnd <= $dateStart) {
        $dateStart = false;
        $dateEnd = false;
    }
} else {
    $dateStart = false;
    $dateEnd = false;
}

$DBLIB->where("assets.assetTypes_id", $_POST['assets_id']);
$assets = $DBLIB->get("assets");

foreach ($assets as $asset) {
    $DBLIB->where("assets_id", $asset['assets_id']);
    $DBLIB->orderBy("assets.assets_tag", "ASC");
    $assetTags = $DBLIB->get("assets", null, ["assets_id", "assets_tag"]);

    if (!$assetTags) continue;

    $count = count($assetTags);
    $countBlocked = 0;
    $availableAssets = [];
    $blockedAssets = []; // Ensure this is initialized as an empty array

    foreach ($assetTags as $tag) {
        if ($dateStart and $dateEnd) {
            // Check availability
            $DBLIB->where("assets_id", $tag['assets_id']);
            $DBLIB->where("assetsAssignments.assetsAssignments_deleted", 0);
            $DBLIB->join("projects", "assetsAssignments.projects_id=projects.projects_id", "LEFT");
            $DBLIB->where("projects.projects_deleted", 0);
            $DBLIB->where("((projects_dates_deliver_start >= '" . date("Y-m-d H:i:s", $dateStart)  . "' AND projects_dates_deliver_start <= '" . date("Y-m-d H:i:s", $dateEnd) . "') OR (projects_dates_deliver_end >= '" . date("Y-m-d H:i:s", $dateStart) . "' AND projects_dates_deliver_end <= '" . date("Y-m-d H:i:s", $dateEnd) . "') OR (projects_dates_deliver_end >= '" . date("Y-m-d H:i:s", $dateEnd) . "' AND projects_dates_deliver_start <= '" . date("Y-m-d H:i:s", $dateStart) . "'))");
            $DBLIB->join("projectsStatuses", "projects.projectsStatuses_id=projectsStatuses.projectsStatuses_id", "LEFT");
            if ($RETURN['PROJECT']['ID']) {
                // If a project is being searched for specifically, check if the asset is assigned to that project
                $DBLIB->where("(projectsStatuses.projectsStatuses_assetsReleased = 0 OR projects.projects_id = '" . $RETURN['PROJECT']['ID'] . "')");
            } else {
                $DBLIB->where("projectsStatuses.projectsStatuses_assetsReleased", 0);
            }
            $tag['assignment'] = $DBLIB->get("assetsAssignments", null, ["assetsAssignments.assetsAssignments_id", "assetsAssignments.projects_id", "projects.projects_name", "projectsStatuses.projectsStatuses_assetsReleased"]);
        }

        // Check for blocked assets
        $tag['flagsblocks'] = assetFlagsAndBlocks($tag['assets_id']);
        if ($tag['assignment'] || $tag['flagsblocks']['COUNT']['BLOCK'] > 0) {
            $countBlocked++;
            $blockedAssets[] = $tag['assets_id'];
        } else {
            // Ha az eszköz nincs blokkolva vagy lefoglalva, akkor elérhető
            $availableAssets[] = $tag['assets_id'];
        }
    }

    $results['count'] += $count;
    $results['countBlocked'] += $countBlocked;
    $results['countAvailable'] += ($count - $countBlocked);

    $results['availableAssets'] = array_merge($results['availableAssets'], $availableAssets);
    $results['blockedAssets'] = array_merge($results['blockedAssets'], (array)$blockedAssets); // Ensure blockedAssets is an array
}

// Return the response with the necessary data
finish(true, null, [$results]);
