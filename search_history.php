<?php
session_start();
include("db_connection.php");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Fetch search history
$history = [];
$query = mysqli_query($conn, "
    SELECT SearchQuery, SearchDate
    FROM SearchHistory
    WHERE UserId = $userId
    ORDER BY SearchDate DESC
    LIMIT 20
");
while ($row = mysqli_fetch_assoc($query)) {
    $history[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Search History - RV Flix</title>
  <style>
    body {
      margin: 0;
      background-color: #121212;
      color: rgb(48, 198, 244);
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background-color: #1F1F1F;
      padding: 20px 40px;
      display: flex;
      gap:30px;
      justify-content: flex-end;
      align-items: center;
    }
    .avatar {
      background-color: rgb(48, 198, 244);
      color: #1C1C1C;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }
    .menu {
      position: relative;
    }
    .menu-button {
      background: none;
      border: none;
      font-size: 24px;
      color: white;
      cursor: pointer;
    }
    .dropdown {
      display: none;
      position: absolute;
      right: 0;
      top: 45px;
      background-color: #2A2A2A;
      border-radius: 6px;
      overflow: hidden;
    }
    .dropdown a {
      display: block;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
    }
    .dropdown a:hover {
      background-color: #3A3A3A;
    }
    .menu:hover .dropdown {
      display: block;
    }
    .container {
      padding: 40px;
    }
    .history-card {
      background-color: #1E1E1E;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(48, 198, 244, 0.4);
      margin-top: 20px;
    }
    .history-card ul {
      list-style-type: none;
      padding: 0;
    }
    .history-card li {
      margin-bottom: 12px;
      border-bottom: 1px solid #333;
      padding-bottom: 6px;
    }
    .history-card span.date {
      color: #aaa;
      font-size: 12px;
      margin-left: 10px;
    }
  </style>
</head>
<body>

<div class="navbar">
  <div>Welcome, <?php echo $userName; ?> (Student)</div>
  <div class="avatar"><?php echo strtoupper($userName[0]); ?></div>
  <div class="menu">
    <button class="menu-button">⋮</button>
    <div class="dropdown">
      <a href="search.php">Search</a>
      <a href="delete_account.php">Delete Account</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</div>

<div class="container">
  <h2>📜 Your Search History</h2>
  <div class="history-card">
    <?php if (count($history) === 0): ?>
      <p>No recent searches found.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($history as $entry): ?>
          <li>
            <?php echo htmlspecialchars($entry['SearchQuery']); ?>
            <span class="date">(<?php echo $entry['SearchDate']; ?>)</span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
