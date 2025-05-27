<?php
session_start();
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    echo json_encode([
        "username" => $_SESSION['username'],
        "role" => $_SESSION['role']
    ]);
} else {
    echo json_encode([
        "error" => "Not logged in"
    ]);
}
?>
