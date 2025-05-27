<?php
include("db_connection.php");

// Load programs from DB
$programs = [];
$result = mysqli_query($conn, "SELECT Id, Name FROM Programs");
while ($row = mysqli_fetch_assoc($result)) {
    $programs[] = $row;
}

$alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $mobile   = trim($_POST['mobile']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];
    $programId = $_POST['program'];

    if (empty($name) || empty($mobile) || empty($password) || empty($role) || empty($programId)) {
        $alert = "⚠️ Please fill in all fields.";
    } else {
        $check = mysqli_query($conn, "SELECT * FROM Users WHERE Mobile = '$mobile'");
        if (mysqli_num_rows($check) > 0) {
            $alert = "❌ A user with this mobile number already exists.";
        } else {
            $plain_password = $password;
            $insert = mysqli_query($conn, "
                INSERT INTO Users (Name, Mobile, Password, Role, ProgramId, Active, LastUpdatedDate)
                VALUES ('$name', '$mobile', '$plain_password', '$role', $programId, 1, NOW())");

            if ($insert) {
                header("Location: login.php?success=1");
                exit;
            } else {
                $alert = "❌ Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - RV Flix</title>
    <style>
        body {
            background-color: #1C1C1C;
            color:rgb(48, 202, 244);
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
            box-shadow: 0 0 15px rgb(56, 194, 245);
            width: 420px;
        }
        h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 20px;
        }
        input, select, button {
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
            background-color:rgb(48, 208, 244);
            color: #1C1C1C;
            font-weight: bold;
            cursor: pointer;
        }
        .alert {
            background-color:rgb(152, 50, 50);
            color: white;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
        }
        .login{
            color: #fb8500;
            font-size: 18px;
            text-align:center;

        }
        .login a{
            color: #fb8500;
            text-decoration:none;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Register on RV Flix</h2>

    <?php if (!empty($alert)): ?>
        <div class="alert"><?php echo $alert; ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="mobile" placeholder="Mobile Number" required>
        <input type="password" name="password" placeholder="Password" required>

        <select name="role" required>
            <option value="">-- Select Role --</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
        </select>

        <select name="program" required>
            <option value="">-- Select Program --</option>
            <?php foreach ($programs as $program): ?>
                <option value="<?php echo $program['Id']; ?>"><?php echo $program['Name']; ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Register</button>
        <div class="login"><a href="login.php"><p>Do u alredy have a account?</p></a>
            </div>
    </form>
</div>
</body>
</html>
