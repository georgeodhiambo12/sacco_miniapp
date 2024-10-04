<?php
session_start(); 
$servername = "localhost";
$username = "tenderso_home";
$password = "QMPch6bU7}7W";
$dbname = "tenderso_HOME";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_username = $_POST['username'];  
    $input_password = $_POST['password'];

    // Fetch user by username and password from the members table (note: avoid SQL injection by using prepared statements in production)
    $sql = "SELECT * FROM members WHERE username = '$input_username' AND password = '$input_password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Store the member_id, username, and role in the session
        $_SESSION['user_id'] = $row['member_id'];  // Store the member_id
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role']; // Store the role

        // Check if the username is 'admin'
        if ($row['username'] == 'admin') {
            // Redirect to admin page
            header("Location: view_all_payments.php");
        } else {
            // Redirect to user page
            $_SESSION['role'] = 'user'; // Ensure role is set to 'user' for other users
            header("Location: view_individual_payments.php");
        }
        exit();  // Ensure no further code runs after redirection
    } else {
        $error_message = "Invalid login credentials.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            text-align: left;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
        }

        button {
            padding: 10px 20px; /* Reduced padding */
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            width: auto; /* Auto width to match the text size */
            align-self: center; /* Align the button to the center */
        }

        button:hover {
            background-color: #0056b3;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .link {
            margin-top: 15px;
            font-size: 14px;
        }

        .link a {
            color: #007bff;
            text-decoration: none;
        }

        .link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>

        <!-- Display error message if any -->
        <?php
        if ($error_message != "") {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>

        <!-- Login form -->
        <form action="login.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
