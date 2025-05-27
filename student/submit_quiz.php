<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_id = intval($_POST['quiz_id']);
    $answers = $_POST['answers'] ?? [];

    // Check if quiz exists
    $quiz_sql = "SELECT * FROM Quizzes WHERE Id = $quiz_id";
    $quiz_result = mysqli_query($conn, $quiz_sql);
    if (!$quiz_result || mysqli_num_rows($quiz_result) == 0) {
        echo "Invalid quiz.";
        exit();
    }
    $quiz = mysqli_fetch_assoc($quiz_result);

    // Check one-time attempt
    $attempt_check = mysqli_query($conn, "SELECT * FROM QuizResults WHERE QuizId = $quiz_id AND StudentId = $student_id");
    if (mysqli_num_rows($attempt_check) > 0 && $quiz['OneTime']) {
        echo "<h3>You already submitted this one-time quiz.</h3>";
        exit();
    }

    // Get all questions and correct answers
    $question_sql = "SELECT Id, correctoption FROM QuizQuestions WHERE QuizId = $quiz_id";
    $question_result = mysqli_query($conn, $question_sql);

    $total = 0;
    $correct = 0;

    while ($question = mysqli_fetch_assoc($question_result)) {
        $qid = $question['Id'];
        $correct_ans = $question['Correctoption'];
        $total++;

        if (isset($answers[$qid]) && $answers[$qid] === $correct_ans) {
            $correct++;
        }
    }

    // Store result
    $score = ($total > 0) ? round(($correct / $total) * 100) : 0;

    $insert_sql = "
        INSERT INTO QuizResults (QuizId, StudentId, Score)
        VALUES ($quiz_id, $student_id, $score)
    ";
    mysqli_query($conn, $insert_sql);

    echo "<h2>Quiz Submitted</h2>";
    echo "<p>You scored: $correct out of $total</p>";
    echo "<p>Percentage: $score%</p>";
    echo "<a href='dashboard.php'>Back to Dashboard</a>";
} else {
    echo "Invalid request.";
}
?>
