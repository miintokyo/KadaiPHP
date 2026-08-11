<?php
// db.php - Connection Configuration

require_once __DIR__ . '/config.php';

// Construct the Data Source Name (DSN) using constants from config.php
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

// Configure PDO options for safety and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,     // Fetches associative arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                 // Uses native prepared statements
];

try {
    // Create the global PDO instance
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Stop execution safely if connection fails
    die("Database connection failed: " . $e->getMessage());
}
?>