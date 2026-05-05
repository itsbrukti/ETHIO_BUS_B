<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

$trip_id = $_GET['id'] ?? 0;

if (!$trip_id) {
    header('Location: manage_trips.php');
    exit;
}

// Get trip data
$stmt = $pdo->prepare("
    SELECT t.*, b.total_capacity 
    FROM trips t
    JOIN buses b ON t.bus_id = b.id
    WHERE t.id = ?
");
$stmt->execute([$trip_id]);
$trip = $stmt->fetch();

if (!$trip) {
    header('Location: manage_trips.php');
    exit;
}

// Get buses for edit form
$buses = $pdo->query("SELECT b.*, comp.name as company_name FROM buses b JOIN companies comp ON b.company_id = comp.id WHERE b.status = 'active'")->fetchAll();

// Get routes for edit form
$routes = $pdo->query("SELECT r.*, cf.name as from_city, ct.name as to_city FROM routes r JOIN cities cf ON r.from_city_id = cf.id JOIN cities ct ON r.to_city_id = ct.id")->fetchAll();

// Get drivers for edit form
$drivers = $pdo->query("SELECT id, fullname FROM drivers WHERE status = 'active'")->fetchAll();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $route_id = $_POST['route_id'];
    $bus_id = $_POST['bus_id'];
    $driver_id = $_POST['driver_id'] ?: null;
    $trip_date = $_POST['trip_date'];  // CHANGED: from 'date' to 'trip_date'
    $departure_time = $_POST['departure_time'];
    $arrival_time = $_POST['arrival_time'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    
    // Get new bus capacity
    $stmt = $pdo->prepare("SELECT total_capacity FROM buses WHERE id = ?");
    $stmt->execute([$bus_id]);
    $bus = $stmt->fetch();
    $new_capacity = $bus['total_capacity'];
    
    // Calculate new available seats if bus changed
    $booked_seats = $trip['total_capacity'] - $trip['available_seats'];
    $available_seats = $new_capacity - $booked_seats;
    
    // CHANGED: column name from 'date' to 'trip_date'
    $stmt = $pdo->prepare("UPDATE trips SET route_id=?, bus_id=?, driver_id=?, trip_date=?, departure_time=?, arrival_time=?, price=?, status=?, available_seats=? WHERE id=?");
    $stmt->execute([$route_id, $bus_id, $driver_id, $trip_date, $departure_time, $arrival_time, $price, $status, $available_seats, $trip_id]);
    
    $message = 'Trip updated successfully!';
    
    // Refresh data
    $stmt = $pdo->prepare("
        SELECT t.*, b.total_capacity 
        FROM trips t
        JOIN buses b ON t.bus_id = b.id
        WHERE t.id = ?
    ");
    $stmt->execute([$trip_id]);
    $trip = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .container { max-width: 600px; margin: 2rem auto; background: white; border-radius: 24px; padding: 2rem; }
        h2 { margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input, select { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 10px; }
        button { background: #e67e22; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 10px; cursor: pointer; width: 100%; font-size: 1rem; }
        .message { padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #dc2626; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #e67e22; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-edit"></i> Edit Trip</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Route</label>
                <select name="route_id" required>
                    <?php foreach($routes as $route): ?>
                        <option value="<?php echo $route['id']; ?>" <?php echo $route['id'] == $trip['route_id'] ? 'selected' : ''; ?>>
                            <?php echo $route['from_city']; ?> → <?php echo $route['to_city']; ?> (<?php echo $route['distance']; ?> km)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Bus</label>
                <select name="bus_id" required>
                    <?php foreach($buses as $bus): ?>
                        <option value="<?php echo $bus['id']; ?>" <?php echo $bus['id'] == $trip['bus_id'] ? 'selected' : ''; ?>>
                            <?php echo $bus['plate_number']; ?> (<?php echo $bus['company_name']; ?>) - <?php echo $bus['total_capacity']; ?> seats
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Driver (Optional)</label>
                <select name="driver_id">
                    <option value="">No Driver Assigned</option>
                    <?php foreach($drivers as $driver): ?>
                        <option value="<?php echo $driver['id']; ?>" <?php echo $driver['id'] == $trip['driver_id'] ? 'selected' : ''; ?>>
                            <?php echo $driver['fullname']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="trip_date" value="<?php echo $trip['trip_date']; ?>" required>
            </div>
            <div class="form-group">
                <label>Departure Time</label>
                <input type="time" name="departure_time" value="<?php echo $trip['departure_time']; ?>" required>
            </div>
            <div class="form-group">
                <label>Arrival Time</label>
                <input type="time" name="arrival_time" value="<?php echo $trip['arrival_time']; ?>" required>
            </div>
            <div class="form-group">
                <label>Price (ETB)</label>
                <input type="number" name="price" value="<?php echo $trip['price']; ?>" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="scheduled" <?php echo $trip['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                    <option value="ongoing" <?php echo $trip['status'] == 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="completed" <?php echo $trip['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $trip['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit">Update Trip</button>
        </form>
        <a href="manage_trips.php" class="back-link">← Back to Trips List</a>
    </div>
</body>
</html>