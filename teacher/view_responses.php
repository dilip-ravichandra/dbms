<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$teacherId = $_SESSION['user_id'];

// Get all programs for dropdown
$programs = mysqli_query($conn, "SELECT Id, Name FROM Programs");

// Handle filter
$programId = isset($_GET['program']) ? intval($_GET['program']) : 0;

$query = "
    SELECT qr.*, u.Name AS StudentName, q.Title AS QuizTitle, p.Name AS ProgramName
    FROM quizresults qr
    JOIN Users u ON qr.StudentId = u.Id
    JOIN Quizzes q ON qr.QuizId = q.Id
    JOIN Programs p ON q.ProgramId = p.Id
    WHERE q.TeacherId = $teacherId
";

if ($programId > 0) {
    $query .= " AND q.ProgramId = $programId";
}

$query .= " ORDER BY qr.AttemptedAt DESC";
$results = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Responses | RV Flix</title>
    <style>
        body {
            margin: 0;
            background-color: #121212;
            font-family: 'Segoe UI', sans-serif;
            color: #30c6f4;
        }
        .navbar {
            background-color: #1F1F1F;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: #30c6f4;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }
        .container {
            padding: 40px;
        }
        select, table {
            margin-top: 20px;
            background-color: #1F1F1F;
            color: #30c6f4;
            border: 1px solid #30c6f4;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #30c6f4;
        }
        th {
            background-color: #1a1a1a;
        }
        tr:hover {
            background-color: #1c1c1c;
        }
        h2 {
            color: #30c6f4;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div style="font-weight: bold; font-size: 20px;">RV Flix</div>
    <div>
        <a href="dashboard.php">Home</a>
        <a href="upload_video.php">Upload Video</a>
        <a href="create_quiz.php">Quizzes</a>
        <a href="view_responses.php">View Responses</a>
    </div>
</div>

<div class="container">
    <h2>Student Quiz Responses</h2>

    <form method="GET">
        <label for="program">Filter by Program:</label>
        <select name="program" id="program" onchange="this.form.submit()">
            <option value="0">All Programs</option>
            <?php while ($p = mysqli_fetch_assoc($programs)): ?>
                <option value="<?= $p['Id'] ?>" <?= ($p['Id'] == $programId) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['Name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Quiz Title</th>
                <th>Student</th>
                <th>Program</th>
                <th>Score</th>
                <th>Attempted At</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($results) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($results)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['QuizTitle']) ?></td>
                        <td><?= htmlspecialchars($row['StudentName']) ?></td>
                        <td><?= htmlspecialchars($row['ProgramName']) ?></td>
                        <td><?= $row['Score'] ?></td>
                        <td><?= $row['AttemptedAt'] ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No responses found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>