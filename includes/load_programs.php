<?php
include("db_connection.php");

function getPrograms($conn) {
    $result = mysqli_query($conn, "SELECT * FROM Programs");
    $programs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $programs[] = $row;
    }
    return $programs;
}
?>
