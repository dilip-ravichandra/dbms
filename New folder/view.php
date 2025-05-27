<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}


// Check login (only students or teachers can view)
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Connect to database
$conn = new mysqli('localhost', 'root', '', 'rvflix', 3307);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Fetch videos
$sql = "SELECT title, description, file_path FROM videos ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

$videos = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($videos);

$conn->close();
?>
