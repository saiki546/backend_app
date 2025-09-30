<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

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
                
                // Send OTP Email
                $mail = new PHPMailer(true);
                
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'saikiran2212215709@gmail.com'; // Your Gmail address
                    $mail->Password = 'hfkl mddz evyl nxdd';    // 16-character app password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    
                    // Recipients
                    $mail->setFrom('saikiran2212215709@gmail.com', 'Auratext App');
                    $mail->addAddress($email, $user['name']);
                    
                    // Email content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset OTP - Auratext';
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #333;'>Password Reset Request</h2>
                            <p>Hello " . htmlspecialchars($user['name']) . ",</p>
                            <p>You have requested to reset your password. Please use the following OTP to proceed:</p>
                            <div style='background-color: #f4f4f4; padding: 20px; text-align: center; margin: 20px 0;'>
                                <h1 style='color: #007bff; font-size: 32px; margin: 0; letter-spacing: 5px;'>" . $otp . "</h1>
                            </div>
                            <p><strong>This OTP is valid for 5 minutes.</strong></p>
                            <p>If you did not request this password reset, please ignore this email.</p>
                            <hr style='margin: 30px 0;'>
                            <p style='color: #666; font-size: 12px;'>This is an automated message from Auratext App.</p>
                        </div>
                    ";
                    
                    $mail->send();
                    
                    $response = array(
                        "success" => true, 
                        "message" => "OTP sent to your email successfully"
                    );
                    
                } catch (Exception $e) {
                    // If email fails, log OTP for testing
                    $log_message = "[" . date('Y-m-d H:i:s') . "] OTP for " . $email . ": " . $otp . " (Email failed: " . $mail->ErrorInfo . ")\n";
                    file_put_contents('otp_log.txt', $log_message, FILE_APPEND | LOCK_EX);
                    
                    $response = array(
                        "success" => true, 
                        "message" => "OTP sent to your email successfully. If not received, check otp_log.txt for testing.",
                        "debug_otp" => $otp, // Only for testing - remove in production
                        "email_error" => $mail->ErrorInfo // For debugging
                    );
                }
                
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