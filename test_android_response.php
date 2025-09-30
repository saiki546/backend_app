<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Test the exact response format that Android app expects
$testResponse = [
    "success" => true,
    "message" => "Login successful",
    "data" => [
        "user" => [
            "id" => "test_user_123",
            "name" => "Test User",
            "email" => "test@example.com",
            "email_verified" => false,
            "created_at" => date('Y-m-d H:i:s')
        ],
        "token" => "test_token_1234567890abcdef"
    ]
];

echo "<h2>Android App Expected Response Format</h2>";
echo "<pre>" . json_encode($testResponse, JSON_PRETTY_PRINT) . "</pre>";

// Test actual login response
echo "<h2>Actual Login Response</h2>";
$testData = [
    'email' => 'saikiran2212215709@gmail.com',
    'password' => 'password123'
];

$url = 'http://localhost/auratext2/auth_login.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// Parse and show structure
$responseData = json_decode($response, true);
if ($responseData) {
    echo "<h3>Parsed Response Structure:</h3>";
    echo "<pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre>";
    
    // Check if token is accessible as expected by Android
    if (isset($responseData['data']['token'])) {
        echo "<p>✅ Token found at: data.token = " . $responseData['data']['token'] . "</p>";
    } else {
        echo "<p>❌ Token NOT found at data.token</p>";
    }
    
    if (isset($responseData['data']['user'])) {
        echo "<p>✅ User found at: data.user</p>";
    } else {
        echo "<p>❌ User NOT found at data.user</p>";
    }
}

$conn->close();
?>
