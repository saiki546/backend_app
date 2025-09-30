<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

echo "<h2>Test Login and Token Storage</h2>";

// Test login with a user
$testEmail = "saikiran2212215709@gmail.com"; // Use your existing user email
$testPassword = "password123"; // Use a test password

echo "<h3>Testing login for: $testEmail</h3>";

// Find user by email
$stmt = $conn->prepare("SELECT id, name, email, password, token FROM users WHERE email = ?");
$stmt->bind_param("s", $testEmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<p>✅ User found: " . $user['name'] . " (ID: " . $user['id'] . ")</p>";
    echo "<p>Current token: " . (empty($user['token']) ? 'NULL' : substr($user['token'], 0, 20) . '...') . "</p>";
    
    // Generate new token
    $newToken = bin2hex(random_bytes(32));
    echo "<p>Generated new token: " . substr($newToken, 0, 20) . "...</p>";
    
    // Update token in database
    $updateStmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
    $updateStmt->bind_param("ss", $newToken, $user['id']);
    
    if ($updateStmt->execute()) {
        echo "<p>✅ Token updated in database</p>";
        
        // Test the profile endpoint with this token
        echo "<h3>Testing profile endpoint with new token:</h3>";
        
        $url = 'http://localhost/auratext2/user_profile.php';
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $newToken
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        echo "<p>HTTP Code: $httpCode</p>";
        if ($error) {
            echo "<p>cURL Error: $error</p>";
        }
        echo "<p>Response: " . htmlspecialchars($response) . "</p>";
        
        if ($httpCode === 200) {
            echo "<p>✅ Profile endpoint works with token!</p>";
            echo "<p><strong>Use this token in your Android app: $newToken</strong></p>";
        } else {
            echo "<p>❌ Profile endpoint failed</p>";
        }
        
    } else {
        echo "<p>❌ Failed to update token: " . $updateStmt->error . "</p>";
    }
    
} else {
    echo "<p>❌ User not found with email: $testEmail</p>";
    echo "<p>Available users:</p>";
    $allUsers = $conn->query("SELECT id, name, email FROM users");
    if ($allUsers) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th></tr>";
        while ($row = $allUsers->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['name'] . "</td>";
            echo "<td>" . $row['email'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

$conn->close();
?>
