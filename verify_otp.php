<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$otp = $data['otp'] ?? '';

if (empty($email) || empty($otp)) {
    echo json_encode(["status" => false, "message" => "Email and OTP are required"]);
    exit;
}

// Use prepared statements to avoid SQL injection
$sql = "SELECT otp, otp_created_at FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($user['otp'] == $otp) {
        $otp_time = strtotime($user['otp_created_at']);
        $current_time = time();

        if (($current_time - $otp_time) <= 300) { // 5 minutes = 300 seconds
            echo json_encode([
                "status" => true,
                "message" => "OTP verified"
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "OTP expired. Please request a new one."
            ]);
        }
    } else {
        echo json_encode([
            "status" => false,
            "message" => "Incorrect OTP"
        ]);
    }
} else {
    echo json_encode([
        "status" => false,
        "message" => "Email not found"
    ]);
}

$stmt->close();
$conn->close();
