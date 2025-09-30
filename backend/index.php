<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config/database.php';
require_once 'includes/response.php';

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/auratext2/', '', $path);

// Route requests to appropriate endpoints
switch ($path) {
    case 'auth/signup':
        include 'api/auth/signup.php';
        break;
    case 'auth/login':
        include 'api/auth/login.php';
        break;
    case 'auth/logout':
        include 'api/auth/logout.php';
        break;
    case 'auth/forgot-password':
        include 'api/auth/forgot-password.php';
        break;
    case 'auth/reset-password':
        include 'api/auth/reset-password.php';
        break;
    case 'auth/verify-email':
        include 'api/auth/verify-email.php';
        break;
    case 'user/profile':
        include 'api/user/profile.php';
        break;
    case 'videos/upload':
        include 'api/videos/upload.php';
        break;
    case 'videos':
        include 'api/videos/list.php';
        break;
    case 'captions/generate':
        include 'api/captions/generate.php';
        break;
    default:
        sendErrorResponse('Endpoint not found', 404);
}
?>

