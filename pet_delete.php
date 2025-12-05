<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pets.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: pets.php?status=error');
    exit;
}

try {
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM pets WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    header('Location: pets.php?status=error');
    exit;
}

header('Location: pets.php?status=deleted');
exit;


