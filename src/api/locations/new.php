<?php
require_once __DIR__ . '/../apiHeadSecure.php';

if (!$AUTH->instancePermissionCheck("LOCATIONS:CREATE")) die("404");

$array = [];
foreach ($_POST['formData'] as $item) {
    if ($item['value'] == '') $item['value'] = null;
    $array[$item['name']] = $item['value'];
}
if (strlen($array['locations_name']) <1) finish(false, ["code" => "PARAM-ERROR", "message"=> "No data for action"]);
$array['instances_id'] = $AUTH->data['instance']['instances_id'];

$location = $DBLIB->insert("locations", $array);
if (!$location) finish(false);

$locationBarcodeData = [
    "locationsBarcodes_value" => "L" . $location,
    "locationsBarcodes_type" => "QR_CODE",
    "locations_id" => $location,
    "users_userid" => $AUTH->data['users_userid'],
    "locationsBarcodes_added" => date("Y-m-d H:i:s")
];
$locationBarcode = $DBLIB->insert("locationsBarcodes", $locationBarcodeData);

$bCMS->auditLog("INSERT", "locations", json_encode($array), $AUTH->data['users_userid']);
finish(true);

/** @OA\Post(
 *     path="/locations/new.php", 
 *     summary="New Location", 
 *     description="Create a new location  
Requires Instance Permission LOCATION:CREATE
", 
 *     operationId="newLocation", 
 *     tags={"locations"}, 
 *     @OA\Response(
 *         response="200", 
 *         description="Success",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *                 @OA\Property(
 *                     property="response", 
 *                     type="array", 
 *                     description="A null Array",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Auth Fail",
 *     ), 
 *     @OA\Response(
 *         response="default", 
 *         description="Error",
 *         @OA\MediaType(
 *             mediaType="application/json", 
 *             @OA\Schema( 
 *                 type="object", 
 *                 @OA\Property(
 *                     property="result", 
 *                     type="boolean", 
 *                     description="Whether the request was successful",
 *                 ),
 *             ),
 *         ),
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="The location data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="locations_name", 
 *                 type="string", 
 *                 description="The location name",
 *             ),
 *             @OA\Property(
 *                 property="clients_id", 
 *                 type="number", 
 *                 description="The owning Client",
 *             ),
 *             @OA\Property(
 *                 property="locations_address", 
 *                 type="string", 
 *                 description="The location address",
 *             ),
 *             @OA\Property(
 *                 property="locations_subOf", 
 *                 type="number", 
 *                 description="The parent location",
 *             ),
 *             @OA\Property(
 *                 property="locations_notes", 
 *                 type="string", 
 *                 description="The location notes",
 *             ),
 *         ),
 *     ), 
 * )
 */