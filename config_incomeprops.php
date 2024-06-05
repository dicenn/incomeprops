<?php
// Use getenv() to check for a Heroku environment variable
$dbUrl = getenv("DATABASE_URL");

if ($dbUrl) {
    // Parse the Database URL from Heroku
    $dbParams = parse_url($dbUrl);
    $host = $dbParams['host'];
    $port = $dbParams['port'];
    $user = $dbParams['user'];
    $pass = $dbParams['pass'];
    $db   = ltrim($dbParams['path'], '/'); // Trim leading slash
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
} else {
    // Fallback to local MySQL configuration
    $host = 'localhost';
    $db = 'income_props';
    $user = 'root';
    $pass = 'kfockNAVAu6.eMbbiFa3';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
}

// Create a new PDO instance for either MySQL or PostgreSQL
try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set charset for MySQL
    if (!$dbUrl) {
        $conn->exec("set names utf8mb4");
    }
    // echo "Connected successfully";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>