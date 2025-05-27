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

// Fetch quizzes for this subject
$quizzes = [];
$quizQuery = mysqli_query($conn, "
    SELECT q.Id, q.Title, q.Description, u.Name AS TeacherName
    FROM Quizzes q
    JOIN Users u ON q.TeacherId = u.Id
    WHERE q.SubjectId = $subjectId
");

while ($row = mysqli_fetch_assoc($quizQuery)) {
    $quizzes[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attempt Quiz - <?php echo $subjectName; ?> | RV Flix</title>
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
        .quiz-card {
            background-color: #1E1E1E;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 8px #00ffff66;
        }
        .quiz-card h3 {
            margin: 0 0 10px;
            color:rgb(0, 242, 255);
        }
        .quiz-card p {
            color: #aaa;
            font-size: 14px;
            margin: 5px 0;
        }
        .quiz-card a {
            display: inline-block;
            margin-top: 10px;
            background-color: rgb(0, 242, 255);
            color: black;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .quiz-card a:hover {
            background-color: rgb(0, 200, 220);
        }
    </style>
</head>
<body>

<div class="navbar">
    <h2>Attempt Quiz - <?php echo $subjectName; ?></h2>
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
    <?php if (count($quizzes) === 0): ?>
        <h3>No quizzes available for this subject yet.</h3>
    <?php else: ?>
        <?php foreach ($quizzes as $quiz): ?>
            <div class="quiz-card">
                <h3><?php echo $quiz['Title']; ?></h3>
                <p>By: <?php echo $quiz['TeacherName']; ?></p>
                <p><?php echo $quiz['Description']; ?></p>
                <a href="student/start_quiz.php?quiz_id=<?php echo $quiz['Id']; ?>">Start Quiz</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
