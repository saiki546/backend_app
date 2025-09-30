<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT * FROM profile";
$result = $conn->query($sql);

$profiles = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $profiles[] = $row;
    }
    echo json_encode([
        "status" => true,
        "data" => $profiles
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "No profiles found"
    ]);
}

$conn->close();
?>
