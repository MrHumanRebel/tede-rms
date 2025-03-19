<?php
require_once __DIR__ . '/../apiHeadSecure.php';

$results = [
    'count' => 0,
    'countBlocked' => 0,
    'countAvailable' => 0,
    'availableAssets' => [] // Új tömb az elérhető eszközök ID-jainak tárolására
];

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
        } else {
            // Ha az eszköz nincs blokkolva vagy lefoglalva, akkor elérhető
            $availableAssets[] = $tag['assets_id'];
        }
    }

    $results['count'] += $count;
    $results['countBlocked'] += $countBlocked;
    $results['countAvailable'] += ($count - $countBlocked);

    // Az elérhető eszközöket hozzáadjuk a fő tömbhöz
    $results['availableAssets'] = array_merge($results['availableAssets'], $availableAssets);
}

// Return the response with the necessary data
finish(true, null, [$results]);
