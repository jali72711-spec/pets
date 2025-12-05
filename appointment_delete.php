<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: appointments.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if ($id <= 0) {
    header('Location: appointments.php?status=error');
    exit;
}

$db = get_db();
$stmt = $db->prepare('DELETE FROM appointments WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: appointments.php?status=deleted');
exit;


