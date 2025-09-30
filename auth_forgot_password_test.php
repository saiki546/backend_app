<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $response = array("success" => false, "message" => "Invalid JSON input");
        echo json_encode($response);
        exit;
    }
    
    $email = isset($input["email"]) ? trim($input["email"]) : "";

    if (!empty($email)) {
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array("success" => false, "message" => "Invalid email format");
            echo json_encode($response);
            exit;
        }
        
        // Check if email exists in database
        $checkStmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate OTP
            $otp = rand(100000, 999999);
            $created_at = date("Y-m-d H:i:s");
            
            // Update OTP in database
            $updateStmt = $conn->prepare("UPDATE users SET otp = ?, otp_created_at = ? WHERE email = ?");
            $updateStmt->bind_param("sss", $otp, $created_at, $email);
            
            if ($updateStmt->execute()) {
                
                // For testing: Log the OTP to a file instead of sending email
                $log_message = "[" . date('Y-m-d H:i:s') . "] OTP for " . $email . ": " . $otp . "\n";
                file_put_contents('otp_log.txt', $log_message, FILE_APPEND | LOCK_EX);
                
                $response = array(
                    "success" => true, 
                    "message" => "OTP sent to your email successfully. For testing, check otp_log.txt file.",
                    "debug_otp" => $otp // Only for testing - remove in production
                );
                
            } else {
                $response = array("success" => false, "message" => "Failed to generate OTP");
            }
            
            $updateStmt->close();
            
        } else {
            // For security, don't reveal if email exists or not
            $response = array(
                "success" => true, 
                "message" => "If the email exists, an OTP has been sent"
            );
        }
        
        $checkStmt->close();
        echo json_encode($response);

    } else {
        $response = array("success" => false, "message" => "Email is required");
        echo json_encode($response);
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method");
    echo json_encode($response);
}

$conn->close();
?>
