<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch program info
$programQuery = mysqli_query($conn, "
    SELECT p.Id, p.Name FROM Users u
    JOIN Programs p ON u.ProgramId = p.Id
    WHERE u.Id = $userId
");
$program = mysqli_fetch_assoc($programQuery);
$programId = $program['Id'];
$programName = $program['Name'];

// Fetch subjects and calculate video + quiz progress
$subjects = [];
$subjectQuery = mysqli_query($conn, "
    SELECT s.Id, s.Name
    FROM Subjects s
    WHERE s.ProgramId = $programId
");
while ($row = mysqli_fetch_assoc($subjectQuery)) {
    $subjectId = $row['Id'];
    $subjectName = $row['Name'];

    // Video progress
    $totalVideosRes = mysqli_query($conn, "
        SELECT COUNT(*) AS total
        FROM UploadVideos
        WHERE SubjectId = $subjectId
    ");
    $totalVideos = mysqli_fetch_assoc($totalVideosRes)['total'];

    $watchedVideosRes = mysqli_query($conn, "
        SELECT COUNT(DISTINCT uvw.VideoId) AS watched
        FROM UserVideoWatch uvw
        JOIN UploadVideos uv ON uv.Id = uvw.VideoId
        WHERE uvw.UserId = $userId AND uv.SubjectId = $subjectId
    ");
    $watchedVideos = mysqli_fetch_assoc($watchedVideosRes)['watched'];

    $videoProgress = ($totalVideos > 0) ? round(($watchedVideos / $totalVideos) * 100) : 0;

    // Quiz progress
    $quizScoreRes = mysqli_query($conn, "
        SELECT AVG(sqa.Score) AS avg_score
        FROM StudentQuizAttempts sqa
        JOIN Quizzes q ON q.Id = sqa.QuizId
        WHERE sqa.StudentId = $userId AND q.SubjectId = $subjectId
    ");
    $avgScore = mysqli_fetch_assoc($quizScoreRes)['avg_score'];
    $quizProgress = $avgScore !== null ? round($avgScore) : 0;

    $subjects[] = [
        'Id' => $subjectId,
        'Name' => $subjectName,
        'VideoProgress' => $videoProgress,
        'QuizProgress' => $quizProgress
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>RV Flix - Student Dashboard</title>
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
      justify-content:flex-end;
      align-items:right;
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
    .subjects-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
      padding: 30px;
    }
    .subject-card {
      background-color: #1E1E1E;
      width: 240px;
      border-radius: 12px;
      padding: 15px;
      box-shadow: 0 0 10px rgba(48,198,244,0.4);
      cursor: pointer;
    }
    .subject-card img {
      width: 100%;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
      background: #333;
    }
    .subject-card h4 {
      margin: 12px 0 6px;
      color: rgb(48, 198, 244);
    }
    .progress-bar {
      width: 100%;
      height: 10px;
      background-color: #333;
      border-radius: 5px;
      overflow: hidden;
      margin-bottom: 8px;
    }
    .progress-bar-inner {
      height: 100%;
      background-color: rgb(48, 198, 244);
    }
    .progress-label {
      font-size: 12px;
      margin-bottom: 4px;
    }
    footer {
      background-color: #1F1F1F;
      color: #aaa;
      padding: 15px;
      text-align: center;
    }
    .avatar-menu {
      position: relative;
      display: inline-block;
    }
    .progress-bar-inner {
  height: 100%;
  background-color: rgb(48, 198, 244);
  transition: width 0.3s ease;
}

  </style>
</head>
<body>

<div class="sidebar">
  <h2>RV Flix</h2>
  <a href="#">Home</a>
  <a href="recent.php">History</a>
  <a href="watchlist.php">Watchlist</a>
  <a href="../search.php">Search</a>
</div>

<div class="main-content">
  <div class="navbar">
    <div class="welcome">
      Welcome, <?php echo $userName; ?> (<?php echo $programName; ?>)
    </div>
    <div class="avatar-menu">
      <div class="avatar" onclick="toggleDropdown()"><?php echo strtoupper($userName[0]); ?></div>
      <div class="dropdown" id="avatarDropdown">
        <a href="../search_history.php">Search History</a>
        <a href="../delete_account.php">Delete Account</a>
        <a href="../logout.php">Logout</a>
      </div>
    </div>
  </div>

  <div class="subjects-grid">
    <?php foreach ($subjects as $subject): ?>
      <div class="subject-card" onclick="location.href='../subject_view.php?subject_id=<?php echo $subject['Id']; ?>'">
        <img src="../images/<?php echo strtolower(str_replace(' ', '_', $subject['Name'])); ?>.png" alt="Image">
        <h4><?php echo $subject['Name']; ?></h4>
        <div class="progress-label">Video Progress: <?php echo $subject['VideoProgress']; ?>%</div>
        <div class="progress-bar">
          <div class="progress-bar-inner" style="width: <?php echo $subject['VideoProgress']; ?>%;"></div>
        </div>
        <div class="progress-label">Quiz Score: <?php echo $subject['QuizProgress']; ?>%</div>
        <div class="progress-bar">
          <div class="progress-bar-inner" style="width: <?php echo $subject['QuizProgress']; ?>%;"></div>
        </div>
      </div>
    <?php endforeach; ?>
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