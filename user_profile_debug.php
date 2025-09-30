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

// Debug: Log all headers
error_log("=== PROFILE DEBUG ===");
error_log("Method: " . $method);
foreach (getallheaders() as $name => $value) {
    error_log("Header $name: $value");
}

// Get authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

error_log("Auth header: " . $authHeader);

if (empty($authHeader) || !str_starts_with($authHeader, 'Bearer ')) {
    error_log("No valid authorization header found");
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Authorization token required',
        'debug' => [
            'auth_header' => $authHeader,
            'all_headers' => getallheaders()
        ]
    ]);
    exit;
}

$token = substr($authHeader, 7); // Remove 'Bearer ' prefix
error_log("Token: " . substr($token, 0, 20) . "...");

if ($method === 'GET') {
    // Get user profile
    $stmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("User found: " . $user['name']);
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
        error_log("No user found with token");
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid token',
            'debug' => [
                'token_used' => substr($token, 0, 20) . '...',
                'all_tokens' => []
            ]
        ]);
    }
    
} elseif ($method === 'PUT') {
    // Update user profile
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("PUT input: " . json_encode($input));
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
        exit;
    }
    
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    
    if (empty($name) || empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Name and email are required']);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email already exists for another user
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND token != ?");
    $checkStmt->bind_param("ss", $email, $token);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Update user profile
    $updateStmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE token = ?");
    $updateStmt->bind_param("sss", $name, $email, $token);
    
    if ($updateStmt->execute()) {
        error_log("Profile updated successfully");
        // Get updated user data
        $getStmt = $conn->prepare("SELECT id, name, email, created_at FROM users WHERE token = ?");
        $getStmt->bind_param("s", $token);
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
        error_log("Failed to update profile: " . $updateStmt->error);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
    }
    
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

$conn->close();
?>
