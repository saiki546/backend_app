<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

header('Content-Type: application/json');
require 'db.php';

// Get user input
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];

$otp = rand(100000, 999999);
$created_at = date("Y-m-d H:i:s");

// Update the OTP in DB
$sql = "UPDATE users SET otp = '$otp', otp_created_at = '$created_at' WHERE email = '$email'";
if ($conn->query($sql) === TRUE) {

    // Send OTP Email
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;
        $mail->Username = 'saikiran2212215709@gmail.com'; // Your Gmail address
        $mail->Password = 'myzj skyw gbvu ehms';    // 16-character app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Auratext App');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Password Reset';
        $mail->Body    = "Your OTP is: <b>$otp</b>. It is valid for 5 minutes.";

        $mail->send();
        echo json_encode(["status" => true, "message" => "OTP sent to your email"]);
    } catch (Exception $e) {
        echo json_encode(["status" => false, "message" => "Email sending failed: {$mail->ErrorInfo}"]);
    }

} else {
    echo json_encode(["status" => false, "message" => "Email not found or error updating OTP"]);
}

$conn->close();
