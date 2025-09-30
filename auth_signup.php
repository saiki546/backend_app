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
    
    $name = isset($input["name"]) ? trim($input["name"]) : "";
    $email = isset($input["email"]) ? trim($input["email"]) : "";
    $password = isset($input["password"]) ? $input["password"] : "";

    if (!empty($name) && !empty($email) && !empty($password)) {
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response = array("success" => false, "message" => "Invalid email format");
            echo json_encode($response);
            exit;
        }
        
        // Validate password strength
        if (strlen($password) < 6) {
            $response = array("success" => false, "message" => "Password must be at least 6 characters long");
            echo json_encode($response);
            exit;
        }

        // Check if email already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows > 0) {
            $response = array("success" => false, "message" => "Email already registered");
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
            // Get the inserted user ID
            $user_id = $conn->insert_id;
            
            // Generate a simple token (in production, use JWT or similar)
            $token = bin2hex(random_bytes(32));
            
            // Store token in database
            $updateStmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
            $updateStmt->bind_param("si", $token, $user_id);
            $updateStmt->execute();
            $updateStmt->close();
            
            // Prepare response data
            $user_data = array(
                "id" => $user_id,
                "name" => $name,
                "email" => $email,
                "email_verified" => false,
                "created_at" => date('Y-m-d H:i:s')
            );
            
            $response = array(
                "success" => true, 
                "message" => "User registered successfully",
                "data" => array(
                    "user" => $user_data,
                    "token" => $token
                )
            );
        } else {
            $response = array("success" => false, "message" => "Error: " . $stmt->error);
        }

        echo json_encode($response);
        $stmt->close();

    } else {
        $response = array("success" => false, "message" => "Name, email and password are required");
        echo json_encode($response);
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method");
    echo json_encode($response);
}

$conn->close();
?>
