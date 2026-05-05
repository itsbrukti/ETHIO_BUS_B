<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

session_start();

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$rememberMe = $data['rememberMe'] ?? false;

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

try {
    $user = null;
    $table = '';
    
    // Check in passengers table
    $stmt = $pdo->prepare("SELECT id, fullname, email, phone, password, 'passenger' as user_type FROM passengers WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) $table = 'passengers';
    
    // Check in admins table if not found
    if (!$user) {
        $stmt = $pdo->prepare("SELECT id, fullname, email, phone, password, 'admin' as user_type FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) $table = 'admins';
    }
    
    // Check in drivers table if not found
    if (!$user) {
        $stmt = $pdo->prepare("SELECT id, fullname, email, phone, password, 'driver' as user_type, status FROM drivers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) $table = 'drivers';
    }
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email not found. Please register first.']);
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Incorrect password. Please try again.']);
        exit;
    }
    
    // Check if driver is active
    if ($user['user_type'] === 'driver' && isset($user['status']) && $user['status'] !== 'active') {
        echo json_encode(['success' => false, 'message' => 'Your driver account is inactive. Contact admin.']);
        exit;
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['fullname'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    
    // Remember me
    if ($rememberMe) {
        setcookie('user_email', $email, time() + (86400 * 30), "/");
    }
    
    // Determine redirect URL - FIXED PATHS
    $redirect = '';
  switch($user['user_type']) {
    case 'admin':
        $redirect = '/ETHIOGO/backend/admin/dashboard.php';
        break;

    case 'driver':
        $redirect = '/ETHIOGO/backend/driver/dashboard.php';
        break;

    case 'passenger':
        $redirect = '/ETHIOGO/backend/passenger/dashboard.php';
        break;

    default:
        $redirect = '/ETHIOGO/index.html';
}
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful!',
        'redirect' => $redirect,
        'user_type' => $user['user_type']
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>