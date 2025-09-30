<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Simple routing for testing
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// Route requests
switch ($path) {
    case 'auratext2/auth/signup':
        // Handle signup
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        
        // Validate required fields
        $required_fields = ['name', 'email', 'password'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Field '$field' is required"]);
                exit;
            }
        }
        
        $name = trim($input['name']);
        $email = trim($input['email']);
        $password = $input['password'];
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        // Validate password strength
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long']);
            exit;
        }
        
        // Simple success response for testing
        $response = [
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => [
                    'id' => 'user_' . time(),
                    'name' => $name,
                    'email' => $email,
                    'email_verified' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'token' => bin2hex(random_bytes(32))
            ]
        ];
        
        echo json_encode($response);
        break;
        
    case 'auratext2/auth/login':
        // Handle login
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }
        
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
            exit;
        }
        
        // Simple success response for testing
        $response = [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => [
                    'id' => 'user_123',
                    'name' => 'Test User',
                    'email' => $input['email'],
                    'email_verified' => true,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                'token' => bin2hex(random_bytes(32))
            ]
        ];
        
        echo json_encode($response);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Endpoint not found', 'path' => $path]);
}
?>
