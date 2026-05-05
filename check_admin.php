<?php
require_once 'backend/config/database.php';

echo "<h1>Admin Account Check</h1>";

// Check if admin exists
$stmt = $pdo->prepare("SELECT id, fullname, email, password FROM admins WHERE email = 'admin@ethiogo.com'");
$stmt->execute();
$admin = $stmt->fetch();

if ($admin) {
    echo "<p style='color:green'>✅ Admin user found!</p>";
    echo "<p>Name: " . $admin['fullname'] . "</p>";
    echo "<p>Email: " . $admin['email'] . "</p>";
    echo "<p>Stored Hash: " . substr($admin['password'], 0, 30) . "...</p>";
    
    // Test the password
    $testPassword = 'admin123';
    if (password_verify($testPassword, $admin['password'])) {
        echo "<p style='color:green'>✅ Password 'admin123' VERIFIED successfully!</p>";
    } else {
        echo "<p style='color:red'>❌ Password 'admin123' does NOT match!</p>";
        echo "<p>Fixing now...</p>";
        
        // Fix the password
        $newHash = password_hash('admin123', PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE admins SET password = ? WHERE email = 'admin@ethiogo.com'");
        $update->execute([$newHash]);
        
        echo "<p style='color:green'>✅ Password has been reset to 'admin123'</p>";
        echo "<p>New Hash: " . $newHash . "</p>";
    }
} else {
    echo "<p style='color:red'>❌ Admin user NOT found! Creating now...</p>";
    
    // Create admin user
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO admins (fullname, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
    $insert->execute(['System Administrator', 'admin@ethiogo.com', '0911000000', $newHash, 'super_admin']);
    
    echo "<p style='color:green'>✅ Admin user created!</p>";
    echo "<p>Email: admin@ethiogo.com</p>";
    echo "<p>Password: admin123</p>";
}

// Show all admins
echo "<h2>All Admins in Database:</h2>";
$stmt = $pdo->query("SELECT id, fullname, email, LEFT(password, 20) as hash_preview FROM admins");
$admins = $stmt->fetchAll();
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Hash Preview</th></tr>";
foreach($admins as $a) {
    echo "<tr>";
    echo "<td>" . $a['id'] . "</td>";
    echo "<td>" . $a['fullname'] . "</td>";
    echo "<td>" . $a['email'] . "</td>";
    echo "<td>" . $a['hash_preview'] . "...</td>";
    echo "</tr>";
}
echo "</table>";
?>