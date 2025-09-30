<?php
include 'db.php';
header('Content-Type: application/json');

echo "<h2>Database Check</h2>";

// Check if token column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'token'");
if ($result && $result->num_rows > 0) {
    echo "✅ Token column exists<br>";
} else {
    echo "❌ Token column missing<br>";
    echo "<p><strong>SOLUTION:</strong> Go to <a href='add_token_column.php'>add_token_column.php</a> to add the token column</p>";
}

// Check users table structure
echo "<h3>Users table structure:</h3>";
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
}

// Check if any users have tokens
echo "<h3>Users with tokens:</h3>";
$result = $conn->query("SELECT id, name, email, token FROM users WHERE token IS NOT NULL");
if ($result && $result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Token</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['name'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . (empty($row['token']) ? 'NULL' : substr($row['token'], 0, 20) . '...') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users with tokens found. You need to login first.</p>";
}

$conn->close();
?>
