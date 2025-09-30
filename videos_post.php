<?php
header('Content-Type: application/json');
require 'db.php';

$data = json_decode(file_get_contents("php://input"), true);

$vid_id = $data['vid_id'];
$vid_title = $data['vid_title'];
$duration = $data['duration'];
$prof_id = $data['prof_id'];
$path = $data['path']; // video path (optional)
$sub_id = $data['sub_id'] ?? null;
$sub_path = $data['sub_path'] ?? null;

$sql = "INSERT INTO videos (vid_id, vid_title, duration, path, prof_id, sub_id, sub_path)
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssiss", $vid_id, $vid_title, $duration, $path, $prof_id, $sub_id, $sub_path);

if ($stmt->execute()) {
    echo json_encode(["status" => true, "message" => "Video added successfully"]);
} else {
    echo json_encode(["status" => false, "message" => "Failed to add video"]);
}

$stmt->close();
$conn->close();
