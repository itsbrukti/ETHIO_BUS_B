<?php
// test_db.php - Test database connection and admin user

require_once 'backend/config/database.php';

echo "<h1>Database Test</h1>";

// Test connection
try {
    $stmt = $pdo->query("SELECT 1");
    echo "<p style='color:green'>✓ Database connection successful!</p>";
} catch(Exception $e) {
    echo "<p style='color:red'>✗ Database connection failed: " . $e->getMessage() . "</p>";
}

// Check admin user
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = 'admin@ethiogo.com'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<p style='color:green'>✓ Admin user found!</p>";
        echo "<p>Name: " . $admin['fullname'] . "</p>";
        echo "<p>Email: " . $admin['email'] . "</p>";
    } else {
        echo "<p style='color:orange'>⚠ Admin user NOT found! Need to insert admin user.</p>";
        
        // Insert admin user
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (fullname, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute(['System Administrator', 'admin@ethiogo.com', '0911000000', $hashedPassword, 'super_admin'])) {
            echo "<p style='color:green'>✓ Admin user has been created!</p>";
            echo "<p>Email: admin@ethiogo.com</p>";
            echo "<p>Password: admin123</p>";
        } else {
            echo "<p style='color:red'>✗ Failed to create admin user</p>";
        }
    }
} catch(Exception $e) {
    echo "<p style='color:red'>Error checking admin: " . $e->getMessage() . "</p>";
}

// Check all tables
echo "<h2>Tables in database:</h2>";
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "<ul>";
foreach($tables as $table) {
    echo "<li>" . $table . "</li>";
}
echo "</ul>";
?>