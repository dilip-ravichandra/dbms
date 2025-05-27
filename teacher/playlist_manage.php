<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$message = "";

// Handle create playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_playlist'])) {
    $title = trim($_POST['title']);
    $subjectId = intval($_POST['subject']);

    if ($title && $subjectId) {
        $stmt = mysqli_prepare($conn, "INSERT INTO Playlists (Title, TeacherId, SubjectId) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sii", $title, $userId, $subjectId);
        mysqli_stmt_execute($stmt);
        $message = "✅ Playlist created successfully!";
    } else {
        $message = "⚠️ Fill all fields to create a playlist.";
    }
}

// Handle delete playlist
if (isset($_GET['delete_playlist'])) {
    $pid = intval($_GET['delete_playlist']);
    mysqli_query($conn, "DELETE FROM Playlists WHERE Id = $pid AND TeacherId = $userId");
    $message = "🗑 Playlist deleted.";
}

// Handle delete video
if (isset($_GET['delete_video'])) {
    $vid = intval($_GET['delete_video']);
    mysqli_query($conn, "DELETE FROM UploadVideos WHERE Id = $vid AND TeacherId = $userId");
    $message = "🗑 Video deleted.";
}

// Fetch teacher's playlists
$playlists = [];
$playlistQuery = mysqli_query($conn, "
    SELECT p.Id, p.Title, s.Name AS SubjectName
    FROM Playlists p
    JOIN Subjects s ON p.SubjectId = s.Id
    WHERE p.TeacherId = $userId
");
while ($row = mysqli_fetch_assoc($playlistQuery)) {
    $playlists[] = $row;
}

// Fetch all subjects for dropdown
$subjects = [];
$subjectQuery = mysqli_query($conn, "SELECT Id, Name FROM Subjects");
while ($s = mysqli_fetch_assoc($subjectQuery)) {
    $subjects[] = $s;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Manage Playlists - RV Flix</title>
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
    .menu { position: relative; }
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
      margin-bottom: 25px;
    }
    form input, form select {
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      width: 100%;
      background-color: #2D2D2D;
      color: white;
      border: 1px solid #444;
    }
    form button {
      background-color: rgb(48, 198, 244);
      border: none;
      color: #121212;
      font-weight: bold;
      padding: 10px 20px;
      cursor: pointer;
      border-radius: 6px;
    }
    ul li {
      margin-bottom: 8px;
    }
    .btn-delete {
      color: red;
      margin-left: 10px;
      text-decoration: none;
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
      <h3>Create New Playlist</h3>
      <form method="POST">
        <input type="text" name="title" placeholder="Playlist Title" required>
        <select name="subject" required>
          <option value="">-- Select Subject --</option>
          <?php foreach ($subjects as $sub): ?>
            <option value="<?php echo $sub['Id']; ?>"><?php echo $sub['Name']; ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" name="create_playlist">Create Playlist</button>
      </form>
    </div>

    <div class="card">
      <h3>My Playlists</h3>
      <?php if (count($playlists) === 0): ?>
        <p>You have no playlists.</p>
      <?php else: ?>
        <ul>
        <?php foreach ($playlists as $pl): ?>
          <li>
            <strong><?php echo $pl['Title']; ?></strong> (<?php echo $pl['SubjectName']; ?>)
            <a href="?delete_playlist=<?php echo $pl['Id']; ?>" class="btn-delete">[Delete Playlist]</a>
            <ul>
              <?php
              $videos = mysqli_query($conn, "SELECT Id, Title FROM UploadVideos WHERE PlaylistId = {$pl['Id']} AND TeacherId = $userId");
              while ($vid = mysqli_fetch_assoc($videos)):
              ?>
              <li>
                <?php echo $vid['Title']; ?>
                <a href="?delete_video=<?php echo $vid['Id']; ?>" class="btn-delete">[Delete Video]</a>
              </li>
              <?php endwhile; ?>
            </ul>
          </li>
        <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>

</div>

</body>
</html>
