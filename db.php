<?php
// db.php - connect to MySQL database
$host = "localhost";
$username = "root";
$password = "";
$database = "auratext2";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
