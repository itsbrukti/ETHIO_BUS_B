<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

$driver_id = $_GET['id'] ?? 0;

if ($driver_id) {
    $stmt = $pdo->prepare("DELETE FROM drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
}

header('Location: manage_drivers.php');
exit;
?>