<?php
// insert_user.php

$servername = "localhost";
$username = "root";
$password = "";
$database = "rvflix";
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sample user data
$user_username = "dilip";
$user_password = "123456"; // normally you should hash passwords!
$user_email = "dilip@example.com";
$user_mobile = 9876543210;
$user_country = "India";

// SQL Insert Query
$sql = "INSERT INTO users (username, password, email_address, mobile, country)
VALUES ('$user_username', '$user_password', '$user_email', '$user_mobile', '$user_country')";

if ($conn->query($sql) === TRUE) {
    echo "New user inserted successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>
