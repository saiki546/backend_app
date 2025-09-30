<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

echo "<h2>Quick Fix Test</h2>";

// Test 1: Check if token column exists
echo "<h3>1. Database Check:</h3>";
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'token'");
if ($result && $result->num_rows > 0) {
    echo "✅ Token column exists<br>";
} else {
    echo "❌ Token column missing - run add_token_column.php first<br>";
}

// Test 2: Test login and get token
echo "<h3>2. Login Test:</h3>";
$testData = [
    'email' => 'saikiran2212215709@gmail.com',
    'password' => 'password123'
];

$url = 'http://localhost/auratext2/auth_login.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>Login HTTP Code: $httpCode</p>";
echo "<p>Login Response: <pre>" . htmlspecialchars($response) . "</pre></p>";

$responseData = json_decode($response, true);
if ($responseData && isset($responseData['data']['token'])) {
    $token = $responseData['data']['token'];
    echo "<p>✅ Token received: " . substr($token, 0, 20) . "...</p>";
    
    // Test 3: Test profile with this token
    echo "<h3>3. Profile Test with Token:</h3>";
    
    $profileUrl = 'http://localhost/auratext2/user_profile.php';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $profileUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $profileResponse = curl_exec($ch);
    $profileHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p>Profile HTTP Code: $profileHttpCode</p>";
    echo "<p>Profile Response: <pre>" . htmlspecialchars($profileResponse) . "</pre></p>";
    
    if ($profileHttpCode === 200) {
        echo "<p>✅ Profile endpoint works!</p>";
        echo "<p><strong>Your backend is working correctly!</strong></p>";
        echo "<p>The issue is in the Android app - the token is not being sent properly.</p>";
    } else {
        echo "<p>❌ Profile endpoint failed</p>";
    }
    
} else {
    echo "<p>❌ No token in login response</p>";
    echo "<p>Response structure: <pre>" . json_encode($responseData, JSON_PRETTY_PRINT) . "</pre></p>";
}

$conn->close();
?>
