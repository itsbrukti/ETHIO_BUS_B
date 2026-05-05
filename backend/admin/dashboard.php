<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

// Get statistics
$stats = [];

// Total passengers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM passengers");
$stats['total_passengers'] = $stmt->fetch()['total'];

// Total drivers
$stmt = $pdo->query("SELECT COUNT(*) as total FROM drivers");
$stats['total_drivers'] = $stmt->fetch()['total'];

// Total trips
$stmt = $pdo->query("SELECT COUNT(*) as total FROM trips");
$stats['total_trips'] = $stmt->fetch()['total'];

// Total bookings
$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
$stats['total_bookings'] = $stmt->fetch()['total'];

// Total revenue from bookings
$stmt = $pdo->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'booked'");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

// Upcoming trips today - FIXED: changed 'date' to 'trip_date'
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM trips WHERE trip_date = CURDATE()");
$stmt->execute();
$stats['trips_today'] = $stmt->fetch()['total'];

// Recent bookings
$stmt = $pdo->query("
    SELECT b.*, u.name as passenger_name, t.trip_date, t.departure_time
    FROM bookings b
    JOIN passengers u ON b.user_id = u.id
    JOIN trips t ON b.trip_id = t.id
    ORDER BY b.booking_date DESC LIMIT 5
");
$recentBookings = $stmt->fetchAll();

// Recent trips - FIXED: changed 'date' to 'trip_date'
$stmt = $pdo->query("
    SELECT t.*, 
           b.plate_number,
           b.total_capacity
    FROM trips t
    JOIN buses b ON t.bus_id = b.id
    ORDER BY t.trip_date DESC, t.departure_time DESC LIMIT 5
");
$recentTrips = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EthioGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            color: #1a1a2e;
            overflow-x: hidden;
        }

        /* Admin Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: #0f172a;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 100;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header h2 {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.3rem;
        }

        .sidebar-header i {
            color: #e67e22;
            font-size: 1.5rem;
        }

        .admin-info {
            padding: 1rem 1.5rem;
            background: rgba(255,255,255,0.05);
            margin: 1rem;
            border-radius: 12px;
        }

        .admin-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .admin-email {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
            margin: 0.25rem 0;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: #e67e22;
            color: white;
            border-left: 3px solid white;
        }

        .sidebar-nav i {
            width: 22px;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 1.5rem;
        }

        /* Top Bar */
        .top-bar {
            background: white;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .page-title p {
            color: #64748b;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #e67e22, #f39c12);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .stat-info h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1a2e;
        }

        .stat-info p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.25rem;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-header h3 {
            font-size: 1.1rem;
        }

        .view-all-btn {
            color: #e67e22;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
            font-weight: 600;
            color: #1a1a2e;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-scheduled {
            background: #dbeafe;
            color: #2563eb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-bus"></i> EthioGo Admin</h2>
            </div>
            <div class="admin-info">
                <div class="admin-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="admin-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_drivers.php"><i class="fas fa-users"></i> Manage Drivers</a>
                <a href="manage_trips.php"><i class="fas fa-calendar-alt"></i> Manage Trips</a>
                <a href="manage_passengers.php"><i class="fas fa-user-friends"></i> Passengers</a>
                <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <div class="page-title">
                    <h1>Dashboard</h1>
                    <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
                </div>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_passengers']); ?></h3>
                        <p>Total Passengers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_drivers']); ?></h3>
                        <p>Total Drivers</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-bus"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_trips']); ?></h3>
                        <p>Total Trips</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_bookings']); ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-info">
                        <h3>ETB <?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $stats['trips_today']; ?></h3>
                        <p>Trips Today</p>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings -->
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-clock"></i> Recent Bookings</h3>
                    <a href="manage_bookings.php" class="view-all-btn">View All →</a>
                </div>
                <table>
                    <thead>
                        <tr><th>Passenger</th><th>Date</th><th>Time</th><th>Passengers</th><th>Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentBookings as $booking): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['passenger_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['trip_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($booking['departure_time'])); ?></td>
                            <td><?php echo $booking['number_of_passengers']; ?></td>
                            <td>ETB <?php echo number_format($booking['total_price'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Trips -->
            <div class="table-container">
                <div class="table-header">
                    <h3><i class="fas fa-bus"></i> Recent Trips</h3>
                    <a href="manage_trips.php" class="view-all-btn">Manage Trips →</a>
                </div>
                <table>
                    <thead>
                        <tr><th>Bus</th><th>Date</th><th>Departure</th><th>Price</th><th>Available Seats</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentTrips as $trip): ?>
                        <tr>
                            <td><?php echo $trip['plate_number']; ?></td>
                            <td><?php echo date('M d, Y', strtotime($trip['trip_date'])); ?></td>
                            <td><?php echo date('h:i A', strtotime($trip['departure_time'])); ?></td>
                            <td>ETB <?php echo number_format($trip['price'], 2); ?></td>
                            <td><?php echo $trip['available_seats']; ?>/<?php echo $trip['total_capacity']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>