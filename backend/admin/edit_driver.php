<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../../login.html');
    exit;
}

require_once '../config/database.php';

$driver_id = $_GET['id'] ?? 0;

if (!$driver_id) {
    header('Location: manage_drivers.php');
    exit;
}

// Get driver data
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch();

if (!$driver) {
    header('Location: manage_drivers.php');
    exit;
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $license_number = trim($_POST['license_number']);
    $experience_years = intval($_POST['experience_years']);
    $status = $_POST['status'];
    $new_password = $_POST['new_password'];
    
    $errors = [];
    if (empty($fullname)) $errors[] = 'Full name required';
    if (empty($email)) $errors[] = 'Email required';
    if (empty($phone)) $errors[] = 'Phone required';
    if (empty($license_number)) $errors[] = 'License number required';
    
    if (empty($errors)) {
        // Check if email exists for other driver
        $stmt = $pdo->prepare("SELECT id FROM drivers WHERE email = ? AND id != ?");
        $stmt->execute([$email, $driver_id]);
        if ($stmt->fetch()) {
            $error = 'Email already exists for another driver!';
        } else {
            if (!empty($new_password)) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE drivers SET fullname=?, email=?, phone=?, password=?, license_number=?, experience_years=?, status=? WHERE id=?");
                $stmt->execute([$fullname, $email, $phone, $hashedPassword, $license_number, $experience_years, $status, $driver_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE drivers SET fullname=?, email=?, phone=?, license_number=?, experience_years=?, status=? WHERE id=?");
                $stmt->execute([$fullname, $email, $phone, $license_number, $experience_years, $status, $driver_id]);
            }
            $message = 'Driver updated successfully!';
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id = ?");
            $stmt->execute([$driver_id]);
            $driver = $stmt->fetch();
        }
    } else {
        $error = implode(', ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Driver - Admin</title>
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
        <h2><i class="fas fa-edit"></i> Edit Driver</h2>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" value="<?php echo htmlspecialchars($driver['fullname']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($driver['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($driver['phone']); ?>" required>
            </div>
            <div class="form-group">
                <label>New Password (leave blank to keep current)</label>
                <input type="password" name="new_password" placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label>License Number</label>
                <input type="text" name="license_number" value="<?php echo htmlspecialchars($driver['license_number']); ?>" required>
            </div>
            <div class="form-group">
                <label>Experience (Years)</label>
                <input type="number" name="experience_years" value="<?php echo $driver['experience_years']; ?>" min="0">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?php echo $driver['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $driver['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    <option value="suspended" <?php echo $driver['status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                </select>
            </div>
            <button type="submit">Update Driver</button>
        </form>
        <a href="manage_drivers.php" class="back-link">← Back to Drivers List</a>
    </div>
</body>
</html>