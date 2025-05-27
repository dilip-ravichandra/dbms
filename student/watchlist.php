<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$query = "
    SELECT uv.Id, uv.Title, uv.FilePath
    FROM Watchlist w
    JOIN UploadVideos uv ON w.VideoId = uv.Id
    WHERE w.UserId = ?
    ORDER BY w.AddedDate DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$videos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $videos[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>My Watchlist - RV Flix</title>
  <style>
    body {
      margin: 0;
      background-color: #121212;
      color: rgb(48, 198, 244);
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background-color: #1F1F1F;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .navbar a {
      color: rgb(48, 198, 244);
      margin-left: 20px;
      text-decoration: none;
    }
    .container {
      padding: 30px 40px;
    }
    .video-card {
      background-color: #1E1E1E;
      border-radius: 10px;
      padding: 15px 20px;
      margin-bottom: 15px;
      box-shadow: 0 0 10px rgba(48, 198, 244, 0.3);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .video-title {
      font-size: 18px;
      font-weight: bold;
      color: white;
    }
    .video-actions {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    a.play-button {
      background-color: rgb(48, 198, 244);
      color: #121212;
      padding: 8px 14px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    a.delete-button {
      color: red;
      font-size: 20px;
      text-decoration: none;
      font-weight: bold;
    }
    a.delete-button:hover {
      color: #ff4d4d;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div><strong>RV Flix - Watchlist</strong></div>
  <div>
    Welcome, <?php echo $userName; ?> |
    <a href="dashboard.php">Dashboard</a>
    <a href="../logout.php">Logout</a>
  </div>
</div>

<div class="container">
  <h2>My Watchlist</h2>
  <?php if (empty($videos)): ?>
    <p>No videos in your watchlist.</p>
  <?php else: ?>
    <?php foreach ($videos as $video): ?>
      <div class="video-card">
        <div class="video-title"><?php echo htmlspecialchars($video['Title']); ?></div>
        <div class="video-actions">
          <a class="play-button" href="../watch_video.php?video_id=<?php echo $video['Id']; ?>">▶ Watch</a>
          <a class="delete-button" href="../remove_from_watchlist.php?video_id=<?php echo $video['Id']; ?>" title="Remove from Watchlist">❌</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

</body>
</html>
