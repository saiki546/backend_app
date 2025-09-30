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
    
    $email = isset($input["email"]) ? trim($input["email"]) : "";
    $password = isset($input["password"]) ? $input["password"] : "";

    if (!empty($email) && !empty($password)) {
        
        // Find user by email
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Generate a simple token (in production, use JWT or similar)
                $token = bin2hex(random_bytes(32));
                
                // Store token in database
                $updateStmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
                $updateStmt->bind_param("ss", $token, $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Prepare response data (don't include password)
                $user_data = array(
                    "id" => $user['id'],
                    "name" => $user['name'],
                    "email" => $user['email'],
                    "email_verified" => false,
                    "created_at" => date('Y-m-d H:i:s')
                );
                
                $response = array(
                    "success" => true, 
                    "message" => "Login successful",
                    "data" => array(
                        "user" => $user_data,
                        "token" => $token
                    )
                );
            } else {
                $response = array("success" => false, "message" => "Invalid email or password");
            }
        } else {
            $response = array("success" => false, "message" => "Invalid email or password");
        }
        
        $stmt->close();
        echo json_encode($response);

    } else {
        $response = array("success" => false, "message" => "Email and password are required");
        echo json_encode($response);
    }
} else {
    $response = array("success" => false, "message" => "Invalid request method");
    echo json_encode($response);
}

$conn->close();
?>
