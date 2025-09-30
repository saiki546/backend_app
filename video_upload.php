<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'config/database.php';
require_once 'includes/response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse('Method not allowed', 405);
}

// Get authorization header
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? '';
$token = str_replace('Bearer ', '', $authHeader);

if (empty($token)) {
    sendErrorResponse('Authorization token required', 401);
}

// Verify token (simplified - you should implement proper JWT verification)
// For now, we'll just check if token exists
if ($token === 'test-token' || !empty($token)) {
    // Token is valid (simplified check)
} else {
    sendErrorResponse('Invalid token', 401);
}

// Get form data
$video_id = $_POST['video_id'] ?? '';
$title = $_POST['title'] ?? '';
$duration = $_POST['duration'] ?? '';

if (empty($video_id) || empty($title)) {
    sendErrorResponse('Missing required fields: video_id and title');
}

// Check if video file was uploaded
if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    sendErrorResponse('No video file uploaded or upload error');
}

$videoFile = $_FILES['video'];

// Validate file type
$allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv'];
if (!in_array($videoFile['type'], $allowedTypes)) {
    sendErrorResponse('Invalid file type. Only video files are allowed.');
}

// Create uploads directory if it doesn't exist
$uploadDir = 'uploads/videos/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$fileExtension = pathinfo($videoFile['name'], PATHINFO_EXTENSION);
$fileName = $video_id . '.' . $fileExtension;
$filePath = $uploadDir . $fileName;

// Move uploaded file
if (!move_uploaded_file($videoFile['tmp_name'], $filePath)) {
    sendErrorResponse('Failed to save video file');
}

// Save to database
try {
    $stmt = $pdo->prepare("INSERT INTO videos (id, user_id, title, duration, file_path, status, created_at) VALUES (?, ?, ?, ?, ?, 'uploaded', NOW())");
    $stmt->execute([$video_id, '1', $title, $duration, $filePath]);
    
    $response = [
        'video_id' => $video_id,
        'title' => $title,
        'status' => 'uploaded'
    ];
    
    sendSuccessResponse($response, 'Video uploaded successfully');
    
} catch (PDOException $e) {
    // If video already exists, update it
    if ($e->getCode() == 23000) { // Duplicate key error
        $stmt = $pdo->prepare("UPDATE videos SET title = ?, duration = ?, file_path = ?, status = 'uploaded', updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $duration, $filePath, $video_id]);
        
        $response = [
            'video_id' => $video_id,
            'title' => $title,
            'status' => 'uploaded'
        ];
        
        sendSuccessResponse($response, 'Video updated successfully');
    } else {
        sendErrorResponse('Database error: ' . $e->getMessage());
    }
}
?>
