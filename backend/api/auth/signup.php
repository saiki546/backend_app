<?php
// Signup API endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendErrorResponse('Invalid JSON input');
}

// Validate required fields
$required_fields = ['name', 'email', 'password'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        sendErrorResponse("Field '$field' is required");
    }
}

$name = trim($input['name']);
$email = trim($input['email']);
$password = $input['password'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendErrorResponse('Invalid email format');
}

// Validate password strength
if (strlen($password) < 6) {
    sendErrorResponse('Password must be at least 6 characters long');
}

// Check if user already exists
$users = getUsers();
foreach ($users as $user) {
    if ($user['email'] === $email) {
        sendErrorResponse('User with this email already exists');
    }
}

// Create new user
$new_user = [
    'id' => generateId(),
    'name' => $name,
    'email' => $email,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'email_verified' => false,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
];

// Add user to database
$users[] = $new_user;
saveUsers($users);

// Generate auth token
$token = generateToken();

// Prepare response data (don't include password)
$user_data = [
    'id' => $new_user['id'],
    'name' => $new_user['name'],
    'email' => $new_user['email'],
    'email_verified' => $new_user['email_verified'],
    'created_at' => $new_user['created_at']
];

$response_data = [
    'user' => $user_data,
    'token' => $token
];

sendSuccessResponse($response_data, 'User registered successfully');
?>
