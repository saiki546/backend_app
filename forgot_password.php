<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = isset($data['email']) ? $data['email'] : '';

if (empty($email)) {
    echo json_encode(["status" => false, "message" => "Email is required"]);
    exit;
}

// Generate OTP
$otp = rand(100000, 999999);
$created_at = date("Y-m-d H:i:s");

// Use prepared statement
$stmt = $conn->prepare("UPDATE users SET otp = ?, otp_created_at = ? WHERE email = ?");
$stmt->bind_param("sss", $otp, $created_at, $email);

if ($stmt->execute() && $stmt->affected_rows > 0) {

    // Send OTP using PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saikiran2212215709@gmail.com';       // ✅ Replace with your Gmail
        $mail->Password   = 'myzj skyw gbvu ehms';          // ✅ Replace with your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('saikiran2212215709@gmail.com', 'Auratext');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP is <b>$otp</b>. It is valid for 5 minutes.";

        $mail->send();

        echo json_encode([
            "status" => true,
            "message" => "OTP sent to your email.",
             
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "status" => false,
            "message" => "OTP generated but email failed: {$mail->ErrorInfo}"
        ]);
    }

} else {
    echo json_encode(["status" => false, "message" => "Email not found or error updating OTP"]);
}

$stmt->close();
$conn->close();
