<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_delete'])) {
    // Delete user
    $deleteQuery = mysqli_query($conn, "DELETE FROM Users WHERE Id = $userId");

    // Clear session
    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Delete Account - RV Flix</title>
  <style>
    body {
      margin: 0;
      background-color: #121212;
      color: rgb(48, 198, 244);
      font-family: 'Segoe UI', sans-serif;
      text-align: center;
      padding-top: 100px;
    }
    .container {
      background-color: #1E1E1E;
      border-radius: 12px;
      padding: 40px;
      width: 400px;
      margin: auto;
      box-shadow: 0 0 12px rgba(48, 198, 244, 0.3);
    }
    button {
      padding: 12px 24px;
      margin: 20px 10px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
    }
    .confirm {
      background-color: red;
      color: white;
    }
    .cancel {
      background-color: rgb(48, 198, 244);
      color: black;
    }
    a {
      color: rgb(48, 198, 244);
      text-decoration: none;
    }
  </style>
</head>
<body>

<div class="container">
  <h2>Are you sure you want to delete your account?</h2>
  <form method="POST">
    <button type="submit" name="confirm_delete" class="confirm">Yes, Delete My Account</button>
    <a href="<?php echo ($role === 'student') ? 'student/dashboard.php' : 'teacher_dashboard.php'; ?>">
      <button type="button" class="cancel">Cancel</button>
    </a>
  </form>
</div>

</body>
</html>
