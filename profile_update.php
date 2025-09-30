<?php
header('Content-Type: application/json');
require 'db.php';

// Check if sno is provided
if (!isset($_POST['sno'])) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required parameter: sno"
    ]);
    exit;
}

$sno = intval($_POST['sno']);
$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$location = $_POST['location'] ?? null;
$phone = $_POST['phone'] ?? null;

// Handle profile photo upload
$profile_photo = null;
if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES["profile_photo"]["name"]);
    $target_path = $upload_dir . uniqid() . "_" . $file_name;

    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_path)) {
        $profile_photo = $target_path;
    } else {
        echo json_encode([
            "status" => false,
            "message" => "File upload failed"
        ]);
        exit;
    }
}

// Build update query
$fields = [];
$params = [];
$types = "";

if ($name !== null) {
    $fields[] = "name = ?";
    $params[] = $name;
    $types .= "s";
}
if ($email !== null) {
    $fields[] = "email = ?";
    $params[] = $email;
    $types .= "s";
}
if ($location !== null) {
    $fields[] = "location = ?";
    $params[] = $location;
    $types .= "s";
}
if ($phone !== null) {
    $fields[] = "phone = ?";
    $params[] = $phone;
    $types .= "s";
}
if ($profile_photo !== null) {
    $fields[] = "profile_photo = ?";
    $params[] = $profile_photo;
    $types .= "s";
}

if (empty($fields)) {
    echo json_encode([
        "status" => false,
        "message" => "No fields to update"
    ]);
    exit;
}

$sql = "UPDATE profile SET " . implode(", ", $fields) . " WHERE sno = ?";
$params[] = $sno;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode([
        "status" => true,
        "message" => "Profile updated successfully"
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Update failed: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
