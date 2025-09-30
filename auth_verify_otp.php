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
    $otp = isset($input["otp"]) ? trim($input["otp"]) : "";

    if (!empty($email) && !empty($otp)) {
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array("success" => false, "message" => "Invalid email format");
            echo json_encode($response);
            exit;
        }
        
        // Check if OTP is valid and not expired
        $stmt = $conn->prepare("SELECT id, name, otp, otp_created_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if OTP matches
            if ($user['otp'] == $otp) {
                
                // Check if OTP is not expired (5 minutes = 300 seconds)
                $otp_created_at = strtotime($user['otp_created_at']);
                $current_time = time();
                $time_diff = $current_time - $otp_created_at;
                
                if ($time_diff <= 300) { // 5 minutes
                    
                    // Generate reset token
                    $reset_token = bin2hex(random_bytes(32));
                    
                    // Store reset token in database
                    $reset_token_created_at = date('Y-m-d H:i:s');
                    $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_created_at = ? WHERE email = ?");
                    $updateStmt->bind_param("sss", $reset_token, $reset_token_created_at, $email);
                    
                    if ($updateStmt->execute()) {
                        $response = array(
                            "success" => true, 
                            "message" => "OTP verified successfully",
                            "data" => array(
                                "reset_token" => $reset_token
                            )
                        );
                    } else {
                        $response = array("success" => false, "message" => "Failed to generate reset token");
                    }
                    
                    $updateStmt->close();
                    
                } else {
                    $response = array("success" => false, "message" => "OTP has expired. Please request a new one.");
                }
                
            } else {
                $response = array("success" => false, "message" => "Invalid OTP");
            }
            
        } else {
            $response = array("success" => false, "message" => "Email not found");
        }
        
        $stmt->close();
        echo json_encode($response);

    } else {
        $response = array("success" => false, "message" => "Email and OTP are required");
        echo json_encode($response);
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method");
    echo json_encode($response);
}

$conn->close();
?>
