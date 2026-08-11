<?php
// db.php - Connection Configuration

require_once __DIR__ . '/config.php';

$pdo = new PDO("mysql:host=localhost;dbname=your_db_name;charset=utf8mb4", "username", "password");

?>