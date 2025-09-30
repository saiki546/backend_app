<?php

include 'db.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = isset($_POST["email"]) ? $_POST["email"] : "";
    $password = isset($_POST["password"]) ? $_POST["password"] : "";

    if (!empty($email) && !empty($password)) {

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // ✅ Use password_verify instead of plain comparison
            if (password_verify($password, $user['password'])) {
                $response = array(
                    "status" => "success",
                    "message" => "Login successful.",
                    "user" => array(
                        "name" => $user["name"],
                        "email" => $user["email"]
                    )
                );
            } else {
                $response = array("status" => "failed", "message" => "Invalid password.");
            }

        } else {
            $response = array("status" => "failed", "message" => "User not found.");
        }

        echo json_encode($response);
        $stmt->close();

    } else {
        echo json_encode(array("status" => "failed", "message" => "Email and password are required."));
    }
}

$conn->close();
