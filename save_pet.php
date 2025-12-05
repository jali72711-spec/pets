<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$name = trim($_POST['name'] ?? '');
$species = trim($_POST['species'] ?? '');
$breed = trim($_POST['breed'] ?? '');
$age = trim($_POST['age'] ?? '');
$weight = trim($_POST['weight'] ?? '');
$ownerName = trim($_POST['owner'] ?? '');
$ownerContact = trim($_POST['contact'] ?? '');
$notes = trim($_POST['notes'] ?? '');
$createdAt = time();

if ($name === '' || $species === '') {
    header('Location: index.php?status=error');
    exit;
}

try {
    $db = get_db();

    if ($id > 0) {
        $stmt = $db->prepare(
            'UPDATE pets
             SET name = ?, species = ?, breed = ?, age = NULLIF(?, ""), weight = NULLIF(?, ""), owner_name = ?, owner_contact = ?, notes = ?
             WHERE id = ?'
        );

        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . $db->error);
        }

        $stmt->bind_param(
            'ssssssssi',
            $name,
            $species,
            $breed,
            $age,
            $weight,
            $ownerName,
            $ownerContact,
            $notes,
            $id
        );
        $stmt->execute();
        $stmt->close();

        header('Location: pets.php?status=updated');
        exit;
    }

    $stmt = $db->prepare(
        'INSERT INTO pets (name, species, breed, age, weight, owner_name, owner_contact, notes, created_at)
         VALUES (?, ?, ?, NULLIF(?, ""), NULLIF(?, ""), ?, ?, ?, ?)'
    );

    if (!$stmt) {
        throw new RuntimeException('Failed to prepare statement: ' . $db->error);
    }

    $stmt->bind_param(
        'ssssssssi',
        $name,
        $species,
        $breed,
        $age,
        $weight,
        $ownerName,
        $ownerContact,
        $notes,
        $createdAt
    );

    $stmt->execute();
    $stmt->close();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    header('Location: index.php?status=error');
    exit;
}

header('Location: pets.php?status=created');
exit;

