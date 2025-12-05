<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: inventory.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: inventory.php?status=error');
    exit;
}

$db = get_db();
$stmt = $db->prepare('DELETE FROM inventory_items WHERE id = ?');
if (!$stmt) {
    header('Location: inventory.php?status=error');
    exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: inventory.php?status=deleted');
exit;


