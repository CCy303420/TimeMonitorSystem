<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'monitoring_system');
$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

// Fetch the latest login session for the user
$query = $conn->query("SELECT id, login_time, logout_time FROM logs WHERE user_id = '$user_id' ORDER BY login_time DESC LIMIT 1");
$latest_log = $query->fetch_assoc();
$login_time = $latest_log ? strtotime($latest_log['login_time']) : null;

// Fetch all login history for the current user
$history_result = $conn->query("SELECT id, login_time, logout_time FROM logs WHERE user_id = '$user_id' ORDER BY login_time DESC");

// Fetch admin message for the user
$message_query = $conn->query("SELECT message FROM users WHERE id = '$user_id'");
$message_result = $message_query->fetch_assoc();
$user_message = $message_result ? $message_result['message'] : '';


// Logout logic
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    if ($latest_log && !$latest_log['logout_time']) {
        // Update logout time only if it hasn't been set
        $latest_log_id = $latest_log['id'];
        $conn->query("UPDATE logs SET logout_time = NOW() WHERE id = '$latest_log_id' AND logout_time IS NULL");
    }

    session_destroy();
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Logout</title>
    <style>
        /* General Styling */
        body {
            margin: 0;
            padding: 0;
            background-color: #19c8c8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
        }

        .box {
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            text-align: center;
            border-radius: 10px;
        }

        .logout-box button {
            width: 45%;
            padding: 15px;
            margin: 10px;
            background-color: #336eff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .logout-box button:hover {
            background-color: #0736ab;
            transform: scale(1.05);
        }

        .timer {
            font-size: 24px;
            margin-bottom: 15px;
            color: black;
        }

        #history-container {
            display: none; /* Hidden by default */
            max-height: 200px;
            overflow-y: auto; 
            overflow-x: auto;
            border: 1px solid #ccc; 
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }

        #login-history-table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            font-size: 14px;
        }

        tr {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) {
            background-color: #f1f1f1;
        }

        .hide-button {
            background-color: #28a745;
            color: white;
            padding: 10px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .hide-button:hover {
            background-color: #218838;
        }

        h1 {
            width: 100%; /* Full width */
            text-align: center; /* Center text */
            margin-top: 10px; /* Adjust positioning */
            position: absolute;
            top: 50px; /* Fix at the top */
        }
    </style>
	
	<script>
    let elapsedTime = 0;
    let timer;

    // Display the admin message as an alert
    function displayAdminMessage(message) {
        if (message) {
            alert(`Message from Admin: ${message}`);
        }
    }

    // Function to update the timer display
    function updateTimerDisplay() {
        elapsedTime++;
        const hours = Math.floor(elapsedTime / 3600);
        const minutes = Math.floor((elapsedTime % 3600) / 60);
        const seconds = elapsedTime % 60;

        document.getElementById("timer").innerText = `${hours}hrs ${minutes}min ${seconds}s`;
    }

    // Toggle the visibility of the login history table
    function toggleHistory() {
        const table = document.getElementById("history-container");
        const button = document.getElementById("hide-button");
        if (table.style.display === "none") {
            table.style.display = "block";
            button.innerText = "Hide Login History";
        } else {
            table.style.display = "none";
            button.innerText = "View Login History";
        }
    }

    // Start the timer when the page loads
    window.onload = () => {
        elapsedTime = 0;
        timer = setInterval(updateTimerDisplay, 1000);
        displayAdminMessage("<?= htmlspecialchars($user_message) ?>");

        // Alert a message after 10 seconds
        setTimeout(() => {
            alert("2 hours have passed! Please take a break.");
        }, 10000); // 7,200,000 milliseconds = 2 hours
    };
</script>
</head>
<body>
<h1>Time Tracker System</h1><br><br>
    <div class="box">
        <div class="logout-box">
            <h2>Your session is being recorded.</h2>
            <p>Login Time: <?= date('Y-m-d H:i:s', $login_time) ?></p>
            <p id="timer" class="timer">0hrs 0min 0s</p>

            <!-- Logout Button -->
            <form method="POST" style="display:inline;">
                <button type="submit" name="logout">Logout</button>
            </form>

            <!-- Toggle History Button -->
            <button id="hide-button" onclick="toggleHistory()">View Login History</button>
        </div>

        <!-- Scrollable Login History Table -->
        <div id="history-container">
            <table id="login-history-table">
                <thead>
                    <tr>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $history_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['login_time'] ?></td>
                            <td>
                                <?php
									if (!$row['logout_time'] && strtotime($row['login_time']) === $login_time) {
										echo '<span style="color: green; font-weight: bold;">Currently In Session</span>';
									} elseif (!$row['logout_time']) {
										echo '<span style="color: red; font-weight: bold;">Session Interrupted</span>';
									} else {
										echo $row['logout_time'];
									}
								?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
