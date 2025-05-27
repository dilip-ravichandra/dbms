<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch subjects assigned to teacher's program with video and quiz counts
$subjects = [];
$query = mysqli_query($conn, "
    SELECT s.Id, s.Name,
           (SELECT COUNT(*) FROM UploadVideos uv WHERE uv.SubjectId = s.Id AND uv.TeacherId = $userId) AS VideoCount,
           (SELECT COUNT(*) FROM Quizzes q WHERE q.SubjectId = s.Id AND q.TeacherId = $userId) AS QuizCount
    FROM Subjects s
    INNER JOIN Programs p ON s.ProgramId = p.Id
    INNER JOIN Users u ON u.ProgramId = p.Id
    WHERE u.Id = $userId
");
while ($row = mysqli_fetch_assoc($query)) {
    $subjects[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Teacher Dashboard - RV Flix</title>
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
      gap:30px;
      justify-content: flex-end;
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
      cursor: pointer;
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
    .card h3 {
      margin-top: 0;
      color: rgb(48, 198, 244);
    }
    ul li {
      margin-bottom: 10px;
    }
    footer {
      background-color: #1F1F1F;
      color: #888;
      padding: 15px;
      text-align: center;
      font-size: 14px;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }
    .subject-card {
      background-color: #222;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(48, 198, 244, 0.3);
    }
    .subject-card h4 {
      color: #00ffff;
      margin-bottom: 10px;
    }
    .subject-card p {
      color: #ccc;
      font-size: 14px;
      margin: 5px 0;
    }
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 45px;
      background-color: #2A2A2A;
      border-radius: 6px;
      overflow: hidden;
      z-index: 1000;
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
    .avatar-menu {
      position: relative;
      display: inline-block;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <h2>RV Flix</h2>
  <a href="#">Home</a>
  <a href="playlist_manage.php">Manage Playlists</a>
  <a href="upload_video.php">Upload Video</a>
  <a href="create_quiz.php">Create Quiz</a>
  <a href="#">Help</a>
</div>

<div class="main-content">
  <div class="navbar">
    <div>Welcome, <?php echo $userName; ?> (Teacher)</div>
    <div class="avatar-menu">
      <div class="avatar" onclick="toggleDropdown()"><?php echo strtoupper($userName[0]); ?></div>
      <div class="dropdown" id="avatarDropdown">
        <a href="../search_history.php">Search History</a>
        <a href="../delete_account.php">Delete Account</a>
        <a href="../logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="card">
      <h3>📚 Videos & Quizzes by Subject</h3>
      <?php if (count($subjects) === 0): ?>
        <p>You haven't uploaded any videos or created quizzes yet.</p>
      <?php else: ?>
        <div class="grid">
          <?php foreach ($subjects as $s): ?>
            <div class="subject-card">
              <h4><?php echo $s['Name']; ?></h4>
              <p>Videos Uploaded: <strong><?php echo $s['VideoCount']; ?></strong></p>
              <p>Quizzes Created: <strong><?php echo $s['QuizCount']; ?></strong></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <footer>
    © 2025 RV Flix — Built for RV College Students
  </footer>
</div>

<script>
function toggleDropdown() {
  const dropdown = document.getElementById("avatarDropdown");
  dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}
window.onclick = function(event) {
  if (!event.target.matches('.avatar')) {
    const dropdown = document.getElementById("avatarDropdown");
    if (dropdown) dropdown.style.display = "none";
  }
}
</script>

</body>
</html>
