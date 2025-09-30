<?php
// Login API endpoint
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

// Find user by email
$users = getUsers();
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

// Generate new auth token
$token = generateToken();

// Prepare response data (don't include password)
$user_data = [
    'id' => $user['id'],
    'name' => $user['name'],
    'email' => $user['email'],
    'email_verified' => $user['email_verified'],
    'created_at' => $user['created_at']
];

$response_data = [
    'user' => $user_data,
    'token' => $token
];

sendSuccessResponse($response_data, 'Login successful');
?>
