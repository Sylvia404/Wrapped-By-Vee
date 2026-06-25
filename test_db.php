<?php
echo "<h1>Database Connection Test</h1>";

// Test database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'wrappedbyvee';

echo "<h2>Testing connection...</h2>";

try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    echo "✅ Connected to MySQL successfully!<br>";
    
    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Database '$dbname' exists!<br>";
    } else {
        echo "❌ Database '$dbname' does NOT exist. Creating it...<br>";
        $pdo->exec("CREATE DATABASE $dbname");
        echo "✅ Database '$dbname' created successfully!<br>";
    }
    
    // Connect to the database
    $pdo->exec("USE $dbname");
    echo "✅ Connected to database '$dbname'<br>";
    
    // Check admin_users table
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'admin_users' exists!<br>";
        
        // Check if admin user exists
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM admin_users");
        $result = $stmt->fetch();
        if ($result['count'] > 0) {
            echo "✅ Admin user(s) exist: " . $result['count'] . "<br>";
        } else {
            echo "❌ No admin users found. Creating default admin...<br>";
            $hashed = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("INSERT INTO admin_users (username, password_hash) VALUES ('admin', '$hashed')");
            echo "✅ Default admin created: username='admin', password='admin123'<br>";
        }
    } else {
        echo "❌ Table 'admin_users' does NOT exist. Creating it...<br>";
        $pdo->exec("CREATE TABLE admin_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            email VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "✅ Table 'admin_users' created!<br>";
        
        // Create default admin
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admin_users (username, password_hash) VALUES ('admin', '$hashed')");
        echo "✅ Default admin created: username='admin', password='admin123'<br>";
    }
    
    echo "<h2 style='color:green;'>✅ All tests passed! You can now login.</h2>";
    
} catch (PDOException $e) {
    echo "<h2 style='color:red;'>❌ Connection failed!</h2>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "<br><b>Possible solutions:</b><br>";
    echo "1. Make sure MySQL is running in XAMPP<br>";
    echo "2. Check your database credentials in functions.php<br>";
    echo "3. Try changing DB_PASS to your MySQL password if you have one<br>";
    echo "4. In XAMPP, go to phpMyAdmin and create database 'wrappedbyvee' manually<br>";
}
?>