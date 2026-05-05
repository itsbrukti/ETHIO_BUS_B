<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'driver') {
    header('Location: ../../login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Dashboard - EthioGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .header { background: white; padding: 1.5rem; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .logout-btn { background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }
        .welcome-card { background: linear-gradient(135deg, #1a1a2e, #16213e); color: white; padding: 2rem; border-radius: 16px; margin-bottom: 2rem; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Driver Dashboard</h1>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="welcome-card">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 🚍</h2>
            <p>Driver dashboard - Coming Soon</p>
        </div>
    </div>
</body>
</html>