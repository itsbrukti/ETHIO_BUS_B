<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $license_number = trim($_POST['license_number'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    
    if ($fullname && $email && $phone && $password && $license_number) {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM drivers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already exists!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO drivers (fullname, email, phone, password, license_number, experience_years, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$fullname, $email, $phone, $hashedPassword, $license_number, $experience_years, $_SESSION['user_id']])) {
                $message = 'Driver added successfully!';
            } else {
                $error = 'Failed to add driver.';
            }
        }
    } else {
        $error = 'Please fill all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Driver - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f0f2f5; padding: 2rem; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 24px; padding: 2rem; }
        h2 { margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        input, select { width: 100%; padding: 0.75rem; border: 1px solid #e2e8f0; border-radius: 8px; }
        button { background: #e67e22; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; width: 100%; }
        .message { padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; }
        .success { background: #d1fae5; color: #065f46; }
        .error { background: #fee2e2; color: #dc2626; }
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #e67e22; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-user-plus"></i> Add New Driver</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>License Number</label>
                <input type="text" name="license_number" required>
            </div>
            <div class="form-group">
                <label>Experience (Years)</label>
                <input type="number" name="experience_years" min="0" max="50" value="0">
            </div>
            <button type="submit">Add Driver</button>
        </form>
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
</body>
</html>