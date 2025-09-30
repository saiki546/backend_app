<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'];
$password = $data['password'];
$confirm = $data['confirm_password'];

if ($password !== $confirm) {
    echo json_encode(["status" => false, "message" => "Passwords do not match"]);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = '$hashed', otp = NULL WHERE email = '$email'";
if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => true, "message" => "Password reset successful"]);
} else {
    echo json_encode(["status" => false, "message" => "Error resetting password"]);
}
$conn->close();
?>
