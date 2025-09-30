<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$newPassword = $data['new_password'] ?? '';

if (empty($email) || empty($newPassword)) {
    echo json_encode(["status" => false, "message" => "Email and new password are required"]);
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password = ?, otp = NULL, otp_created_at = NULL WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashedPassword, $email);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["status" => true, "message" => "Password updated successfully"]);
} else {
    echo json_encode(["status" => false, "message" => "Failed to update password or email not found"]);
}

$stmt->close();
$conn->close();
