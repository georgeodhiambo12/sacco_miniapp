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

// Check if the user is logged in by checking the session
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Fetch user details (name)
$member_id = $_SESSION['user_id'];
$sql_user = "SELECT member_name FROM members WHERE member_id = '$member_id'";
$result_user = $conn->query($sql_user);
$user = $result_user->fetch_assoc();
$member_name = $user['member_name'];

// Handle date filter from the form
$filter_query = "";
if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $filter_query = "AND p.date BETWEEN '$start_date' AND '$end_date'";
}

// Fetch payment data for the logged-in user, including any filters
$sql = "SELECT m.member_name, p.date, p.pay_date, p.time, p.period_week, p.amount, p.fine 
        FROM payments p 
        JOIN members m ON p.member_id = m.member_id
        WHERE p.member_id = '$member_id' $filter_query
        ORDER BY p.period_week";
$result = $conn->query($sql);

// Variables to calculate the total amounts and fines
$total_amount = 0;
$total_fine = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $member_name; ?>'s Contributions</title>
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

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .filter-form {
            margin-bottom: 20px;
            text-align: center;
        }

        .filter-form input[type="date"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
        }

        .filter-form button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .no-records {
            text-align: center;
            margin-top: 20px;
            color: red;
        }

        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <!-- Top bar with page title and Logout button -->
    <div class="top-bar">
        <h1><?php echo $member_name; ?>'s Contributions</h1>
        <!-- Logout button -->
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Filter Form for Date Range -->
    <div class="filter-form">
        <form method="post" action="view_individual_payments.php">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
            <button type="submit">Filter</button>
        </form>
    </div>

    <!-- Display Payment Records -->
    <?php
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr>
                <th>Member Name</th>
                <th>Date</th>
                <th>Pay Date</th>
                <th>Time</th>
                <th>Period (Week)</th>
                <th>Amount</th>
                <th>Fine</th>
                <th>Total</th>
              </tr>";
        
        while($row = $result->fetch_assoc()) {
            $total = $row['amount'] + $row['fine'];
            $total_amount += $row['amount'];
            $total_fine += $row['fine'];

            echo "<tr>
                    <td>" . $row['member_name'] . "</td>
                    <td>" . $row['date'] . "</td>
                    <td>" . $row['pay_date'] . "</td>
                    <td>" . $row['time'] . "</td>
                    <td>" . $row['period_week'] . "</td>
                    <td>" . number_format($row['amount'], 2) . "</td>
                    <td>" . number_format($row['fine'], 2) . "</td>
                    <td>" . number_format($total, 2) . "</td>
                  </tr>";
        }

        // Display total row
        $total_all = $total_amount + $total_fine;
        echo "<tr class='total-row'>
                <td colspan='5'>Total</td>
                <td>" . number_format($total_amount, 2) . "</td>
                <td>" . number_format($total_fine, 2) . "</td>
                <td>" . number_format($total_all, 2) . "</td>
              </tr>";

        echo "</table>";
    } else {
        echo "<p class='no-records'>No payment records found.</p>";
    }
    ?>

    <?php $conn->close(); ?>
</body>
</html>
