<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "VideoStreaming";
$port = 4306;

$conn = mysqli_connect($host, $user, $password, $database, $port);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
