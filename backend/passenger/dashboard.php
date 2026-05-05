<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'passenger') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

// Get passenger bookings
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE passenger_id = ? ORDER BY booking_date DESC LIMIT 5");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passenger Dashboard - EthioGo</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .header { background: white; padding: 1.5rem; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome h1 { font-size: 1.5rem; margin-bottom: 0.25rem; }
        .logout-btn { background: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none; }
        .welcome-card { background: linear-gradient(135deg, #1a1a2e, #16213e); color: white; padding: 2rem; border-radius: 16px; margin-bottom: 2rem; text-align: center; }
        .empty-state { text-align: center; padding: 3rem; background: white; border-radius: 16px; }
        .book-btn { background: #e67e22; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="welcome">
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h1>
                <p>Passenger Dashboard</p>
            </div>
            <a href="../logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        
        <div class="welcome-card">
            <h2>Your Journey Starts Here</h2>
            <p>Book bus tickets easily and travel stress-free</p>
        </div>
        
        <div class="empty-state">
            <i class="fas fa-calendar-alt" style="font-size: 3rem; color: #cbd5e1;"></i>
            <p style="margin-top: 1rem;">No bookings yet. Start your journey!</p>
            <a href="../../search.html" class="book-btn">Search Buses →</a>
        </div>
    </div>
</body>
</html>