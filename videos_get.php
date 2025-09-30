<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT id, vid_id, vid_title, duration, path, prof_id, sub_id, sub_path FROM videos ORDER BY id DESC";
$result = $conn->query($sql);

$videos = [];

while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}

echo json_encode([
    "status" => true,
    "videos" => $videos
]);

$conn->close();
