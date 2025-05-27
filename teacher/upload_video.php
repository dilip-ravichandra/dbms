<?php
session_start();
include("../db_connection.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$message = "";

// Handle video upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['upload_video'])) {
    $title = trim($_POST['title']);
    $playlistId = intval($_POST['playlist']);
    $subjectId = intval($_POST['subject']);

    if (!empty($_FILES["video"]["name"]) && $title && $playlistId && $subjectId) {
        $targetDir = __DIR__ . '/../videos/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $fileName = time() . "_" . basename($_FILES["video"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFilePath)) {
            $stmt = mysqli_prepare($conn, "
                INSERT INTO UploadVideos (Title, FilePath, PlaylistId, TeacherId, SubjectId)
                VALUES (?, ?, ?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "ssiii", $title, $fileName, $playlistId, $userId, $subjectId);
            mysqli_stmt_execute($stmt);
            $message = "✅ Video uploaded successfully!";
        } else {
            $message = "❌ Error moving uploaded video. Check folder permissions.";
        }
    } else {
        $message = "⚠️ All fields are required.";
    }
}

// Fetch teacher's playlists and subjects
$playlists = [];
$playlistQuery = mysqli_query($conn, "SELECT Id, Title FROM Playlists WHERE TeacherId = $userId");
while ($row = mysqli_fetch_assoc($playlistQuery)) {
    $playlists[] = $row;
}

$subjects = [];
$subjectQuery = mysqli_query($conn, "SELECT Id, Name FROM Subjects");
while ($s = mysqli_fetch_assoc($subjectQuery)) {
    $subjects[] = $s;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Upload Video - RV Flix</title>
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
      justify-content: space-between;
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
    input, select {
      width: 100%;
      padding: 10px;
      margin-top: 10px;
      border-radius: 6px;
      background-color: #2D2D2D;
      color: white;
      border: 1px solid #444;
    }
    button {
      background-color: rgb(48, 198, 244);
      color: #121212;
      padding: 10px 20px;
      margin-top: 20px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>RV Flix</h2>
  <a href="dashboard.php">Home</a>
  <a href="playlist_manage.php">Manage Playlists</a>
  <a href="upload_video.php">Upload Video</a>
  <a href="#">Help</a>
</div>

<div class="main-content">
  <div class="navbar">
    <div>Welcome, <?php echo $userName; ?> (Teacher)</div>
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
    <?php if ($message): ?><p><?php echo $message; ?></p><?php endif; ?>
    <div class="card">
      <h3>Upload Video</h3>
      <form method="POST" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="Video Title" required>
        <select name="subject" required>
          <option value="">-- Select Subject --</option>
          <?php foreach ($subjects as $sub): ?>
            <option value="<?php echo $sub['Id']; ?>"><?php echo $sub['Name']; ?></option>
          <?php endforeach; ?>
        </select>
        <select name="playlist" required>
          <option value="">-- Select Playlist --</option>
          <?php foreach ($playlists as $pl): ?>
            <option value="<?php echo $pl['Id']; ?>"><?php echo $pl['Title']; ?></option>
          <?php endforeach; ?>
        </select>
        <input type="file" name="video" accept="video/*" required>
        <button type="submit" name="upload_video">Upload</button>
      </form>
    </div>
  </div>

</div>

</body>
</html>
