<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

$trip_id = $_GET['id'] ?? 0;

if ($trip_id) {
    $stmt = $pdo->prepare("DELETE FROM trips WHERE id = ?");
    $stmt->execute([$trip_id]);
}

header('Location: manage_trips.php');
exit;
?>