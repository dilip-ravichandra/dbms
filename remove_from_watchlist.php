<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

if (isset($_GET['video_id'])) {
    $videoId = intval($_GET['video_id']);
    $stmt = mysqli_prepare($conn, "DELETE FROM Watchlist WHERE UserId = ? AND VideoId = ?");
    mysqli_stmt_bind_param($stmt, "ii", $userId, $videoId);
    mysqli_stmt_execute($stmt);
}

header("Location: student/watchlist.php");
exit;
?>
