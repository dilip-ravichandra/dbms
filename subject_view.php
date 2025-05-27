<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

if (!isset($_GET['subject_id'])) {
    echo "<h2 style='color:white;'>Subject not found.</h2>";
    exit;
}

$subjectId = intval($_GET['subject_id']);
$subjectQuery = mysqli_query($conn, "SELECT Name FROM Subjects WHERE Id = $subjectId");
$subjectData = mysqli_fetch_assoc($subjectQuery);
$subjectName = $subjectData ? $subjectData['Name'] : "Unknown Subject";

// Fetch playlists
$playlists = [];
$playlistQuery = mysqli_query($conn, "
    SELECT p.Id, p.Title, u.Name as TeacherName
    FROM Playlists p
    JOIN Users u ON p.TeacherId = u.Id
    WHERE p.SubjectId = $subjectId
");

while ($row = mysqli_fetch_assoc($playlistQuery)) {
    $playlists[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $subjectName; ?> - Playlists | RV Flix</title>
    <style>
        body {
            background-color: #121212;
            color:rgb(48, 205, 244);
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }
        .navbar {
            background-color: #1F1F1F;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h2 {
            margin: 0;
            color:rgb(0, 242, 255);
        }
        .navbar .right {
            color: white;
        }
        .topnav-links {
            display: flex;
            gap: 20px;
        }
        .topnav-links a {
            color:rgb(0, 242, 255);
            text-decoration: none;
            font-size: 14px;
        }
        .topnav-links a:hover {
            text-decoration: underline;
        }
        .container {
            padding: 40px;
        }
        .actions {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .action-button {
            background-color: rgb(0, 242, 255);
            color: black;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
        }
        .action-button:hover {
            background-color: rgb(0, 200, 220);
        }
        .playlist-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            margin-top: 20px;
        }
        .playlist-card {
            background-color: #1E1E1E;
            border-radius: 12px;
            width: 260px;
            padding: 20px;
            color: white;
            box-shadow: 0 0 8px #00ffff66;
        }
        .playlist-card h4 {
            margin: 0 0 10px;
            font-size: 18px;
            color:rgb(0, 242, 255);
        }
        .playlist-card p {
            font-size: 14px;
            color: #aaa;
            margin: 0;
        }
    </style>
</head>
<body>

<div class="navbar">
    <h2><?php echo $subjectName; ?> - Subject View</h2>
    <div class="right">
        Welcome, <?php echo $userName; ?> |
        <div class="topnav-links">
            <a href="student/dashboard.php">Home</a>
            <a href="#">Help</a>
            <a href="#">About</a>
        </div>
    </div>
</div>

<div class="container">
    <!-- Buttons to switch -->
    <div class="actions">
        <a href="subject_view.php?subject_id=<?php echo $subjectId; ?>" class="action-button">View Playlists</a>
        <a href="attempt_quiz.php?subject_id=<?php echo $subjectId; ?>" class="action-button">Attempt Quiz</a>
    </div>

    <!-- Playlist section -->
    <?php if (count($playlists) === 0): ?>
        <h3>No playlists found for this subject yet.</h3>
    <?php else: ?>
        <div class="playlist-grid">
            <?php foreach ($playlists as $playlist): ?>
                <div class="playlist-card">
                    <h4><?php echo $playlist['Title']; ?></h4>
                    <p>By: <?php echo $playlist['TeacherName']; ?></p>
                    <p><a href="playlist_view.php?playlist_id=<?php echo $playlist['Id']; ?>" style="color:#0ff;">View Playlist</a></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
