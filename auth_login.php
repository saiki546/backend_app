<?php
// Login API endpoint
require_once 'config/database.php';
require_once 'includes/response.php';

// Set content type to JSON
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendErrorResponse('Invalid JSON input');
}

// Validate required fields
if (empty($input['email']) || empty($input['password'])) {
    sendErrorResponse('Email and password are required');
}

$email = trim($input['email']);
$password = $input['password'];

// Get users from database
$users = getUsers();

// Check if database is empty
if (empty($users)) {
    sendErrorResponse('No users found in database. Please sign up first.');
}

// Find user by email
$user = null;
foreach ($users as $u) {
    if ($u['email'] === $email) {
        $user = $u;
        break;
    }
}

if (!$user) {
    sendErrorResponse('Invalid email or password');
}

// Verify password
if (!password_verify($password, $user['password'])) {
    sendErrorResponse('Invalid email or password');
}

// Generate new auth token (not stored in database)
$token = generateToken();

// Note: Token is generated but not stored in database to avoid schema issues
// The token will be validated on the client side for session management

// Prepare response data (don't include password)
$user_data = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'email_verified' => $user['email_verified'] ?? false,
    'created_at' => $user['created_at']
];

$response_data = [
    'user' => $user_data,
    'token' => $token
];

sendSuccessResponse($response_data, 'Login successful');
?>
