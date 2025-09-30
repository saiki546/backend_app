<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vid_id = $_POST['vid_id'];

    // Generate unique subtitle ID and path
    $sub_id = uniqid('sub_');
    $sub_path = 'subtitles/' . $sub_id . '.vtt'; // You can change extension if needed

    // Insert into subtitles table
    $stmt = $conn->prepare("INSERT INTO subtitles (vid_id, sub_id, sub_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $vid_id, $sub_id, $sub_path);
    $stmt->execute();

    // Update the videos table with subtitle info
    $update = $conn->prepare("UPDATE videos SET sub_id = ?, sub_path = ? WHERE vid_id = ?");
    $update->bind_param("sss", $sub_id, $sub_path, $vid_id);
    $update->execute();

    echo json_encode(["status" => true, "message" => "Subtitle data saved", "sub_id" => $sub_id]);
} else {
    echo json_encode(["status" => false, "message" => "Invalid request"]);
}
?>
