<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$from_city = $_GET['from'] ?? '';
$to_city = $_GET['to'] ?? '';
$date = $_GET['date'] ?? date('Y-m-d');

if (empty($from_city) || empty($to_city)) {
    echo json_encode(['success' => false, 'message' => 'Please select origin and destination']);
    exit;
}

try {
    $sql = "SELECT 
                t.id as trip_id,
                t.departure_time,
                t.arrival_time,
                t.price,
                t.date,
                t.available_seats,
                b.id as bus_id,
                b.plate_number,
                b.bus_type,
                c.name as company_name,
                cf.name as from_city,
                ct.name as to_city,
                r.distance,
                r.duration
            FROM trips t
            JOIN buses b ON t.bus_id = b.id
            JOIN companies c ON b.company_id = c.id
            JOIN routes r ON t.route_id = r.id
            JOIN cities cf ON r.from_city_id = cf.id
            JOIN cities ct ON r.to_city_id = ct.id
            WHERE cf.name = ? AND ct.name = ? AND t.date = ? AND t.status = 'scheduled'
            ORDER BY t.departure_time";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$from_city, $to_city, $date]);
    $trips = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'trips' => $trips,
        'count' => count($trips)
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Search failed: ' . $e->getMessage()]);
}
?>