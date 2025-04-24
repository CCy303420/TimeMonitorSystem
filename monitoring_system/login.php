<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'monitoring_system');

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    // Query to check user credentials
    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if the user is banned
        if ($user['banned'] == true) {
            // Display "banned" message
            echo "
            <div style='background-color: #f8d7da; 
                        color: #721c24; 
                        justify-content: center; 
                        align-items: center; 
                        text-align: center; 
                        padding: 200px; 
                        margin-top: 100px; 
                        font-size: 25px;'>
                Your account has been banned.<br><br>
                <a href='login.html' style='color: #336eff;'>Return to Login Page</a>
            </div>";
        } else {
            // Proceed with login for non-banned users
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin_home.php");
            } else {
                $conn->query("INSERT INTO logs (user_id, login_time) VALUES ('{$user['id']}', NOW())");
                $_SESSION['log_id'] = $conn->insert_id;
                header("Location: user_logout.php");
            }
        }
    } else {
        // Display invalid login message
        echo "
        <div style='background-color: #f8d7da; 
                    color: #721c24; 
                    justify-content: center; 
                    align-items: center; 
                    text-align: center; 
                    padding: 200px; 
                    margin-top: 100px; 
                    font-size: 25px;'>
            Invalid Username or Password.<br><br>
            <a href='login.html' style='color: #336eff;'>Return to Login Page</a>
        </div>";
    }
}
?>
<!DOCTYPE html>
<html>
<style>
        body {
            margin: 0;
            padding: 0;
            background-color: #19c8c8; /* Light blue background */
    }
</style>
</html>
