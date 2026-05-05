<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$fullname = trim($data['fullname'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';
$confirmPassword = $data['confirmPassword'] ?? '';

$errors = [];

// Validation
if (empty($fullname)) {
    $errors[] = 'Full name is required';
} elseif (strlen($fullname) < 3) {
    $errors[] = 'Full name must be at least 3 characters';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!preg_match('/@gmail\.com$/i', $email)) {
    $errors[] = 'Email must be a Gmail address (@gmail.com)';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (!preg_match('/^09[0-9]{8}$/', $phone)) {
    $errors[] = 'Phone must start with 09 and be 10 digits';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
} elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
    $errors[] = 'Password must contain uppercase, lowercase, and number';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

// Check if email already exists in passengers table
if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT id FROM passengers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $errors[] = 'Email already registered. Please login.';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => $errors[0]]);
    exit;
}

// Hash password and insert passenger
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO passengers (fullname, email, phone, password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$fullname, $email, $phone, $hashedPassword]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Please login.'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()]);
}
?>