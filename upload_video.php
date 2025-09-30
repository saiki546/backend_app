<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["error" => "Invalid JSON data"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO videos (vid_id, title, duration, path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $data['vid_id'], $data['vid_title'], $data['duration'], $data['path']);
    $stmt->execute();
    
    $response = [
        "video_id" => $data['vid_id'],
        "title" => $data['vid_title'],
        "status" => "uploaded"
    ];
    
    echo json_encode($response);
} catch (Exception $e) {
    // If video already exists, update it
    $stmt = $conn->prepare("UPDATE videos SET title = ?, duration = ?, path = ? WHERE vid_id = ?");
    $stmt->bind_param("ssss", $data['vid_title'], $data['duration'], $data['path'], $data['vid_id']);
    $stmt->execute();
    
    $response = [
        "video_id" => $data['vid_id'],
        "title" => $data['vid_title'],
        "status" => "updated"
    ];
    
    echo json_encode($response);
}
?>
