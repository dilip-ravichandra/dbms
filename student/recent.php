<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch recently watched videos
$recentVideos = [];
$query = mysqli_query($conn, "
    SELECT uv.Title, s.Name AS SubjectName, uv.Id AS VideoId, uw.WatchedAt
    FROM UserVideoWatch uw
    JOIN UploadVideos uv ON uw.VideoId = uv.Id
    JOIN Subjects s ON uv.SubjectId = s.Id
    WHERE uw.UserId = $userId
    ORDER BY uw.WatchedAt DESC
    LIMIT 5
");
while ($row = mysqli_fetch_assoc($query)) {
    $recentVideos[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Recently Watched - RV Flix</title>
  <style>
    body {
      margin: 0;
      background-color: #121212;
      color: rgb(48, 198, 244);
      font-family: 'Segoe UI', sans-serif;
      display: flex;
    }
    .sidebar {
      width: 200px;
      background-color: #1C1C1C;
      height: 100vh;
      padding-top: 30px;
      position: fixed;
      left: 0;
      top: 0;
    }
    .sidebar h2 {
      text-align: center;
      color: rgb(48, 198, 244);
      margin-bottom: 30px;
    }
    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: rgb(48, 198, 244);
      text-decoration: none;
    }
    .sidebar a:hover {
      background-color: #2D2D2D;
    }
    .main-content {
      margin-left: 200px;
      flex-grow: 1;
    }
    .navbar {
      background-color: #1F1F1F;
      padding: 20px 40px;
      display: flex;
      justify-content: flex-end;
      gap:30px
      align-items: center;
    }
    .avatar {
      background-color: rgb(48, 198, 244);
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
      top: 45px;
      background-color: #2A2A2A;
      border-radius: 6px;
      overflow: hidden;
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
    .container {
      padding: 30px 40px;
    }
    .card {
      background-color: #1E1E1E;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(48, 198, 244, 0.4);
    }
    ul li {
      margin-bottom: 10px;
    }
    a.watch-link {
      color: #0ff;
      text-decoration: none;
    }
    a.watch-link:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>RV Flix</h2>
  <a href="dashboard.php">Home</a>
  <a href="recent.php">History</a>
  <a href="watchlist.php">Watchlist</a>
  <a href="../search.php">Search</a>
</div>

<div class="main-content">
  <div class="navbar">
    <div>Welcome, <?php echo $userName; ?></div>
    <div class="avatar"><?php echo strtoupper($userName[0]); ?></div>
    <div class="menu">
      <button class="menu-button">⋮</button>
      <div class="dropdown">
        <a href="../search_history.php">Search History</a>
        <a href="../delete_account.php">Delete Account</a>
        <a href="../logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="card">
      <h3>Recently Watched Videos</h3>
      <?php if (count($recentVideos) === 0): ?>
        <p>You haven't watched any videos yet.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($recentVideos as $vid): ?>
            <li>
              <strong><?php echo $vid['Title']; ?></strong> (<?php echo $vid['SubjectName']; ?>)
              <a class="watch-link" href="../watch_video.php?video_id=<?php echo $vid['VideoId']; ?>">▶ Watch Again</a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

</div>

</body>
</html>
