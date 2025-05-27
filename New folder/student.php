<?php
$conn = new mysqli("localhost", "root", "", "rvflix", 4306);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

$result = $conn->query("SELECT filename FROM videos ORDER BY id DESC");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $filename = htmlspecialchars($row['filename']);
        echo '<div class="video-box">';
        echo '<video controls>';
        echo '<source src="uploads/' . $filename . '" type="video/mp4">';
        echo 'Your browser does not support the video tag.';
        echo '</video>';
        echo '</div>';
    }
} else {
    echo '<div class="video-box">No videos uploaded yet.</div>';
}

$conn->close();
?>
