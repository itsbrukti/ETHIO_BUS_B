<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = $_POST['route_id'];
    $bus_id = $_POST['bus_id'];
    $driver_id = $_POST['driver_id'] ?: null;
    $trip_date = $_POST['trip_date'];  // CHANGED: from 'date' to 'trip_date'
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    
    // Get bus capacity
    $stmt = $pdo->prepare("SELECT total_capacity FROM buses WHERE id = ?");
    $stmt->execute([$bus_id]);
    $bus = $stmt->fetch();
    $available_seats = $bus['total_capacity'];
    
    // CHANGED: column name from 'date' to 'trip_date'
    $stmt = $pdo->prepare("INSERT INTO trips (route_id, bus_id, driver_id, trip_date, departure_time, arrival_time, price, available_seats, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
    $stmt->execute([$route_id, $bus_id, $driver_id, $trip_date, $departure_time, $arrival_time, $price, $available_seats]);
}

header('Location: manage_trips.php');
exit;
?>