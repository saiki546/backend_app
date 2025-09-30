<?php

include 'db.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = isset($_POST["name"]) ? $_POST["name"] : "";
    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (!empty($name) && !empty($email) && !empty($password)) {

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $response = array("status" => "failed", "message" => "Email already registered.");
            echo json_encode($response);
            $checkStmt->close();
            $conn->close();
            exit;
        }
        $checkStmt->close();

        // Hash the password before storing
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashedPassword);

        if ($stmt->execute()) {
            $response = array("status" => "success", "message" => "User registered successfully.");
        } else {
            $response = array("status" => "failed", "message" => "Error: " . $stmt->error);
        }

        echo json_encode($response);
        $stmt->close();

    } else {
        $response = array("status" => "failed", "message" => "Name, email and password are required.");
        echo json_encode($response);
    }
} else {
    $response = array("status" => "failed", "message" => "Invalid request method.");
    echo json_encode($response);
}

$conn->close();
?>