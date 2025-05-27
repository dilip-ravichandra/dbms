<?php
session_start();
include("db_connection.php");

$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($password)) {
        $alert = "⚠️ Please fill in all fields.";
    } else {
        $result = mysqli_query($conn, "SELECT * FROM Users WHERE Name = '$name' LIMIT 1");
        $user = mysqli_fetch_assoc($result);

        if ($user && $password === $user['Password']) {
            $_SESSION['user_id'] = $user['Id'];
            $_SESSION['user_name'] = $user['Name'];
            $_SESSION['role'] = $user['Role'];

            if ($user['Role'] == "student") {
                header("Location: student/dashboard.php");
            } else {
                header("Location: teacher/dashboard.php");
            }
            exit;
        } else {
            $alert = "❌ Invalid name or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - RV Flix</title>
    <style>
        body {
            background-color: #1C1C1C;
            color:rgb(56, 194, 245);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #2A2A2A;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(29, 246, 246, 0.4);
            width: 420px;
        }
        h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 20px;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background-color: #333;
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 14px;
        }
        button {
            background-color:rgb(56, 194, 245);
            color: #1C1C1C;
            font-weight: bold;
            cursor: pointer;
        }
        .alert {
            background-color: #FF4D4D;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        .success {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        .register{
            text-align:center;
            font-size:18px;
        }
        .register a{
            color: #fb8500;
            text-decoration:none;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Login to RV Flix</h2>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">✅ Registration successful. Please log in.</div>
    <?php endif; ?>

    <?php if (!empty($alert)): ?>
        <div class="alert"><?php echo $alert; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Enter Your Name" required>
        <input type="password" name="password" placeholder="Enter Your Password" required>
        <button type="submit">Login</button>
        <div class="register">
            <a href="register.php"><p>Do you want to register?</p><a>
    </form>
</div>
</body>
</html>
