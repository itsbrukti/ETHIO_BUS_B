<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $driver_id = $_POST['driver_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE drivers SET status = ? WHERE id = ?");
    $stmt->execute([$status, $driver_id]);
    
    header('Location: manage_drivers.php');
    exit;
}

// Get all drivers
$stmt = $pdo->query("
    SELECT d.*, a.fullname as added_by_name 
    FROM drivers d
    LEFT JOIN admins a ON d.added_by = a.id
    ORDER BY d.created_at DESC
");
$drivers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Drivers - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        
        .admin-container { display: flex; min-height: 100vh; }
        
        /* Sidebar */
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
        
        /* Main Content */
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
        .edit-btn, .delete-btn { padding: 0.25rem 0.5rem; border-radius: 6px; text-decoration: none; font-size: 0.75rem; }
        .edit-btn { background: #dbeafe; color: #2563eb; }
        .delete-btn { background: #fee2e2; color: #dc2626; }
        .status-select { padding: 0.25rem; border-radius: 6px; border: 1px solid #e2e8f0; }
        .logout-btn { background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; }
        
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
                <a href="manage_drivers.php" class="active"><i class="fas fa-users"></i> Manage Drivers</a>
                <a href="manage_trips.php"><i class="fas fa-calendar-alt"></i> Manage Trips</a>
                <a href="manage_passengers.php"><i class="fas fa-user-friends"></i> Passengers</a>
                <a href="profile.php"><i class="fas fa-user-cog"></i> Profile Settings</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <h2>Manage Drivers</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="add_driver.php" class="add-btn"><i class="fas fa-plus"></i> Add Driver</a>
                    <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>License</th><th>Experience</th><th>Status</th><th>Added By</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($drivers as $driver): ?>
                        <tr>
                            <td><?php echo $driver['id']; ?></td>
                            <td><?php echo htmlspecialchars($driver['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($driver['email']); ?></td>
                            <td><?php echo htmlspecialchars($driver['phone']); ?></td>
                            <td><?php echo htmlspecialchars($driver['license_number']); ?></td>
                            <td><?php echo $driver['experience_years']; ?> yrs</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="driver_id" value="<?php echo $driver['id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="active" <?php echo $driver['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $driver['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo $driver['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                    <input type="hidden" name="action" value="update_status">
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($driver['added_by_name'] ?? 'System'); ?></td>
                            <td class="action-buttons">
                                <a href="edit_driver.php?id=<?php echo $driver['id']; ?>" class="edit-btn"><i class="fas fa-edit"></i> Edit</a>
                                <a href="delete_driver.php?id=<?php echo $driver['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>