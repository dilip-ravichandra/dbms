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

// Fetch result
$resultRes = mysqli_query($conn, "SELECT * FROM QuizResults WHERE UserId = $userId AND QuizId = $quizId");
if (mysqli_num_rows($resultRes) == 0) {
    echo "You have not attempted this quiz yet.";
    exit;
}
$result = mysqli_fetch_assoc($resultRes);
$scorePercent = $result['Score'];

// Fetch answers
$answersRes = mysqli_query($conn, "SELECT qa.QuestionId, qa.SelectedOption, q.QuestionText, q.OptionA, q.OptionB, q.OptionC, q.OptionD, q.CorrectOption 
    FROM QuizAnswers qa 
    JOIN Questions q ON qa.QuestionId = q.Id 
    WHERE qa.ResultId = " . intval($result['Id'])
);
?>

<!DOCTYPE html>
<html>
<head><title>Quiz Result</title></head>
<body>
<h2>Quiz Result</h2>
<p>Your score: <?php echo $scorePercent; ?>%</p>
<h3>Your Answers:</h3>
<?php
while ($row = mysqli_fetch_assoc($answersRes)) {
    echo "<div style='margin-bottom:20px;'>";
    echo "<strong>" . htmlspecialchars($row['QuestionText']) . "</strong><br>";
    $options = ['A' => 'OptionA', 'B' => 'OptionB', 'C' => 'OptionC', 'D' => 'OptionD'];
    foreach ($options as $opt => $col) {
        $style = "";
        if ($opt == $row['CorrectOption']) {
            $style = "color: green; font-weight: bold;";
        }
        if ($opt == $row['SelectedOption'] && $row['SelectedOption'] != $row['CorrectOption']) {
            $style = "color: red; font-weight: bold;";
        }
        echo "<div style='$style'>$opt: " . htmlspecialchars($row[$col]) . "</div>";
    }
    echo "</div>";
}
?>
<p><a href="student_dashboard.php">Back to Dashboard</a></p>
</body>
</html>
