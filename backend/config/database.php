<?php
// Simple file-based database for testing
// In production, use MySQL/PostgreSQL

$db_file = 'data/users.json';
$db_dir = dirname($db_file);

if (!file_exists($db_dir)) {
    mkdir($db_dir, 0777, true);
}

if (!file_exists($db_file)) {
    file_put_contents($db_file, json_encode([]));
}

function getUsers() {
    global $db_file;
    $data = file_get_contents($db_file);
    return json_decode($data, true) ?: [];
}

function saveUsers($users) {
    global $db_file;
    file_put_contents($db_file, json_encode($users, JSON_PRETTY_PRINT));
}

function generateId() {
    return uniqid('user_', true);
}

function generateToken() {
    return bin2hex(random_bytes(32));
}
?>

