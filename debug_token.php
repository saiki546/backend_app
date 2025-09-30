<?php
include 'db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

echo "<h2>Token Debug Information</h2>";

// Debug 1: Check all headers
echo "<h3>1. All Request Headers:</h3>";
echo "<pre>";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
echo "</pre>";

// Debug 2: Check Authorization header specifically
echo "<h3>2. Authorization Header:</h3>";
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? 'NOT_FOUND';
echo "<p>Authorization header: <strong>" . htmlspecialchars($authHeader) . "</strong></p>";

// Debug 3: Check if token starts with Bearer
if ($authHeader !== 'NOT_FOUND') {
    if (str_starts_with($authHeader, 'Bearer ')) {
        $token = substr($authHeader, 7);
        echo "<p>✅ Token found: <strong>" . htmlspecialchars(substr($token, 0, 20)) . "...</strong></p>";
        
        // Debug 4: Check if token exists in database
        echo "<h3>3. Database Token Check:</h3>";
        $stmt = $conn->prepare("SELECT id, name, email, token FROM users WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<p>✅ Token found in database for user: <strong>" . $user['name'] . "</strong> (ID: " . $user['id'] . ")</p>";
        } else {
            echo "<p>❌ Token NOT found in database</p>";
            
            // Show all tokens in database for comparison
            echo "<h4>All tokens in database:</h4>";
            $allTokens = $conn->query("SELECT id, name, token FROM users WHERE token IS NOT NULL");
            if ($allTokens) {
                echo "<table border='1'>";
                echo "<tr><th>ID</th><th>Name</th><th>Token (first 20 chars)</th></tr>";
                while ($row = $allTokens->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . substr($row['token'], 0, 20) . "...</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
    } else {
        echo "<p>❌ Authorization header does not start with 'Bearer '</p>";
    }
} else {
    echo "<p>❌ No Authorization header found</p>";
}

// Debug 5: Check request method
echo "<h3>4. Request Method:</h3>";
echo "<p>Method: <strong>" . $_SERVER['REQUEST_METHOD'] . "</strong></p>";

// Debug 6: Check request body for PUT requests
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    echo "<h3>5. Request Body:</h3>";
    $input = file_get_contents('php://input');
    echo "<p>Raw input: <pre>" . htmlspecialchars($input) . "</pre></p>";
    
    $jsonData = json_decode($input, true);
    if ($jsonData) {
        echo "<p>Parsed JSON: <pre>" . htmlspecialchars(json_encode($jsonData, JSON_PRETTY_PRINT)) . "</pre></p>";
    } else {
        echo "<p>❌ Invalid JSON data</p>";
    }
}

$conn->close();
?>
