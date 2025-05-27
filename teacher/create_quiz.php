<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get teacher's program ID
$result = mysqli_query($conn, "SELECT ProgramId FROM Users WHERE Id = $userId");
$row = mysqli_fetch_assoc($result);
$programId = $row['ProgramId'];

// Get subjects for that program
$subjects = [];
$subjectQuery = mysqli_query($conn, "SELECT Id, Name FROM Subjects WHERE ProgramId = $programId");
while ($sub = mysqli_fetch_assoc($subjectQuery)) {
    $subjects[] = $sub;
}

// Handle quiz creation
$success = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_quiz'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $subjectId = intval($_POST['subject']);
    $onetime = isset($_POST['onetime']) ? 1 : 0;
    $question = mysqli_real_escape_string($conn, $_POST['question']);
    $option1 = mysqli_real_escape_string($conn, $_POST['option1']);
    $option2 = mysqli_real_escape_string($conn, $_POST['option2']);
    $option3 = mysqli_real_escape_string($conn, $_POST['option3']);
    $option4 = mysqli_real_escape_string($conn, $_POST['option4']);
    $correct = mysqli_real_escape_string($conn, $_POST['correct']);

    $insertQuiz = mysqli_query($conn, "
        INSERT INTO Quizzes (Title, SubjectId, TeacherId, OneTime, Description, CreatedAt)
        VALUES ('$title', $subjectId, $userId, $onetime, '$description', NOW())
    ");

    if ($insertQuiz) {
        $quizId = mysqli_insert_id($conn);
        $insertQuestion = mysqli_query($conn, "
            INSERT INTO QuizQuestions (QuizId, Questiontext, OptionA, OptionB, OptionC, OptionD, CorrectOPTION)
            VALUES ($quizId, '$question', '$option1', '$option2', '$option3', '$option4', '$correct')
        ");
        $success = $insertQuestion ? "✅ Quiz created successfully." : "❌ Error adding question.";
    } else {
        $success = "❌ Error: " . mysqli_error($conn);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $quizId = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM QuizQuestions WHERE QuizId = $quizId");
    mysqli_query($conn, "DELETE FROM Quizzes WHERE Id = $quizId AND TeacherId = $userId");
    header("Location: create_quiz.php");
    exit;
}

// Fetch quizzes
$quizzes = [];
$quizResult = mysqli_query($conn, "
    SELECT q.Id, q.Title, q.Description, q.OneTime, q.CreatedAt, s.Name AS SubjectName
    FROM Quizzes q
    JOIN Subjects s ON q.SubjectId = s.Id
    WHERE q.TeacherId = $userId
    ORDER BY q.CreatedAt DESC
");
while ($q = mysqli_fetch_assoc($quizResult)) {
    $quizzes[] = $q;
}
?><!DOCTYPE html><html>
<head>
    <title>Create Quiz | RVFlix</title>
    <style>
        body {
            align:center;
            background-color: #121212;
            color: #30c6f4;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }
        nav {
            background-color: #1F1F1F;
            padding: 10px 20px;
            margin-bottom: 30px;
        }
        nav a {
            color: #30c6f4;
            margin-right: 20px;
            text-decoration: none;
        }
        h2 {
            color: #30c6f4;
            align:center;
        }
        .form-container {
            background-color: #1F1F1F;
            padding: 20px;
            border-radius: 12px;
            width: 500px;
            margin-bottom: 30px;
            align:center;
        }
        label, select, input[type="text"], textarea, input[type="submit"] {
            display: block;
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 6px;
            border: none;
        }
        input[type="submit"] {
            background-color: #30c6f4;
            color: #121212;
            font-weight: bold;
            cursor: pointer;
        }
        .message {
            margin-bottom: 20px;
            color: #0f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            color: white;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #333;
            text-align: left;
        }
        a {
            color: #30c6f4;
            text-decoration: none;
            margin-right: 10px;
        }
    </style>
</head>
<body><nav>
    <a href="dashboard.php">Dashboard</a>
    <a href="create_quiz.php">Create Quiz</a>
    <a href="view_responses.php">View Responses</a>
    <a href="../logout.php">Logout</a>
</nav><h2>Create a New Quiz</h2>
<?php if ($success): ?>
    <p class="message"><?php echo $success; ?></p>
<?php endif; ?>
<div class="form-container">
    <form method="POST">
        <label>Quiz Title:</label>
        <input type="text" name="title" required><label>Description:</label>
    <textarea name="description" rows="3" required></textarea>

    <label>Subject:</label>
    <select name="subject" required>
        <option value="">-- Select Subject --</option>
        <?php foreach ($subjects as $sub): ?>
            <option value="<?php echo $sub['Id']; ?>"><?php echo $sub['Name']; ?></option>
        <?php endforeach; ?>
    </select>

    <label>Question:</label>
    <input type="text" name="question" required>
    <input type="text" name="option1" placeholder="Option 1" required>
    <input type="text" name="option2" placeholder="Option 2" required>
    <input type="text" name="option3" placeholder="Option 3" required>
    <input type="text" name="option4" placeholder="Option 4" required>
    <input type="text" name="correct" placeholder="Correct Answer (Option 1/2/3/4)" required>

    <label>
        <input type="checkbox" name="onetime"> One-time Attempt Only
    </label>

    <input type="submit" name="create_quiz" value="Create Quiz">
</form>

</div><?php if (count($quizzes) > 0): ?><h2>Your Quizzes</h2>
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Subject</th>
            <th>Description</th>
            <th>One-Time</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?php echo htmlspecialchars($quiz['Title']); ?></td>
                <td><?php echo $quiz['SubjectName']; ?></td>
                <td><?php echo htmlspecialchars($quiz['Description']); ?></td>
                <td><?php echo $quiz['OneTime'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $quiz['CreatedAt']; ?></td>
                <td>
                    <a href="create_quiz.php?delete=<?php echo $quiz['Id']; ?>" onclick="return confirm('Delete this quiz?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php endif; ?></body>
</html>