<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$submitted = false;
$message = "";

// Check if quiz exists
$quizQuery = mysqli_query($conn, "
    SELECT q.*, s.Name AS SubjectName
    FROM Quizzes q
    JOIN Subjects s ON q.SubjectId = s.Id
    WHERE q.Id = $quizId
");
$quiz = mysqli_fetch_assoc($quizQuery);
if (!$quiz) {
    die("Quiz not found.");
}

// Fetch first question
$questionQuery = mysqli_query($conn, "
    SELECT * FROM QuizQuestions WHERE QuizId = $quizId LIMIT 1
");
$question = mysqli_fetch_assoc($questionQuery);
if (!$question) {
    die("No questions found for this quiz.");
}

// Check if one-time and already attempted
if (!empty($quiz['OneTime'])) {
    $attemptCheck = mysqli_query($conn, "
        SELECT * FROM QuizAttempts WHERE QuizId = $quizId AND StudentId = $userId AND QuestionId = {$question['Id']}
    ");
    if (mysqli_num_rows($attemptCheck) > 0) {
        $submitted = true;
        $attemptRow = mysqli_fetch_assoc($attemptCheck);
        $message = $attemptRow['IsCorrect'] ? "✅ You answered correctly!" : "❌ Incorrect. Correct Answer: <strong>{$question['CorrectOption']}</strong>";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$submitted) {
    $selected = mysqli_real_escape_string($conn, $_POST['answer']);
    $questionId = intval($_POST['question_id']);
    $isCorrect = 0;

    // Get correct answer
    $questionResult = mysqli_query($conn, "
        SELECT CorrectOPTION FROM QuizQuestions WHERE Id = $questionId
    ");
    $questionData = mysqli_fetch_assoc($questionResult);
    if ($questionData && strtolower(trim($selected)) === strtolower(trim($question['CorrectOption']))) {
        $isCorrect = 1;
    }

    // Store attempt
    mysqli_query($conn, "
        INSERT INTO QuizAttempts (QuizId, StudentId, QuestionId, AnswerGiven, IsCorrect, AttemptedAt)
        VALUES ($quizId, $userId, $questionId, '$selected', $isCorrect, NOW())
    ");

    $submitted = true;
    $message = $isCorrect ? "✅ Correct!" : "❌ Incorrect! Correct Answer: <strong>{$question['CorrectOption']}</strong>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($quiz['Title']); ?> | Start Quiz</title>
    <style>
        body {
            background-color: #121212;
            color: #30c6f4;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }
        .container {
            background-color: #1F1F1F;
            padding: 25px;
            border-radius: 12px;
            width: 600px;
            margin: auto;
            box-shadow: 0 0 20px #000;
        }
        h2, h4 {
            color: #30c6f4;
        }
        .message {
            color: yellowgreen;
            font-weight: bold;
            margin: 15px 0;
        }
        label {
            display: block;
            margin: 10px 0;
            cursor: pointer;
        }
        input[type="radio"] {
            margin-right: 8px;
        }
        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 25px;
            border: none;
            background-color: #30c6f4;
            color: #121212;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
        .answer {
            font-size: 1.1em;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><?php echo htmlspecialchars($quiz['Title']); ?></h2>
    <h4><?php echo htmlspecialchars($quiz['Description']); ?></h4>
    <p><strong>Subject:</strong> <?php echo $quiz['SubjectName']; ?></p>
    <p><strong>One-Time Attempt:</strong> <?php echo $quiz['OneTime'] ? "Yes" : "No"; ?></p>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!$submitted): ?>
        <form method="POST">
            <h3><?php echo htmlspecialchars($question['QuestionText']); ?></h3>
            <input type="hidden" name="question_id" value="<?php echo $question['Id']; ?>">

            <label><input type="radio" name="answer" value="<?php echo htmlspecialchars($question['OptionA']); ?>" required> <?php echo htmlspecialchars($question['OptionA']); ?></label>
            <label><input type="radio" name="answer" value="<?php echo htmlspecialchars($question['OptionB']); ?>"> <?php echo htmlspecialchars($question['OptionB']); ?></label>
            <label><input type="radio" name="answer" value="<?php echo htmlspecialchars($question['OptionC']); ?>"> <?php echo htmlspecialchars($question['OptionC']); ?></label>
            <label><input type="radio" name="answer" value="<?php echo htmlspecialchars($question['OptionD']); ?>"> <?php echo htmlspecialchars($question['OptionD']); ?></label>

            <input type="submit" value="Submit Answer">
        </form>
    <?php endif; ?>
</div>

</body>
</html>
