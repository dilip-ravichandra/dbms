<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$quizId = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$answers = isset($_POST['answers']) ? $_POST['answers'] : [];

if (!$quizId || empty($answers)) {
    echo "Invalid submission.";
    exit;
}

// Check if already attempted
$checkAttempt = mysqli_query($conn, "SELECT * FROM QuizResults WHERE UserId = $userId AND QuizId = $quizId");
if (mysqli_num_rows($checkAttempt) > 0) {
    echo "You have already submitted this quiz. <a href='view_result.php?quiz_id=$quizId'>View your result</a>";
    exit;
}

// Fetch correct answers
$questionsRes = mysqli_query($conn, "SELECT Id, CorrectOption FROM Questions WHERE QuizId = $quizId");
$correctAnswers = [];
while ($row = mysqli_fetch_assoc($questionsRes)) {
    $correctAnswers[$row['Id']] = $row['CorrectOption'];
}

// Calculate score
$totalQuestions = count($correctAnswers);
$correctCount = 0;
foreach ($answers as $qId => $selected) {
    if (isset($correctAnswers[$qId]) && $correctAnswers[$qId] === $selected) {
        $correctCount++;
    }
}

$scorePercent = round(($correctCount / $totalQuestions) * 100);

// Insert result
$now = date('Y-m-d H:i:s');
mysqli_query($conn, "INSERT INTO QuizResults (UserId, QuizId, Score, SubmittedAt) VALUES ($userId, $quizId, $scorePercent, '$now')");
$resultId = mysqli_insert_id($conn);

// Insert answers
foreach ($answers as $qId => $selected) {
    $qId = intval($qId);
    $selected = mysqli_real_escape_string($conn, $selected);
    mysqli_query($conn, "INSERT INTO QuizAnswers (ResultId, QuestionId, SelectedOption) VALUES ($resultId, $qId, '$selected')");
}

// Show results and answers

echo "<h2>Quiz Completed</h2>";
echo "<p>Your score: $correctCount / $totalQuestions ($scorePercent%)</p>";

echo "<h3>Review your answers:</h3>";

foreach ($correctAnswers as $qId => $correctOption) {
    $questionRes = mysqli_query($conn, "SELECT QuestionText, OptionA, OptionB, OptionC, OptionD FROM Questions WHERE Id = $qId");
    $question = mysqli_fetch_assoc($questionRes);
    $userAnswer = isset($answers[$qId]) ? $answers[$qId] : 'No Answer';

    echo "<div style='margin-bottom:20px;'>";
    echo "<strong>" . htmlspecialchars($question['QuestionText']) . "</strong><br>";
    $options = ['A' => 'OptionA', 'B' => 'OptionB', 'C' => 'OptionC', 'D' => 'OptionD'];
    foreach ($options as $opt => $col) {
        $style = "";
        if ($opt == $correctOption) {
            $style = "color: green; font-weight: bold;";
        }
        if ($opt == $userAnswer && $userAnswer != $correctOption) {
            $style = "color: red; font-weight: bold;";
        }
        echo "<div style='$style'>$opt: " . htmlspecialchars($question[$col]) . "</div>";
    }
    echo "</div>";
}

echo "<p><a href='student_dashboard.php'>Back to Dashboard</a></p>";
?>
