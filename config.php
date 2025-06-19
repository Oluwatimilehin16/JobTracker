<?php
$host     = getenv('DB_HOST');
$dbname   = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$port     = getenv('DB_PORT'); // Default to 3306 if not set

try {
    // Create the PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);

    // Set error mode to Exception for proper error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // (Optional but recommended) Disable emulated prepares
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Connection successful
    // echo "✅ Database connected successfully!";

} catch (PDOException $e) {
    // Connection failed
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
