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
    
    $reset_token = isset($input["reset_token"]) ? trim($input["reset_token"]) : "";
    $new_password = isset($input["new_password"]) ? $input["new_password"] : "";

    if (!empty($reset_token) && !empty($new_password)) {
        
        // Validate password strength
        if (strlen($new_password) < 6) {
            $response = array("success" => false, "message" => "Password must be at least 6 characters long");
            echo json_encode($response);
            exit;
        }
        
        // Check if reset token is valid and not expired
        $stmt = $conn->prepare("SELECT id, name, email, reset_token, reset_token_created_at FROM users WHERE reset_token = ?");
        $stmt->bind_param("s", $reset_token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if reset token is not expired (10 minutes = 600 seconds)
            $token_created_at = strtotime($user['reset_token_created_at']);
            $current_time = time();
            $time_diff = $current_time - $token_created_at;
            
            if ($time_diff <= 600) { // 10 minutes
                
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password and clear reset token
                $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_created_at = NULL, otp = NULL, otp_created_at = NULL WHERE reset_token = ?");
                $updateStmt->bind_param("ss", $hashed_password, $reset_token);
                
                if ($updateStmt->execute()) {
                    $response = array(
                        "success" => true, 
                        "message" => "Password reset successfully"
                    );
                } else {
                    $response = array("success" => false, "message" => "Failed to reset password");
                }
                
                $updateStmt->close();
                
            } else {
                $response = array("success" => false, "message" => "Reset token has expired. Please request a new password reset.");
            }
            
        } else {
            $response = array("success" => false, "message" => "Invalid reset token");
        }
        
        $stmt->close();
        echo json_encode($response);

    } else {
        $response = array("success" => false, "message" => "Reset token and new password are required");
        echo json_encode($response);
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method");
    echo json_encode($response);
}

$conn->close();
?>
