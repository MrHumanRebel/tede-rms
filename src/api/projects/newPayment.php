<?php
require_once __DIR__ . '/../apiHeadSecure.php';
require_once __DIR__ . '/../../common/libs/bCMS/projectFinance.php';
use Money\Currency;
use Money\Money;

if (!$AUTH->instancePermissionCheck("PROJECTS:PROJECT_PAYMENTS:CREATE")) die("404");

// Set UTF-8 header for JSON responses
header('Content-Type: application/json; charset=UTF-8');

// Initialize the array to hold form data
$array = [];
foreach ($_POST['formData'] as $item) {
    $array[$item['name']] = mb_convert_encoding($item['value'], 'UTF-8', 'auto'); // Ensure UTF-8 encoding
}

// Check if the project ID is provided
if (strlen($array['projects_id']) < 1) finish(false, ["code" => "PARAM-ERROR", "message" => "No data for action"]);

// Convert the date to the proper format
$array['payments_date'] = date("Y-m-d H:i:s", strtotime(str_replace(".", "", $array['payments_date']))); // Fix for Hungarian date format

// Ensure payments_quantity has a default value if not provided
if (!$array['payments_quantity']) $array['payments_quantity'] = 1;

// Handle empty values for payments_method, payments_reference, and payments_comment
$array['payments_method'] = $array['payments_method'] ?? null;
$array['payments_reference'] = $array['payments_reference'] ?? null;
$array['payments_comment'] = $array['payments_comment'] ?? null;

use Money\Currencies\ISOCurrencies;
use Money\Parser\DecimalMoneyParser;

$currencies = new ISOCurrencies();
$moneyParser = new DecimalMoneyParser($currencies);
$array['payments_amount'] = $moneyParser->parse($array['payments_amount'], $AUTH->data['instance']['instances_config_currency'])->getAmount();

// Database query to fetch project information
$DBLIB->where("projects.instances_id", $AUTH->data['instance']['instances_id']);
$DBLIB->where("projects.projects_deleted", 0);
$DBLIB->where("projects.projects_id", $array['projects_id']);
$project = $DBLIB->getone("projects", ["projects_id"]);

// If no project found, terminate
if (!$project) finish(false);

// Initialize project finance cacher
$projectFinanceCacher = new projectFinanceCacher($project['projects_id']);

// Insert the payment record into the database
$insert = $DBLIB->insert("payments", $array);
if (!$insert) finish(false);

// Calculate the payment amount based on quantity
$paymentAmount = new Money($array['payments_amount'], new Currency($AUTH->data['instance']['instances_config_currency']));
$paymentAmount = $paymentAmount->multiply($array['payments_quantity']);

// Adjust project finance
$projectFinanceCacher->adjustPayment($array['payments_type'], $paymentAmount, false);

// Log the insert action
$bCMS->auditLog("INSERT", "payments", $insert, $AUTH->data['users_userid'], null, $project['projects_id']);

// Save the finance data and finish with success or failure
if ($projectFinanceCacher->save()) {
    finish(true);
} else {
    finish(false, ["message" => "Finance Cacher Save failed"]);
}

/** @OA\Post(
 *     path="/projects/newPayment.php", 
 *     summary="New Payment", 
 *     description="Create a new project payment  
Requires Instance Permission PROJECTS:PROJECT_PAYMENTS:CREATE
", 
 *     operationId="newPayment", 
 *     tags={"projects"}, 
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
 *             ),
 *         ),
 *     ), 
 *     @OA\Response(
 *         response="404", 
 *         description="Permission Error",
 *     ), 
 *     @OA\Parameter(
 *         name="formData",
 *         in="query",
 *         description="Form Data",
 *         required="true", 
 *         @OA\Schema(
 *             type="object", 
 *             @OA\Property(
 *                 property="projects_id", 
 *                 type="number", 
 *                 description="Project ID",
 *             ),
 *             @OA\Property(
 *                 property="payments_quantity", 
 *                 type="number", 
 *                 description="Payment Quantity",
 *             ),
 *             @OA\Property(
 *                 property="payments_amount", 
 *                 type="string", 
 *                 description="Payment Amount",
 *             ),
 *             @OA\Property(
 *                 property="payments_type", 
 *                 type="string", 
 *                 description="Payment Type",
 *             ),
 *             @OA\Property(
 *                 property="projectsPayments_notes", 
 *                 type="string", 
 *                 description="Payment Notes",
 *             ),
 *         ),
 *     ), 
 * )
 */
