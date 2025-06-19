<?php
$host     = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname   = getenv('DB_NAME');
$port     = getenv('DB_PORT'); 

// Create MySQLi connection
$conn = new mysqli($host, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// ✅ Connection successful
// echo "✅ Connected successfully to the database!";
?>
