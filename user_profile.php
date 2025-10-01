<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get the request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get user profile - accept user ID from query parameter
    $user_id = $_GET['user_id'] ?? '';
    
    if (empty($user_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }
    
    // Get user by ID
    $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'created_at' => $user['created_at']
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
    
} elseif ($method === 'PUT') {
    // Update user profile - accept user ID from request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $user_id = $input['user_id'] ?? '';
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    
    if (empty($user_id) || empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID, name and email are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists for another user
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $checkStmt->bind_param("ss", $email, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Update user profile
    $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $updateStmt->bind_param("sss", $name, $email, $user_id);
    
    if ($updateStmt->execute()) {
        // Get updated user data
        $getStmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE id = ?");
        $getStmt->bind_param("s", $user_id);
        $getStmt->execute();
        $getResult = $getStmt->get_result();
        
        if ($getResult->num_rows > 0) {
            $user = $getResult->fetch_assoc();
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'created_at' => $user['created_at']
                ]
            ]);
        } else {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        }
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>