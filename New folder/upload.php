<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teacher') {
    die("Access denied.");
}

$conn = new mysqli("localhost", "root", "", "rvflix", 4306);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$allowedTypes = ['video/mp4', 'video/avi', 'video/mkv', 'video/webm', 'video/quicktime'];
$fileType = mime_content_type($_FILES["video"]["tmp_name"]);

if (!in_array($fileType, $allowedTypes)) {
    die("Error: Only video files are allowed!");
}

$filename = basename($_FILES["video"]["name"]);
$targetFile = $targetDir . $filename;

if (move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile)) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("INSERT INTO videos (filename, uploaded_by) VALUES (?, ?)");
    $stmt->bind_param("ss", $filename, $username);
    $stmt->execute();

    header("Location: teacher.php?upload=success");
    exit();
} else {
    echo "Sorry, file upload failed.";
}

$conn->close();
?>
