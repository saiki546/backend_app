<?php
include 'db.php';
header('Content-Type: application/json');

echo "<h2>Profile Update Debug Test</h2>";

// Test 1: Check if token column exists
echo "<h3>1. Checking database structure:</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error describing table: " . $conn->error;
}

// Test 2: Check users table data
echo "<h3>2. Current users in database:</h3>";
$result = $conn->query("SELECT id, name, email, token, created_at FROM users");
if ($result) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Token</th><th>Created At</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . (empty($row['token']) ? 'NULL' : substr($row['token'], 0, 10) . '...') . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "Error querying users: " . $conn->error;
}

// Test 3: Test user_profile.php endpoint
echo "<h3>3. Testing user_profile.php endpoint:</h3>";
echo "<p>To test the profile update, you can use the following curl command:</p>";
echo "<pre>";
echo "curl -X PUT http://localhost/auratext2/user_profile.php \\<br>";
echo "  -H 'Content-Type: application/json' \\<br>";
echo "  -H 'Authorization: Bearer YOUR_TOKEN_HERE' \\<br>";
echo "  -d '{\"name\":\"Updated Name\",\"email\":\"updated@example.com\"}'";
echo "</pre>";

// Test 4: Check if user_profile.php file exists and is readable
echo "<h3>4. Checking user_profile.php file:</h3>";
if (file_exists('user_profile.php')) {
    echo "✅ user_profile.php exists<br>";
    echo "File size: " . filesize('user_profile.php') . " bytes<br>";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime('user_profile.php')) . "<br>";
} else {
    echo "❌ user_profile.php does not exist<br>";
}

// Test 5: Check database connection
echo "<h3>5. Database connection test:</h3>";
if ($conn->connect_error) {
    echo "❌ Database connection failed: " . $conn->connect_error;
} else {
    echo "✅ Database connection successful<br>";
    echo "Database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";
}

$conn->close();
?>
