<?php
// db.php - Production ready
error_reporting(E_ALL);
ini_set('display_errors', 0); // Turn off display errors in production
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

$host = 'localhost';
$dbname = 'wrapped_by_vee';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

function getDB() {
    global $pdo;
    return $pdo;
}
?>