<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

// Get all passengers
$stmt = $pdo->query("SELECT * FROM passengers ORDER BY created_at DESC");
$passengers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Passengers - Admin</title>
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
        .logout-btn { background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        
        .table-container {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            overflow-x: auto;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; font-weight: 600; }
        
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
                <a href="manage_trips.php"><i class="fas fa-calendar-alt"></i> Manage Trips</a>
                <a href="manage_passengers.php" class="active"><i class="fas fa-user-friends"></i> Passengers</a>
                <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <h2>Manage Passengers</h2>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered Date</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($passengers as $passenger): ?>
                        <tr>
                            <td><?php echo $passenger['id']; ?></td>
                            <td><?php echo htmlspecialchars($passenger['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($passenger['email']); ?></td>
                            <td><?php echo htmlspecialchars($passenger['phone']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($passenger['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>