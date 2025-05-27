<?php
include("db_connection.php");

function runQuery($conn, $query, $name) {
    if (mysqli_query($conn, $query)) {
        echo "✅ Table <strong>$name</strong> ready.<br>";
    } else {
        echo "❌ Error creating <strong>$name</strong>: " . mysqli_error($conn) . "<br>";
    }
}

// ========== Core Tables ==========
runQuery($conn, "
CREATE TABLE IF NOT EXISTS Programs (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL UNIQUE
)", "Programs");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS Subjects (
    Id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100),
    ProgramId INT,
    FOREIGN KEY (ProgramId) REFERENCES Programs(Id) ON DELETE CASCADE
)", "Subjects");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS Users (
    Id BIGINT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100),
    Mobile VARCHAR(15) UNIQUE,
    Password VARCHAR(255),
    Role VARCHAR(20),
    ProgramId INT,
    Active BIT,
    LastUpdatedBy BIGINT,
    LastUpdatedDate DATETIME,
    FOREIGN KEY (ProgramId) REFERENCES Programs(Id) ON DELETE SET NULL
)", "Users");

// ========== Default Data ==========

$programCheck = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Programs");
if (mysqli_fetch_assoc($programCheck)['total'] == 0) {
    mysqli_query($conn, "
        INSERT INTO Programs (Name) VALUES
        ('B.Tech'), ('B.Design'), ('B.Sc'), ('MBA')
    ");
    echo "✅ Inserted default programs.<br>";
}

function getProgramId($conn, $name) {
    $res = mysqli_query($conn, "SELECT Id FROM Programs WHERE Name = '$name' LIMIT 1");
    $row = mysqli_fetch_assoc($res);
    return $row ? $row['Id'] : null;
}

$btechId = getProgramId($conn, 'B.Tech');
$bdesignId = getProgramId($conn, 'B.Design');
$bscId = getProgramId($conn, 'B.Sc');
$mbaId = getProgramId($conn, 'MBA');

$subjectCheck = mysqli_query($conn, "SELECT COUNT(*) AS total FROM Subjects");
if (mysqli_fetch_assoc($subjectCheck)['total'] == 0) {
    mysqli_query($conn, "
        INSERT INTO Subjects (Name, ProgramId) VALUES
        ('DBMS', $btechId), ('Operating Systems', $btechId),
        ('Web Development', $btechId), ('Computer Networks', $btechId),
        ('Java Programming', $btechId), ('Software Engineering', $btechId),
        ('UI/UX Design', $bdesignId), ('Typography', $bdesignId),
        ('3D Modeling', $bdesignId), ('Design Thinking', $bdesignId),
        ('Animation Basics', $bdesignId), ('Color Theory', $bdesignId),
        ('Physics', $bscId), ('Biochemistry', $bscId),
        ('Mathematics', $bscId), ('Environmental Science', $bscId),
        ('Data Structures', $bscId), ('Statistics', $bscId),
        ('Marketing Management', $mbaId), ('Financial Accounting', $mbaId),
        ('Organizational Behavior', $mbaId), ('Business Analytics', $mbaId),
        ('Human Resources', $mbaId), ('Strategic Management', $mbaId)
    ");
    echo "✅ Inserted default subjects.<br>";
}

// ========== Video & Playlist Tables ==========

runQuery($conn, "
CREATE TABLE IF NOT EXISTS Playlists (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255),
    TeacherId BIGINT,
    SubjectId INT,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (TeacherId) REFERENCES Users(Id) ON DELETE CASCADE,
    FOREIGN KEY (SubjectId) REFERENCES Subjects(Id) ON DELETE CASCADE
)", "Playlists");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS UploadVideos (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255),
    FilePath VARCHAR(500),
    PlaylistId BIGINT,
    TeacherId BIGINT,
    SubjectId INT,
    UploadedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (PlaylistId) REFERENCES Playlists(Id) ON DELETE CASCADE,
    FOREIGN KEY (TeacherId) REFERENCES Users(Id) ON DELETE CASCADE,
    FOREIGN KEY (SubjectId) REFERENCES Subjects(Id) ON DELETE CASCADE
)", "UploadVideos");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS UserVideoWatch (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    VideoId BIGINT,
    UserId BIGINT,
    WatchedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (VideoId) REFERENCES UploadVideos(Id) ON DELETE CASCADE,
    FOREIGN KEY (UserId) REFERENCES Users(Id) ON DELETE CASCADE
)", "UserVideoWatch");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS Watchlist (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    UserId BIGINT,
    VideoId BIGINT,
    AddedDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES Users(Id) ON DELETE CASCADE,
    FOREIGN KEY (VideoId) REFERENCES UploadVideos(Id) ON DELETE CASCADE
)", "Watchlist");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS VideoWatchProgress (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    VideoId BIGINT,
    StudentId BIGINT,
    WatchPercentage FLOAT,
    FOREIGN KEY (VideoId) REFERENCES UploadVideos(Id) ON DELETE CASCADE,
    FOREIGN KEY (StudentId) REFERENCES Users(Id) ON DELETE CASCADE
)", "VideoWatchProgress");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS SearchHistory (
    Id BIGINT PRIMARY KEY AUTO_INCREMENT,
    UserId BIGINT,
    SearchQuery VARCHAR(255),
    SearchDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserId) REFERENCES Users(Id) ON DELETE CASCADE
)", "SearchHistory");

// ========== Quiz System Tables ==========

runQuery($conn, "
CREATE TABLE IF NOT EXISTS Quizzes (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255),
    SubjectId INT,
    TeacherId BIGINT,
    ProgramId INT,
    OneTime BOOLEAN DEFAULT 0,
    Description TEXT,
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (SubjectId) REFERENCES Subjects(Id) ON DELETE CASCADE,
    FOREIGN KEY (TeacherId) REFERENCES Users(Id) ON DELETE CASCADE,
    FOREIGN KEY (ProgramId) REFERENCES Programs(Id) ON DELETE CASCADE
)", "Quizzes");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS QuizQuestions (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    QuizId BIGINT,
    QuestionText TEXT,
    OptionA TEXT,
    OptionB TEXT,
    OptionC TEXT,
    OptionD TEXT,
    CorrectOPTION TEXT,
    FOREIGN KEY (QuizId) REFERENCES Quizzes(Id) ON DELETE CASCADE
)", "QuizQuestions");

runQuery($conn, "
CREATE TABLE IF NOT EXISTS QuizResults (
    Id BIGINT AUTO_INCREMENT PRIMARY KEY,
    QuizId BIGINT,
    StudentId BIGINT,
    Score FLOAT,
    AttemptedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (QuizId) REFERENCES Quizzes(Id) ON DELETE CASCADE,
    FOREIGN KEY (StudentId) REFERENCES Users(Id) ON DELETE CASCADE,
    UNIQUE(QuizId, StudentId)
)", "QuizResults");

echo "<br><strong>✅ Initialization complete with quizzes and tracking!</strong>";
?>
