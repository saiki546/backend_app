<?php
$query = "SELECT v.*, s.sub_id FROM videos v
JOIN subtitles s ON v.vid_id = s.vid_id WHERE s.cno = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_GET['cno']);
$stmt->execute();
$result = $stmt->get_result();

$videos = [];
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}
echo json_encode($videos);
