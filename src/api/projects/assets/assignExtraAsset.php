<?php
require_once __DIR__ . '/../../apiHeadSecure.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assetId = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;
    $projectId = isset($_POST['project_id']) ? intval($_POST['project_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    $action = $_POST['action'] ?? 'add'; // default: add

    if ($assetId && $projectId && $quantity >= 0) {
        $existing = $DBLIB
            ->where('tempStock_assets_id', $assetId)
            ->where('tempStock_project_id', $projectId)
            ->getOne('tempStockAssets');

        if ($existing) {
            if ($action === 'add') {
                $newQuantity = $existing['tempStock_assets_quantity'] + $quantity;
                $DBLIB->where('id', $existing['id'])->update('tempStockAssets', [
                    'tempStock_assets_quantity' => $newQuantity
                ]);
            } elseif ($action === 'remove') {
                $newQuantity = $existing['tempStock_assets_quantity'] - $quantity;
                if ($newQuantity < 0) {
                } elseif ($newQuantity === 0) {
                    $DBLIB->where('id', $existing['id'])->delete('tempStockAssets');
                } else {
                    $DBLIB->where('id', $existing['id'])->update('tempStockAssets', [
                        'tempStock_assets_quantity' => $newQuantity
                    ]);
                }
            } elseif ($action === 'remove_all') {
                $DBLIB->where('id', $existing['id'])->delete('tempStockAssets');
            }
        } else {
            if ($action === 'add' && $quantity > 0) {
                $DBLIB->insert('tempStockAssets', [
                    'tempStock_assets_id' => $assetId,
                    'tempStock_project_id' => $projectId,
                    'tempStock_assets_quantity' => $quantity,
                ]);
            }
        }
    }
}
