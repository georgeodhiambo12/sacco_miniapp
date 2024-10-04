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
    die("Unauthorized access! Only admins can view this page.");
}

// Handle form submission for adding a new member
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_member'])) {
    $member_name = $_POST['member_name'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Insert the new member into the database
    $sql = "INSERT INTO members (member_name, phone, username, password, role) 
            VALUES ('$member_name', '$phone', '$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        echo "New member added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Handle comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_comment'])) {
    $user_id = $_POST['user_id'];  // ID of the user the comment is for
    $admin_id = $_SESSION['user_id'];  // Admin's ID from session
    $comment = $_POST['comment'];

    // Insert the comment into the comments table
    $sql = "INSERT INTO comments (user_id, admin_id, comment) VALUES ('$user_id', '$admin_id', '$comment')";
    if ($conn->query($sql) === TRUE) {
        echo "Comment added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Filter logic
$filter_name = '';
$filter_date_query = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['filter'])) {
    if (!empty($_POST['member_name'])) {
        $filter_name = $_POST['member_name'];
    }

    if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $filter_date_query = "AND p.date BETWEEN '$start_date' AND '$end_date'";
    }
}

// Fetch members for the filter dropdown
$members_query = "SELECT DISTINCT member_name FROM members";
$members_result = $conn->query($members_query);

