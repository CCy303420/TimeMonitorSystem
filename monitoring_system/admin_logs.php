<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'monitoring_system');

// Check if the user has admin access
if ($_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit;
}

// Handle updating user messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_message'])) {
    $user_id = intval($_POST['user_id']); // Sanitize input
    $message = $conn->real_escape_string(trim($_POST['message'])); // Sanitize input
    $conn->query("UPDATE users SET message = '$message' WHERE id = $user_id");
    echo "<p style='color: green; font-weight: bold;'>Message updated successfully.</p>";
}

// Handle resetting the message to null
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_message'])) {
    $user_id = intval($_POST['user_id']); // Sanitize input
    $conn->query("UPDATE users SET message = NULL WHERE id = $user_id");
    echo "<p style='color: red; font-weight: bold;'>Message deleted successfully.</p>";
}

// Handle delete log action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_selected'])) {
    if (!empty($_POST['log_ids'])) {
        $log_ids = implode(",", array_map('intval', $_POST['log_ids'])); // Sanitize IDs
        $conn->query("DELETE FROM logs WHERE id IN ($log_ids)");
        echo "<p style='color: red; font-weight: bold;'>Logs deleted successfully.</p>";
    }
}

// Get user ID from query parameters
$user_id = intval($_GET['user_id']);

// Fetch the selected user's name and message
$user_result = $conn->query("SELECT username, message FROM users WHERE id = '$user_id'");
$user_data = $user_result->num_rows > 0 ? $user_result->fetch_assoc() : ['username' => 'Unknown User', 'message' => ''];

// Fetch login logs associated with the user
$logs = $conn->query("SELECT * FROM logs WHERE user_id = '$user_id' ORDER BY login_time DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Logs</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #19c8c8; /* Light blue background */
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex; /* Use flexbox layout */
            flex-direction: row; /* Align elements side by side */
            justify-content: center; /* Center-align the elements */
            align-items: flex-start; /* Align items at the top */
            gap: 20px; /* Space between the table and textarea box */
            width: 100%; /* Full width for responsiveness */
            max-width: 1200px; /* Limit the maximum width */
        }

        .table-box {
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            flex: 1; /* Equal spacing with the input box */
            max-height: 400px; /* Set a fixed height for scrolling */
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: auto; /* Enable horizontal scrolling if needed */
            box-sizing: border-box; /* Ensure padding doesn't affect width */
            text-align: center; /* Center text */
        }

        .input-box {
            background-color: #7de1e1;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            flex: 1; /* Make both elements take equal space */
            box-sizing: border-box; /* Ensure padding doesn't affect width */
        }

        textarea {
            width: 100%; /* Ensure it fits the box */
            height: 200px; /* Adjust height */
            border-radius: 8px;
            border: 1px solid #ccc;
            padding: 10px;
            font-family: Arial, sans-serif;
            box-sizing: border-box; /* Ensure it fits within the container */
            resize: none; /* Prevent resizing */
        }
	
	table { 
	width: 100%; 
	border-collapse: collapse; 
	margin-top: 10px; 
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

        /* Styling for buttons */
        .delete-button {
            background-color: #ff4d4d; /* Red background for delete button */
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .delete-button:hover {
            background-color: #d63333; /* Darker red on hover */
        }

        .btn-white-blue {
            background-color: #fff;
            color: #007bff;
            border: 1px solid #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
            font-family: Arial, sans-serif;
            font-weight: bold;
        }

        .btn-white-blue:hover {
            background-color: #007bff;
            color: #fff;
        }

        .btn-green-white {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-left: 10px;
            font-family: Arial, sans-serif;
            font-weight: bold;
        }

        .btn-green-white:hover {
            background-color: #218838;
        }

        .button-container {
            margin-top: 10px;
            text-align: center;
        }
		
		.save-message-button {
		background-color: #007bff; /* Blue background */
		color: white; /* White font */
		border: none;
		padding: 10px 15px;
		border-radius: 5px;
		cursor: pointer;
		font-weight: bold;
		}	

		.save-message-button:hover {
		background-color: #0056b3; /* Darker blue on hover */
		}
    </style>
    <script>
        function downloadCSV() {
            let table = document.querySelector('table');
            let csv = [];
            for (let row of table.rows) {
                let cols = Array.from(row.cells).map(cell => cell.innerText);
                csv.push(cols.join(','));
            }
            let csvString = csv.join('\n');
            let blob = new Blob([csvString], { type: 'text/csv' });
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'logs.csv';
            link.click();
        }
    </script>
</head>
<body>
    <h2>User Logs & Admin Messages</h2>

    <!-- Flexbox container for table and textarea -->
    <div class="container">
        <!-- Login Logs Table -->
        <div class="table-box">
            <h3> <u><?= htmlspecialchars($user_data['username']) ?></u><strong>'s Login Logs</strong></h3> <!-- Display the username -->
            
                    <!-- Add form to delete selected logs -->
<form method="POST" id="deleteForm">
     <table>
        <thead>
            <tr>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Select for Deletion</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs->num_rows > 0): ?>
                <?php while ($log = $logs->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $log['login_time'] ?></td>
                        <td><?= $log['logout_time'] ? $log['logout_time'] : '<span style="color: red; font-weight: bold;">Session Interrupted</span>' ?></td>
                        <td><input type="checkbox" name="log_ids[]" value="<?= intval($log['id']) ?>"></td>
                    </tr>
                <?php } ?>
            <?php else: ?>
                <tr><td colspan="3" style="text-align: center;">No Logs Found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Delete Button BELOW the Table -->
    <div style="text-align: center; margin-top: 20px;">
        <button type="submit" name="delete_selected" class="delete-button">Delete Selected</button>
    </div>
</form>
        </div>

        <!-- Message Input Section -->
        <div class="input-box">
            <h3><strong>Write a Message for <u></strong><?= htmlspecialchars($user_data['username']) ?></u></h3>
            <form method="POST">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                <textarea name="message" placeholder="Write your message here..." required><?= htmlspecialchars($user_data['message']) ?></textarea>
                <button type="submit" name="update_message" class="save-message-button" style="margin-top: 10px;">Save Message</button>
            </form>
            <!-- Delete Message Button -->
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="user_id" value="<?= $user_id ?>">
                <button type="submit" name="delete_message" class="delete-button">Delete Message</button>
            </form>
        </div>
    </div>

    <!-- Buttons for Homepage and Print -->
    <div class="button-container">
		
        <button onclick="window.location.href='admin_home.php'" class="btn-white-blue">Return To Homepage</button>
        <button onclick="downloadCSV()" class="btn-green-white">Print</button>
    </div>
</body>
</html>