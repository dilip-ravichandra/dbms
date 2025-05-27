<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

if (!isset($_GET['playlist_id'])) {
    echo "<h2 style='color:white;'>Playlist not found.</h2>";
    exit;
}

$playlistId = intval($_GET['playlist_id']);

// Get playlist info
$playlistQuery = mysqli_query($conn, "
    SELECT p.Title, s.Name as SubjectName, u.Name as TeacherName
    FROM Playlists p
    JOIN Subjects s ON p.SubjectId = s.Id
    JOIN Users u ON p.TeacherId = u.Id
    WHERE p.Id = $playlistId
");
$playlistData = mysqli_fetch_assoc($playlistQuery);

if (!$playlistData) {
    echo "<h2 style='color:white;'>Playlist not found.</h2>";
    exit;
}

$playlistTitle = $playlistData['Title'];
$subjectName = $playlistData['SubjectName'];
$teacherName = $playlistData['TeacherName'];

// Fetch videos in this playlist
$videos = [];
$videoQuery = mysqli_query($conn, "
    SELECT Id, Title, FilePath FROM UploadVideos
    WHERE PlaylistId = $playlistId
    ORDER BY UploadedAt DESC
");
while ($row = mysqli_fetch_assoc($videoQuery)) {
    $videos[] = $row;
}

// Get watchlist entries for this user
$watchlist = [];
$watchlistQuery = mysqli_query($conn, "SELECT VideoId FROM Watchlist WHERE UserId = $userId");
while ($wl = mysqli_fetch_assoc($watchlistQuery)) {
    $watchlist[] = $wl['VideoId'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title><?php echo $playlistTitle; ?> | RV Flix</title>
  <style>
    body {
      background-color: #121212;
      color:rgb(55, 208, 255);
      font-family: 'Segoe UI', sans-serif;
      margin: 0;
      padding: 0;
    }
    .navbar {
      background-color: #1F1F1F;
      padding: 20px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
    }
    .navbar h2 {
      margin: 0;
    }
    .navbar .right {
      display: flex;
      align-items: center;
      gap: 15px;
      color: white;
    }
    .avatar {
      background-color:rgb(55, 208, 255);
      color: #1C1C1C;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }
    .menu {
      position: relative;
      display: inline-block;
    }
    .menu-button {
      background: none;
      border: none;
      font-size: 24px;
      color: white;
      cursor: pointer;
    }
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 50px;
      background-color: #2A2A2A;
      border-radius: 6px;
      overflow: hidden;
      z-index: 10;
    }
    .dropdown a {
      display: block;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
    }
    .dropdown a:hover {
      background-color: #3A3A3A;
    }
    .menu:hover .dropdown {
      display: block;
    }
    .topnav-links a {
      color:rgb(55, 208, 255);
      margin-left: 20px;
      text-decoration: none;
    }
    .topnav-links a:hover {
      text-decoration: underline;
    }
    .container {
      padding: 40px;
    }
    .video-list {
      margin-top: 20px;
    }
    .video-card {
      background-color: #1E1E1E;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 15px;
      box-shadow: 0 0 8px #00ffff33;
    }
    .video-card h4 {
      margin: 0 0 8px;
      color:rgb(55, 208, 255);
    }
    .video-card a {
      color: #0ff;
      text-decoration: none;
    }
    .video-card a:hover {
      text-decoration: underline;
    }
    .video-actions {
      margin-top: 8px;
    }
    .watchlist-message {
      color: lime;
      font-size: 14px;
      display: none;
    }
  </style>
  <script>
    function addToWatchlist(videoId, icon) {
      fetch(`add_to_watchlist.php?video_id=${videoId}`)
        .then(response => response.text())
        .then(msg => {
          icon.textContent = '✔';
          icon.style.color = 'lime';
          let note = document.getElementById('msg-' + videoId);
          note.style.display = 'block';
        });
    }
  </script>
</head>
<body>

<div class="navbar">
  <h2>RV Flix</h2>
  <div class="right">
    Welcome, <?php echo $userName; ?>
    <div class="avatar"><?php echo strtoupper($userName[0]); ?></div>
    <div class="topnav-links">
        <a href="student/dashboard.php">Home</a>
        <a href="#">Help</a>
        <a href="#">About</a>
    </div>
    <div class="menu">
      <button class="menu-button">:</button>
      <div class="dropdown">
        <a href="search_history.php">Search History</a>
        <a href="delete_account.php">Delete Account</a>
        <a href="logout.php">Logout</a>
      </div>
    </div>
  </div>
</div>

<div class="container">
  <h3>🎓 <?php echo $subjectName; ?> | 👤 <?php echo $teacherName; ?> | 📁 Playlist: <?php echo $playlistTitle; ?></h3>

  <?php if (count($videos) === 0): ?>
    <p>No videos available in this playlist.</p>
  <?php else: ?>
    <div class="video-list">
      <?php foreach ($videos as $video): ?>
        <div class="video-card">
          <h4><?php echo $video['Title']; ?></h4>
          <div class="video-actions">
            <a href="watch_video.php?video_id=<?php echo $video['Id']; ?>" style="color:#0ff; margin-right: 10px;">▶ Watch</a>
            <?php if (in_array($video['Id'], $watchlist)): ?>
              <span style="color: lime; font-size: 18px;">✔</span>
            <?php else: ?>
              <span onclick="addToWatchlist(<?php echo $video['Id']; ?>, this)" style="font-size: 18px; color: #0ff; cursor: pointer;">➕</span>
              <span id="msg-<?php echo $video['Id']; ?>" class="watchlist-message">Added to watchlist</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

</body>
</html>