// Fetch filtered payment data for admin view, ordered by member_name and period_week
$sql = "SELECT m.member_name, m.member_id, p.date, p.pay_date, p.time, p.period_week, p.amount, p.fine 
        FROM payments p 
        JOIN members m ON p.member_id = m.member_id
        WHERE m.member_name LIKE '%$filter_name%' $filter_date_query
        ORDER BY m.member_name, p.period_week";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - All Payments</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
            margin: 0;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logout-btn {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-right: 20px;
        }

        .logout-btn:hover {
            background-color: #c82333;
        }

        .add-member-btn, .add-payment-btn, .back-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .add-member-btn:hover, .add-payment-btn:hover, .back-btn:hover {
            background-color: #218838;
        }

        .filter-form {
            display: inline-block;
            margin-right: 20px;
        }

        .filter-form input[type="text"],
        .filter-form input[type="date"],
        .filter-form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
            width: 100%;
        }

        .filter-form button {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
            word-wrap: break-word;
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

        .subtotal-row, .grand-total {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .grand-total {
            background-color: #d1ecf1;
        }

        .green-text {
            color: green;
        }

        .red-text {
            color: red;
        }

        .black-text {
            color: black;
        }

        .add-member-form {
            display: none;
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            background-color: #fff;
            border-radius: 5px;
        }

        .add-member-form input, .add-member-form select, .add-member-form button {
            padding: 10px;
            margin: 5px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
        }

        .add-member-form button {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }

        /* Media Queries for Mobile */
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
            }

            table, th, td {
                font-size: 12px;
            }

            .add-member-btn, .add-payment-btn, .back-btn {
                width: 100%;
                margin-bottom: 10px;
            }

            .filter-form input, .filter-form select, .filter-form button {
                width: 100%;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            h1 {
                font-size: 20px;
            }

            table, th, td {
                font-size: 10px;
            }

            .logout-btn, .add-member-btn, .add-payment-btn, .back-btn {
                font-size: 12px;
                padding: 8px;
            }

            .filter-form input, .filter-form select, .filter-form button {
                font-size: 12px;
                padding: 8px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>

    <script>
        function toggleForm() {
            var form = document.getElementById("addMemberForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <h1>Admin Dashboard - All Payments</h1>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <a href="add_payment.php" class="add-payment-btn">Add Payment</a>
    <a href="javascript:void(0);" class="add-member-btn" onclick="toggleForm()">Add New Member</a>
    <a href="view_all_payments.php" class="back-btn">Back to Home</a>

    <div class="filter-form">
        <form action="view_all_payments.php" method="POST">
            <label for="member_name">Filter by Name:</label>
            <select name="member_name" id="member_name">
                <option value="">-- All Members --</option>
                <?php while ($row_member = $members_result->fetch_assoc()) {
                    echo "<option value='" . $row_member['member_name'] . "'>" . $row_member['member_name'] . "</option>";
                } ?>
            </select>

            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date">

            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date">

            <button type="submit" name="filter">Filter</button>
        </form>
    </div>

    <div id="addMemberForm" class="add-member-form">
        <h2>Add New Member</h2>
        <form action="view_all_payments.php" method="POST">
            <input type="text" name="member_name" placeholder="Member Name" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <button type="submit" name="add_member">Add Member</button>
        </form>
    </div>

    <!-- Table display for payments -->
    <?php if ($result->num_rows > 0) {
        echo "<table><tr>
                <th>Member No.</th>
                <th>Member Name</th>
                <th>Date</th>
                <th>Pay Date</th>
                <th>Time</th>
                <th>Period (Week)</th>
                <th>Amount</th>
                <th>Fine</th>
                <th>Total</th>
                <th>Comments</th>
              </tr>";

        $current_member = '';
        $member_total_amount = 0;
        $member_total_fine = 0;
        $member_count = 0;
        $grand_total_amount = 0;
        $grand_total_fine = 0;

        while ($row = $result->fetch_assoc()) {
            $total = $row['amount'] + $row['fine'];
            $user_id = $row['member_id']; // Fetch the member ID for comments

            // Fetch comments for this user
            $comments_query = "SELECT comment, created_at FROM comments WHERE user_id = '$user_id' ORDER BY created_at DESC";
            $comments_result = $conn->query($comments_query);

            if ($row['member_name'] != $current_member && $current_member != '') {
                $member_total = $member_total_amount + $member_total_fine;
                echo "<tr class='subtotal-row'>
                        <td colspan='6'>Subtotal for $current_member</td>
                        <td class='green-text'>" . number_format($member_total_amount, 2) . "</td>
                        <td class='" . ($member_total_fine == 0.00 ? "black-text" : "red-text") . "'>" . number_format($member_total_fine, 2) . "</td>
                        <td class='green-text'>" . number_format($member_total, 2) . "</td>
                      </tr>";

                $member_total_amount = 0;
                $member_total_fine = 0;
            }

            if ($row['member_name'] != $current_member) {
                $current_member = $row['member_name'];
                $member_count++;
                $show_member_number = true;
            } else {
                $show_member_number = false;
            }

            $member_total_amount += $row['amount'];
            $member_total_fine += $row['fine'];

            $grand_total_amount += $row['amount'];
            $grand_total_fine += $row['fine'];

            echo "<tr>";
            echo $show_member_number ? "<td>" . $member_count . "</td>" : "<td></td>";
            echo "<td>" . $row['member_name'] . "</td>
                    <td>" . $row['date'] . "</td>
                    <td>" . $row['pay_date'] . "</td>
                    <td>" . $row['time'] . "</td>
                    <td>" . $row['period_week'] . "</td>
                    <td class='green-text'>" . number_format($row['amount'], 2) . "</td>
                    <td class='" . ($row['fine'] == 0.00 ? "black-text" : "red-text") . "'>" . number_format($row['fine'], 2) . "</td>
                    <td class='green-text'>" . number_format($total, 2) . "</td>
                    <td>
                        <!-- Comment form -->
                        <form action='view_all_payments.php' method='POST'>
                            <textarea name='comment' placeholder='Add a comment...' required></textarea>
                            <input type='hidden' name='user_id' value='" . $row['member_id'] . "'>
                            <button type='submit' name='add_comment'>Add Comment</button>
                        </form>
                        <ul>";

            // Display existing comments
            while ($comment_row = $comments_result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($comment_row['comment']) . " <small>on " . $comment_row['created_at'] . "</small></li>";
            }

            echo "</ul></td></tr>";
        }

        if ($current_member != '') {
            $member_total = $member_total_amount + $member_total_fine;
            echo "<tr class='subtotal-row'>
                    <td colspan='6'>Subtotal for $current_member</td>
                    <td class='green-text'>" . number_format($member_total_amount, 2) . "</td>
                    <td class='" . ($member_total_fine == 0.00 ? "black-text" : "red-text") . "'>" . number_format($member_total_fine, 2) . "</td>
                    <td class='green-text'>" . number_format($member_total, 2) . "</td>
                  </tr>";
        }

        $grand_total = $grand_total_amount + $grand_total_fine;
        echo "<tr class='grand-total'>
                <td colspan='6'>Grand Total for All Members</td>
                <td class='green-text'>" . number_format($grand_total_amount, 2) . "</td>
                <td class='" . ($grand_total_fine == 0.00 ? "black-text" : "red-text") . "'>" . number_format($grand_total_fine, 2) . "</td>
                <td class='green-text'>" . number_format($grand_total, 2) . "</td>
              </tr>";

        echo "</table>";
    } else {
        echo "<p class='no-records'>No payment records found.</p>";
    }

    $conn->close();
    ?>
</body>
</html>
