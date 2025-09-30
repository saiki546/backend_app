<?php
include 'db.php';
header('Content-Type: application/json');

try {
    // Add token column to users table
    $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS token VARCHAR(64) NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            "success" => true, 
            "message" => "Token column added successfully to users table."
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Error adding token column: " . $conn->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        "success" => false, 
        "message" => "Database error: " . $e->getMessage()
    ]);
}

$conn->close();
?>
