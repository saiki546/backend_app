<?php
require_once 'db.php';

// Drop and recreate videos table to match expected structure
$sql = "DROP TABLE IF EXISTS videos";
$conn->query($sql);

$sql = "CREATE TABLE videos (
    vid_id VARCHAR(255) PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    duration VARCHAR(50) DEFAULT '00:00:00',
    path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Videos table created successfully!";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
