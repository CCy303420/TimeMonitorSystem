<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'monitoring_system');
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

// Fetch login history for the current user
$result = $conn->query("SELECT login_time, logout_time FROM logs WHERE user_id = '$user_id' ORDER BY login_time DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #336eff;
            color: white;
        }
        .return-button {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            background-color: #336eff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
        }
        .return-button:hover {
            background-color: #0736ab;
        }
    </style>
</head>
<body>
    <h2>Login History</h2>
    <table>
        <tr>
            <th>Login Time</th>
            <th>Logout Time</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['login_time'] ?></td>
                <td><?= $row['logout_time'] ? $row['logout_time'] : 'Session Interrupted' ?></td>
            </tr>
        <?php } ?>
    </table>

    <!-- Button to return to logout page -->
    <a href="user_logout.php" class="return-button">Return to Logout Page</a>
</body>
</html>
