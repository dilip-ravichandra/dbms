<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];
$userName = $_SESSION['user_name'];

if (!isset($_GET['video_id'])) {
    echo "Video ID is missing.";
    exit;
}

$videoId = intval($_GET['video_id']);

// Fetch video info
$video = null;
$stmt = mysqli_prepare($conn, "SELECT Title, FilePath FROM UploadVideos WHERE Id = ?");
mysqli_stmt_bind_param($stmt, "i", $videoId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $title, $filePath);
if (mysqli_stmt_fetch($stmt)) {
    $video = ["title" => $title, "filePath" => $filePath];
}
mysqli_stmt_close($stmt);

if (!$video) {
    echo "Video not found.";
    exit;
}

// Log watch history (if student)
if ($role === 'student') {
    $check = mysqli_query($conn, "SELECT Id FROM UserVideoWatch WHERE UserId = $userId AND VideoId = $videoId");
    if (mysqli_num_rows($check) === 0) {
        mysqli_query($conn, "
            INSERT INTO UserVideoWatch (UserId, VideoId,WatchedAt)
            VALUES ($userId, $videoId, NOW())
        ");
    } else {
        mysqli_query($conn, "
            UPDATE UserVideoWatch SET WatchedAt = NOW()
            WHERE UserId = $userId AND VideoId = $videoId
        ");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Watch Video - RV Flix</title>
  <style>
    body {
      margin: 0;
      background-color: #121212;
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background-color: #1F1F1F;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .navbar a {
      color: rgb(48, 198, 244);
      text-decoration: none;
      margin-left: 20px;
    }
    .video-container {
      padding: 30px;
      text-align: center;
    }
    video {
      width: 80%;
      max-width: 900px;
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(48, 198, 244, 0.3);
    }
    h2 {
      margin-bottom: 20px;
      color: rgb(48, 198, 244);
    }
  </style>
</head>
<body>

<div class="navbar">
  <div>
    <strong>RV Flix</strong>
    <a href="student/dashboard.php">Home</a>
    <a href="help.php">Help</a>
    <a href="about.php">About</a>
  </div>
  <div>Welcome, <?php echo htmlspecialchars($userName); ?></div>
</div>

<div class="video-container">
  <h2><?php echo htmlspecialchars($video['title']); ?></h2>
  <video controls>
    <source src="videos/<?php echo htmlspecialchars($video['filePath']); ?>" type="video/mp4">
    Your browser does not support the video tag.
  </video>
</div>

</body>
</html>
