<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

// Get all trips with details
$stmt = $pdo->query("
    SELECT t.*, 
           cf.name as from_city, 
           ct.name as to_city,
           b.plate_number,
           b.bus_type,
           b.total_capacity,
           comp.name as company_name,
           d.fullname as driver_name
    FROM trips t
    JOIN routes r ON t.route_id = r.id
    JOIN cities cf ON r.from_city_id = cf.id
    JOIN cities ct ON r.to_city_id = ct.id
    JOIN buses b ON t.bus_id = b.id
    JOIN companies comp ON b.company_id = comp.id
    LEFT JOIN drivers d ON t.driver_id = d.id
    ORDER BY t.trip_date DESC, t.departure_time DESC
");
$trips = $stmt->fetchAll();

// Get buses for add form
$buses = $pdo->query("SELECT b.*, comp.name as company_name FROM buses b JOIN companies comp ON b.company_id = comp.id WHERE b.status = 'active'")->fetchAll();

// Get routes for add form
$routes = $pdo->query("SELECT r.*, cf.name as from_city, ct.name as to_city FROM routes r JOIN cities cf ON r.from_city_id = cf.id JOIN cities ct ON r.to_city_id = ct.id")->fetchAll();

// Get drivers for add form
$drivers = $pdo->query("SELECT id, fullname FROM drivers WHERE status = 'active'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Trips - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        
        .admin-container { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 280px;
            background: #0f172a;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { display: flex; align-items: center; gap: 0.75rem; }
        .sidebar-header i { color: #e67e22; }
        .admin-info { padding: 1rem 1.5rem; background: rgba(255,255,255,0.05); margin: 1rem; border-radius: 12px; }
        .sidebar-nav { padding: 1rem 0; }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: #e67e22; color: white; }
        
        .main-content { flex: 1; margin-left: 280px; padding: 1.5rem; }
        
        .top-bar {
            background: white;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .add-btn {
            background: #e67e22;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
        }
        .table-container {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        .action-buttons { display: flex; gap: 0.5rem; }
        .edit-btn, .delete-btn { padding: 0.25rem 0.5rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; cursor: pointer; border: none; }
        .edit-btn { background: #dbeafe; color: #2563eb; }
        .delete-btn { background: #fee2e2; color: #dc2626; }
        .status-badge { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
        .status-scheduled { background: #dbeafe; color: #2563eb; }
        .status-ongoing { background: #fed7aa; color: #c2410c; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .logout-btn { background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-content h3 { margin-bottom: 1rem; }
        .modal-content input, .modal-content select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .modal-buttons { display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1rem; }
        .modal-buttons button { padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; }
        .save-btn { background: #e67e22; color: white; border: none; }
        .cancel-btn { background: #e2e8f0; border: none; cursor: pointer; }
        
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-bus"></i> EthioGo Admin</h2>
            </div>
            <div class="admin-info">
                <div class="admin-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="admin-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_drivers.php"><i class="fas fa-users"></i> Manage Drivers</a>
                <a href="manage_trips.php" class="active"><i class="fas fa-calendar-alt"></i> Manage Trips</a>
                <a href="manage_passengers.php"><i class="fas fa-user-friends"></i> Passengers</a>
                <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <h2>Manage Trips</h2>
                <div style="display: flex; gap: 1rem;">
                    <button class="add-btn" onclick="openAddModal()"><i class="fas fa-plus"></i> Add Trip</button>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Route</th>
                            <th>Bus</th>
                            <th>Company</th>
                            <th>Driver</th>
                            <th>Date</th>
                            <th>Departure</th>
                            <th>Arrival</th>
                            <th>Price</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($trips as $trip): ?>
                        <tr>
                            <td><?php echo $trip['id']; ?></td>
                            <td><?php echo $trip['from_city']; ?> → <?php echo $trip['to_city']; ?></td>
                            <td><?php echo $trip['plate_number']; ?> (<?php echo ucfirst($trip['bus_type']); ?>)</td>
                            <td><?php echo $trip['company_name']; ?></td>
                            <td><?php echo $trip['driver_name'] ?? 'Not Assigned'; ?></td>
                            <td><?php echo date('M d, Y', strtotime($trip['trip_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($trip['departure_time'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($trip['arrival_time'])); ?></td>
                            <td>ETB <?php echo number_format($trip['price'], 2); ?></td>
                            <td><?php echo $trip['available_seats']; ?>/<?php echo $trip['total_capacity']; ?></td>
                            <td><span class="status-badge status-<?php echo $trip['status']; ?>"><?php echo ucfirst($trip['status']); ?></span></td>
                            <td class="action-buttons">
                                <button class="edit-btn" onclick="openEditModal(<?php echo $trip['id']; ?>)"><i class="fas fa-edit"></i> Edit</button>
                                <a href="delete_trip.php?id=<?php echo $trip['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this trip?')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Trip Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h3>Add New Trip</h3>
            <form id="addTripForm" action="add_trip.php" method="POST">
                <select name="route_id" required>
                    <option value="">Select Route</option>
                    <?php foreach($routes as $route): ?>
                        <option value="<?php echo $route['id']; ?>"><?php echo $route['from_city']; ?> → <?php echo $route['to_city']; ?> (<?php echo $route['distance']; ?> km)</option>
                    <?php endforeach; ?>
                </select>
                <select name="bus_id" required>
                    <option value="">Select Bus</option>
                    <?php foreach($buses as $bus): ?>
                        <option value="<?php echo $bus['id']; ?>"><?php echo $bus['plate_number']; ?> (<?php echo $bus['company_name']; ?>) - <?php echo $bus['total_capacity']; ?> seats</option>
                    <?php endforeach; ?>
                </select>
                <select name="driver_id">
                    <option value="">Assign Driver (Optional)</option>
                    <?php foreach($drivers as $driver): ?>
                        <option value="<?php echo $driver['id']; ?>"><?php echo $driver['fullname']; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="trip_date" required>
                <input type="time" name="departure_time" required>
                <input type="time" name="arrival_time" required>
                <input type="number" name="price" placeholder="Price (ETB)" step="0.01" required>
                <div class="modal-buttons">
                    <button type="button" class="cancel-btn" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="save-btn">Save Trip</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
        }
        
        function openEditModal(tripId) {
            window.location.href = 'edit_trip.php?id=' + tripId;
        }
    </script>
</body>
</html>