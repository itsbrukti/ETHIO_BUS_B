<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

$admin_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Get admin data
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $fullname = trim($_POST['fullname']);
        $phone = trim($_POST['phone']);
        
        $stmt = $pdo->prepare("UPDATE admins SET fullname = ?, phone = ? WHERE id = ?");
        $stmt->execute([$fullname, $phone, $admin_id]);
        $message = 'Profile updated successfully!';
        
        // Update session
        $_SESSION['user_name'] = $fullname;
        
        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - Admin</title>
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
        
        .profile-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .profile-header { text-align: center; margin-bottom: 2rem; }
        .profile-header i { font-size: 4rem; color: #e67e22; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 10px; }
        input[readonly] { background: #f8fafc; cursor: not-allowed; }
        button { background: #e67e22; color: white; border: none; padding: 0.75rem; border-radius: 10px; cursor: pointer; width: 100%; font-size: 1rem; }
        .message { padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem; }
        .success { background: #d1fae5; color: #065f46; }
        .divider { margin: 2rem 0; border-top: 1px solid #e2e8f0; }
        
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
                <a href="manage_passengers.php"><i class="fas fa-user-friends"></i> Passengers</a>
                <a href="profile.php" class="active"><i class="fas fa-user-cog"></i> Profile Settings</a>
            </nav>
        </div>

        <div class="main-content">
            <div class="top-bar">
                <h2>Profile Settings</h2>
                <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>

            <div class="profile-card">
                <div class="profile-header">
                    <i class="fas fa-user-circle"></i>
                    <h3>Admin Profile</h3>
                </div>
                
                <?php if ($message): ?>
                    <div class="message success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($admin['fullname']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($admin['email']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" value="<?php echo ucfirst($admin['role']); ?>" readonly>
                    </div>
                    <button type="submit" name="update_profile">Update Profile</button>
                </form>
                
                <div class="divider"></div>
                
                <a href="change_password.php" style="display: block; text-align: center; color: #e67e22; text-decoration: none;">Change Password →</a>
            </div>
        </div>
    </div>
</body>
</html>