<?php
$servername = "localhost:4306";
$username = "root";
$password = "Ravichandra";
$dbname = "rvflix_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

$sql = "SELECT * FROM videos ORDER BY uploaded_at DESC";
$result = $conn->query($sql);

$videos = [];
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($videos);
?>
