<?php
$host = 'localhost';
$db = 'income_props';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$conn = mysqli_connect($host, $user, $pass, $db);

// Set charset
mysqli_set_charset($conn, $charset);

// // Check connection
// if (!$conn) {
//     die("Connection failed: " . mysqli_connect_error());
// }
// echo "Connected successfully";
?>
