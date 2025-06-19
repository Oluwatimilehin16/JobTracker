<?php

$host = 'localhost';        
$dbname = 'job_tracker';
$username = 'root';         
$password = '';            

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set error mode to Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Disable emulated prepares for better security
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // Handle connection errors gracefully
    die("Database connection failed: " . $e->getMessage());
}
?>
