<?php
include 'db.php';
header('Content-Type: application/json');

try {
    // Add OTP and reset token columns to users table
    $sql = "ALTER TABLE users 
            ADD COLUMN IF NOT EXISTS otp VARCHAR(6) NULL,
            ADD COLUMN IF NOT EXISTS otp_created_at TIMESTAMP NULL,
            ADD COLUMN IF NOT EXISTS reset_token VARCHAR(64) NULL,
            ADD COLUMN IF NOT EXISTS reset_token_created_at TIMESTAMP NULL";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode([
            "success" => true, 
            "message" => "Database updated successfully. OTP and reset token columns added."
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Error updating database: " . $conn->error
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
