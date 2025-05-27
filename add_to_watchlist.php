<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "Unauthorized";
    exit;
}

$userId = $_SESSION['user_id'];

if (!isset($_GET['video_id'])) {
    echo "Invalid request";
    exit;
}

$videoId = intval($_GET['video_id']);

// Check if already in watchlist
$checkQuery = mysqli_query($conn, "SELECT * FROM Watchlist WHERE UserId = $userId AND VideoId = $videoId");
if (mysqli_num_rows($checkQuery) === 0) {
    // Insert if not present
    mysqli_query($conn, "INSERT INTO Watchlist (UserId, VideoId) VALUES ($userId, $videoId)");
    echo "Added";
} else {
    echo "Already in Watchlist";
}
?>
