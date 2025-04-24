<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'monitoring_system');

// Check if the user has admin access
if ($_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit;
}

// Handle various actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_user'])) {
        $username = $conn->real_escape_string(trim($_POST['username']));
        $password = md5($conn->real_escape_string(trim($_POST['password'])));
        if (!empty($username) && !empty($password)) {
            $sql = "INSERT INTO users (username, password, role, banned) VALUES ('$username', '$password', 'user', 0)";
            if ($conn->query($sql) === TRUE) {
                echo "<p style='color: green;'>New user added successfully.</p>";
            } else {
                echo "<p style='color: red;'>Error adding user: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Username or password cannot be empty.</p>";
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        if ($user_id > 0) {
            $conn->query("DELETE FROM logs WHERE user_id = $user_id");
            $sql = "DELETE FROM users WHERE id = $user_id";
            if ($conn->query($sql) === TRUE) {
                echo "<p style='color: green;'>User and their logs deleted successfully.</p>";
            } else {
                echo "<p style='color: red;'>Error deleting user: " . $conn->error . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Invalid user ID.</p>";
        }
    } elseif (isset($_POST['toggle_ban'])) { // Handle banning/unbanning
        $user_id = intval($_POST['user_id']);
        $current_status = intval($_POST['current_status']);
        $new_status = $current_status ? 0 : 1; // Toggle status
        $conn->query("UPDATE users SET banned = $new_status WHERE id = $user_id");
        echo $new_status ? "<p style='color: red;'>User has been banned successfully.</p>" :
            "<p style='color: green;'>User has been unbanned successfully.</p>";
    } elseif (isset($_POST['update_admin'])) { // Update admin username and password
        $current_password = md5($conn->real_escape_string(trim($_POST['current_password'])));
        $new_username = $conn->real_escape_string(trim($_POST['new_username']));
        $new_password = md5($conn->real_escape_string(trim($_POST['new_password'])));
        $admin_id = intval($_SESSION['user_id']); // Use the current admin's ID

        // Fetch the current password from the database
        $result = $conn->query("SELECT password FROM users WHERE id = $admin_id");
        $admin = $result->fetch_assoc();

        if ($admin && $admin['password'] === $current_password) { // Check if the current password matches
            if (!empty($new_username) && !empty($new_password)) {
                $sql = "UPDATE users SET username = '$new_username', password = '$new_password' WHERE id = $admin_id";
                if ($conn->query($sql) === TRUE) {
                    echo "<p style='color: green; font-weight: bold;'>Admin credentials updated successfully.</p>";
                    $_SESSION['username'] = $new_username; // Update the session
                } else {
                    echo "<p style='color: red; font-weight: bold;'>Error updating credentials: " . $conn->error . "</p>";
                }
            } else {
                echo "<p style='color: red; font-weight: bold;'>New username or password cannot be empty.</p>";
            }
        } else {
            echo "<p style='color: red; font-weight: bold;'>Current password is incorrect or being use.</p>";
        }
    }
}

$users = $conn->query("SELECT * FROM users WHERE role = 'user'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
	<style> 
	body { 
	margin: 0; padding: 0; 
	background-color: #19c8c8; 
	justify-content: center; 
	align-items: center; 
	display: flex; 
	flex-direction: column; 
	height: 100vh; 
	font-family: Arial, sans-serif; 
	} 
	
	.box { 
	background-color: #7de1e1; 
	padding: 20px; 
	border: 1px solid #ccc; 
	box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
	width: fit-content; 
	margin: 20px auto; border-radius: 10px; 
	}  
	
	.table-box { 
	background-color: white; 
	padding: 20px; 
	border: 1px solid #ccc; 
	box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
	width: fit-content; 
	margin: 20px auto; border-radius: 10px; 
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
	
	
	.ban-button { 
	padding: 8px 12px; 
	background-color: #ff4d4d; /* Red button for 'Ban' */ 
	color: white; border: none; 
	border-radius: 5px; cursor: pointer; 
	} 
	
	.ban-button:hover { 
	background-color: #d63333; /* Darker red on hover */ 
	} 
	
	.unban-button { 
	padding: 8px 12px; 
	background-color: #28a745; /* Green button for 'Unban' */ 
	color: white; border: none; 
	border-radius: 5px; 
	cursor: pointer; 
	} 
	
	.unban-button:hover { 
	background-color: #218838; /* Darker green on hover */ 
	}
	
	button { 
	padding: 8px 12px; 
	background-color: #336eff; 
	color: white; 
	border: none; 
	border-radius: 5px; 
	cursor: pointer; 
	} 
	
	button:hover { 
	background-color: #0736ab; 
	} 
	
	h2, h3 { 
	color: black; 
	} 
	
	a { 
	text-decoration: none; color: #007bff; 
	font-weight: bold; 
	} 
	
	.logout-box { 
	background-color: white;
	padding: 10px; 
	border: 1px solid #ccc; 
	box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); 
	width: fit-content; 
	margin: 20px auto; 
	border-radius: 8px; 
	}
	
	</style>
</head>
<body>
    <h1><b><u>Admin Dashboard</u></b></h1>
	<div class="box">
        <h3>Change Admin Username and Password</h3>
        <form method="POST">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="text" name="new_username" placeholder="New Username" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <button type="submit" name="update_admin">Update</button>
        </form>
    </div>
	
    <div class="table-box">
	<!-- Add User Section -->
    <form method="POST">
        <h2>User Accounts</h2>
        Username: <input type="text" name="username" required>
        Password: <input type="password" name="password" required>
        <button type="submit" name="add_user">Add User</button>
    </form>
        <table>
            <tr>
                <th>Username</th>
                <th>Ban Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($user = $users->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= $user['banned'] ? 'Yes' : 'No' ?></td>
                <td>
                    <!-- Ban/Unban Button -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= intval($user['id']) ?>">
                        <input type="hidden" name="current_status" value="<?= intval($user['banned']) ?>">
                        <button type="submit" name="toggle_ban" class="<?= $user['banned'] ? 'unban-button' : 'ban-button' ?>">
                            <?= $user['banned'] ? 'Unban' : 'Ban' ?>
                        </button>
                    </form>

                    <!-- Delete Button -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= intval($user['id']) ?>">
                        <button type="submit" name="delete_user">Delete</button>
                    </form>

                    <!-- View Logs Button -->
                    <form method="GET" action="admin_logs.php" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?= intval($user['id']) ?>">
                        <button type="submit">View</button>
                    </form>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

    <!-- Logout Link -->
    <div class="logout-box">
        <a href="logout_admin.php">Logout</a>
    </div>
</body>
</html>