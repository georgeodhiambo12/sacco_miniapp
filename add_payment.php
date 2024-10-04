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

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access! Only admins can add payments.");
}

// Handle form submission for adding a payment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $member_id = $_POST['member_id'];  // Now we're using the member_id
    $date = $_POST['date'];            // Date entered by the admin
    $pay_date = $_POST['pay_date'];    // Pay Date entered by the admin
    $time = $_POST['time'];            // Time entered by the admin
    $period_week = $_POST['period_week']; // Period Week entered by the admin
    $amount = $_POST['amount'];        // Amount entered by the admin
    $fine = $_POST['fine'];            // Fine entered by the admin (default 0)

    // Insert payment data into the payments table
    $sql_insert = "INSERT INTO payments (member_id, date, pay_date, time, period_week, amount, fine) 
                   VALUES ('$member_id', '$date', '$pay_date', '$time', '$period_week', '$amount', '$fine')";
    
    if ($conn->query($sql_insert) === TRUE) {
        echo "Payment added successfully!";
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }
}

// Fetch all members for the dropdown
$members_sql = "SELECT member_id, member_name FROM members";
$members_result = $conn->query($members_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: auto;
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-btn {
            background-color: #6c757d;
            margin-top: 20px;
        }

        .back-btn:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <h1>Add Payment</h1>

    <form action="add_payment.php" method="POST">
        <label for="member_id">Member:</label>
        <select name="member_id" required>
            <?php
            if ($members_result->num_rows > 0) {
                while($row = $members_result->fetch_assoc()) {
                    // Display member_name but the value will be member_id
                    echo "<option value='" . $row['member_id'] . "'>" . $row['member_name'] . "</option>";
                }
            } else {
                echo "<option disabled>No members found</option>";
            }
            ?>
        </select>

        <label for="date">Date:</label>
        <input type="date" name="date" required> <!-- Date field added -->

        <label for="pay_date">Pay Date:</label>
        <input type="date" name="pay_date" required>

        <label for="time">Time:</label>
        <input type="time" name="time" required>

        <label for="period_week">Period (Week):</label>
        <input type="number" name="period_week" min="1" required>

        <label for="amount">Amount:</label>
        <input type="number" name="amount" min="0" required>

        <label for="fine">Fine (if any):</label>
        <input type="number" name="fine" min="0" value="0" required>

        <button type="submit">Add Payment</button>
    </form>

    <!-- Back button to redirect to view_all_payments.php -->
    <form action="view_all_payments.php" method="GET">
        <button class="back-btn">Back to Payments</button>
    </form>

    <?php $conn->close(); ?>
</body>
</html>
