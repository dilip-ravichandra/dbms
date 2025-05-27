<?php
session_start();
include("../db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$quizId = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
if (!$quizId) {
    echo "Invalid quiz.";
    exit;
}

// Check if student already attempted
$attemptRes = mysqli_query($conn, "SELECT * FROM QuizResults WHERE UserId = $userId AND QuizId = $quizId");
if (mysqli_num_rows($attemptRes) > 0) {
    echo "You have already attempted this quiz. <a href='view_result.php?quiz_id=$quizId'>View your result</a>";
    exit;
}

// Fetch quiz questions
$questionsRes = mysqli_query($conn, "SELECT * FROM Questions WHERE QuizId = $quizId");
if (mysqli_num_rows($questionsRes) == 0) {
    echo "No questions found for this quiz.";
    exit;
}

$questions = [];
while ($row = mysqli_fetch_assoc($questionsRes)) {
    $questions[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attempt Quiz</title>
</head>
<body>
    <h2>Quiz Attempt</h2>
    <form method="post" action="submit.php">
        <input type="hidden" name="quiz_id" value="<?php echo $quizId; ?>">
        <?php foreach ($questions as $index => $q): ?>
            <div style="margin-bottom:20px;">
                <p><strong><?php echo ($index + 1) . ". " . htmlspecialchars($q['QuestionText']); ?></strong></p>
                <label><input type="radio" name="answers[<?php echo $q['Id']; ?>]" value="A" required> <?php echo htmlspecialchars($q['OptionA']); ?></label><br>
                <label><input type="radio" name="answers[<?php echo $q['Id']; ?>]" value="B"> <?php echo htmlspecialchars($q['OptionB']); ?></label><br>
                <label><input type="radio" name="answers[<?php echo $q['Id']; ?>]" value="C"> <?php echo htmlspecialchars($q['OptionC']); ?></label><br>
                <label><input type="radio" name="answers[<?php echo $q['Id']; ?>]" value="D"> <?php echo htmlspecialchars($q['OptionD']); ?></label><br>
            </div>
        <?php endforeach; ?>
        <button type="submit">Submit Quiz</button>
    </form>
</body>
</html>
