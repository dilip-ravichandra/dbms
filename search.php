<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$searchResults = [];
$searchTerm = "";

// Get student's program ID
$progQuery = mysqli_query($conn, "
    SELECT ProgramId FROM Users WHERE Id = $userId
");
$progRow = mysqli_fetch_assoc($progQuery);
$programId = $progRow['ProgramId'];

// Handle search
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["query"])) {
    $searchTerm = trim($_POST["query"]);
    if (!empty($searchTerm)) {
        // Log the search
        $stmt = mysqli_prepare($conn, "INSERT INTO SearchHistory (UserId, SearchQuery) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $userId, $searchTerm);
        mysqli_stmt_execute($stmt);

        // Search videos
        $searchTermEscaped = mysqli_real_escape_string($conn, $searchTerm);
        $searchQuery = mysqli_query($conn, "
            SELECT uv.Title, s.Name AS SubjectName, u.Name AS TeacherName, uv.Id AS VideoId
            FROM UploadVideos uv
            JOIN Subjects s ON uv.SubjectId = s.Id
            JOIN Users u ON uv.TeacherId = u.Id
            WHERE s.ProgramId = $programId
              AND uv.Title LIKE '%$searchTermEscaped%'
            ORDER BY uv.Title ASC
        ");

        while ($row = mysqli_fetch_assoc($searchQuery)) {
            $searchResults[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Search - RV Flix</title>
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
      justify-content: flex-end;
      gap:30px;
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
      padding: 40px;
    }
    input[type="text"] {
      padding: 10px;
      width: 300px;
      border-radius: 6px;
      border: 1px solid #555;
      background-color: #1C1C1C;
      color: white;
    }
    button {
      padding: 10px 20px;
      background-color: rgb(48, 198, 244);
      color: black;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .results {
      margin-top: 30px;
      background-color: #1E1E1E;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 0 10px rgba(48, 198, 244, 0.4);
    }
    .results ul li {
      margin-bottom: 12px;
    }
    .results a {
      color: #0ff;
      text-decoration: none;
    }
    .results a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div>Welcome, <?php echo $userName; ?> (Student)</div>
  <div class="avatar"><?php echo strtoupper($userName[0]); ?></div>
  <div class="menu">
    <button class="menu-button">⋮</button>
    <div class="dropdown">
      <a href="search_history.php">Search History</a>
      <a href="delete_account.php">Delete Account</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="container">
  <h2>🔍 Search Videos in Your Program</h2>
  <form method="POST">
    <input type="text" name="query" placeholder="Enter video title..." required value="<?php echo htmlspecialchars($searchTerm); ?>">
    <button type="submit">Search</button>
  </form>

  <?php if (!empty($searchTerm)): ?>
  <div class="results">
    <h3>Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h3>
    <?php if (count($searchResults) === 0): ?>
      <p>No matching videos found.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($searchResults as $vid): ?>
          <li>
            <strong><?php echo $vid['Title']; ?></strong> (<?php echo $vid['SubjectName']; ?> by <?php echo $vid['TeacherName']; ?>)
            <a href="watch_video.php?video_id=<?php echo $vid['VideoId']; ?>">▶ Watch</a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
