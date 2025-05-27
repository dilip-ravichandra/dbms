<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'teacher') {
    header('Location: login.html');
    exit();
}
?>

<?php include 'teacher.html'; ?>
