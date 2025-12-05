<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inventory.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$itemName = trim($_POST['item_name'] ?? '');
$category = trim($_POST['category'] ?? '');
$quantity = (int) ($_POST['quantity'] ?? 0);
$reorderLevel = (int) ($_POST['reorder_level'] ?? 0);
$unitCostRaw = trim((string) ($_POST['unit_cost'] ?? ''));
$unitCost = $unitCostRaw === '' ? '' : number_format((float) $unitCostRaw, 2, '.', '');
$supplier = trim($_POST['supplier'] ?? '');
$status = $_POST['status'] ?? 'In stock';
$notes = trim($_POST['notes'] ?? '');
$timestamp = time();

if ($itemName === '') {
    header('Location: inventory.php?status=error');
    exit;
}

$db = get_db();

if ($id > 0) {
    $stmt = $db->prepare(
        'UPDATE inventory_items
         SET item_name = ?, category = ?, quantity = ?, reorder_level = ?, unit_cost = NULLIF(?, ""), supplier = ?, status = ?, notes = ?, updated_at = ?
         WHERE id = ?'
    );
    if (!$stmt) {
        header('Location: inventory.php?status=error');
        exit;
    }
    $stmt->bind_param(
        'ssiissssii',
        $itemName,
        $category,
        $quantity,
        $reorderLevel,
        $unitCost,
        $supplier,
        $status,
        $notes,
        $timestamp,
        $id
    );
    $stmt->execute();
    $stmt->close();
    header('Location: inventory.php?status=updated');
    exit;
}

$stmt = $db->prepare(
    'INSERT INTO inventory_items (item_name, category, quantity, reorder_level, unit_cost, supplier, status, notes, created_at, updated_at)
     VALUES (?, ?, ?, ?, NULLIF(?, ""), ?, ?, ?, ?, ?)'
);
if (!$stmt) {
    header('Location: inventory.php?status=error');
    exit;
}
$stmt->bind_param(
    'ssiissssii',
    $itemName,
    $category,
    $quantity,
    $reorderLevel,
    $unitCost,
    $supplier,
    $status,
    $notes,
    $timestamp,
    $timestamp
);
$stmt->execute();
$stmt->close();

header('Location: inventory.php?status=created');
exit;
