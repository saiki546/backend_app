<?php
$data = json_decode(file_get_contents("php://input"), true);
$stmt = $conn->prepare("UPDATE users SET profile_photo = ?, name = ?, email = ?, location = ?, phone = ? WHERE id = ?");
$stmt->bind_param("sssssi", $data['photo'], $data['name'], $data['email'], $data['location'], $data['phone'], $data['id']);
$stmt->execute();
echo json_encode(["status" => "updated"]);
