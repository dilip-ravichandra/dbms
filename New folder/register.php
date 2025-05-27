<?php
$servername = "localhost";
$db_username = "root";
$db_password = "";
$database = "rvflix";
$port = 4306;

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from form
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Insert into database
$sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";

if ($conn->query($sql) === TRUE) {
    echo "<h2 style='color:lime;'>Registration Successful!</h2>";
    echo "<a href='login.html'>Go to Login</a>";
} else {
    echo "<h2 style='color:red;'>Error: " . $conn->error . "</h2>";
    echo "<a href='register.html'>Try Again</a>";
}

$conn->close();
?>
