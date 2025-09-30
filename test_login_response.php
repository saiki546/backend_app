<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Test login with your existing user
$testData = [
    'email' => 'saikiran2212215709@gmail.com',
    'password' => 'password123'
];

echo "<h2>Testing Login Response</h2>";
echo "<p>Testing login for: " . $testData['email'] . "</p>";

// Simulate the login request
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
$error = curl_error($ch);
curl_close($ch);

echo "<h3>Login Response:</h3>";
echo "<p>HTTP Code: $httpCode</p>";
if ($error) {
    echo "<p>cURL Error: $error</p>";
}
echo "<p>Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

// Parse the response
$responseData = json_decode($response, true);
if ($responseData && isset($responseData['data']['token'])) {
    $token = $responseData['data']['token'];
    echo "<p>✅ Token received: " . substr($token, 0, 20) . "...</p>";
    
    // Test profile endpoint with this token
    echo "<h3>Testing Profile Endpoint with Login Token:</h3>";
    
    $profileUrl = 'http://localhost/auratext2/user_profile.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $profileUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $profileResponse = curl_exec($ch);
    $profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $profileError = curl_error($ch);
    curl_close($ch);
    
    echo "<p>Profile HTTP Code: $profileHttpCode</p>";
    if ($profileError) {
        echo "<p>Profile cURL Error: $profileError</p>";
    }
    echo "<p>Profile Response: <pre>" . htmlspecialchars($profileResponse) . "</pre></p>";
    
    if ($profileHttpCode === 200) {
        echo "<p>✅ Profile endpoint works!</p>";
    } else {
        echo "<p>❌ Profile endpoint failed</p>";
    }
    
} else {
    echo "<p>❌ No token in login response</p>";
    echo "<p>Response structure: <pre>" . htmlspecialchars(json_encode($responseData, JSON_PRETTY_PRINT)) . "</pre></p>";
}

$conn->close();
?>
