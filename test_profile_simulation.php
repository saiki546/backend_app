<?php
include 'db.php';
header('Content-Type: application/json');

echo "<h2>Profile Update Simulation Test</h2>";

// Get a user with a token
$result = $conn->query("SELECT id, name, email, token FROM users WHERE token IS NOT NULL LIMIT 1");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $token = $user['token'];
    
    echo "<h3>Testing with user: " . $user['name'] . " (ID: " . $user['id'] . ")</h3>";
    echo "<p>Token: " . substr($token, 0, 20) . "...</p>";
    
    // Simulate the profile update request
    $testData = [
        'name' => 'Test Updated Name',
        'email' => 'test_updated@example.com'
    ];
    
    echo "<h3>Simulating PUT request to user_profile.php:</h3>";
    echo "<p>Request data: " . json_encode($testData) . "</p>";
    
    // Test the actual endpoint
    $url = 'http://localhost/auratext2/user_profile.php';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h3>Response from user_profile.php:</h3>";
    echo "<p>HTTP Code: " . $httpCode . "</p>";
    if ($error) {
        echo "<p>cURL Error: " . $error . "</p>";
    }
    echo "<p>Response: " . htmlspecialchars($response) . "</p>";
    
    // Check if the update actually happened
    $checkResult = $conn->query("SELECT name, email FROM users WHERE id = '" . $user['id'] . "'");
    if ($checkResult && $checkResult->num_rows > 0) {
        $updatedUser = $checkResult->fetch_assoc();
        echo "<h3>Database after update:</h3>";
        echo "<p>Name: " . $updatedUser['name'] . "</p>";
        echo "<p>Email: " . $updatedUser['email'] . "</p>";
    }
    
} else {
    echo "<h3>No users with tokens found!</h3>";
    echo "<p>You need to login or signup first to get a token.</p>";
    echo "<p>Try logging in through your Android app or create a new account.</p>";
}

$conn->close();
?>
