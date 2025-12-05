<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: appointments.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$petName = trim($_POST['pet_name'] ?? '');
$ownerName = trim($_POST['owner_name'] ?? '');
$ownerContact = trim($_POST['owner_contact'] ?? '');
$appointmentDate = trim($_POST['appointment_date'] ?? '');
$reason = trim($_POST['reason'] ?? '');
$status = $_POST['status'] ?? 'Scheduled';
$notes = trim($_POST['notes'] ?? '');
$timestamp = time();

if ($petName === '' || $appointmentDate === '') {
    header('Location: appointments.php?status=error');
    exit;
}

$db = get_db();

if ($id) {
    $stmt = $db->prepare(
        'UPDATE appointments
         SET pet_name = ?, owner_name = ?, owner_contact = ?, appointment_date = ?, reason = ?, status = ?, notes = ?, updated_at = ?
         WHERE id = ?'
    );
    $stmt->bind_param(
        'sssssssii',
        $petName,
        $ownerName,
        $ownerContact,
        $appointmentDate,
        $reason,
        $status,
        $notes,
        $timestamp,
        $id
    );
    $stmt->execute();
    $stmt->close();

    header('Location: appointments.php?status=updated');
    exit;
}

$stmt = $db->prepare(
    'INSERT INTO appointments (pet_name, owner_name, owner_contact, appointment_date, reason, status, notes, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'sssssssii',
    $petName,
    $ownerName,
    $ownerContact,
    $appointmentDate,
    $reason,
    $status,
    $notes,
    $timestamp,
    $timestamp
);
$stmt->execute();
$stmt->close();

header('Location: appointments.php?status=created');
exit;


