<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insurance_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch claims for dropdown and display
$claims_query = "SELECT claim_id, CONCAT('Claim ID: ', claim_id, ' (', claim_description, ')') AS claim_description FROM Claims";
$claims_result = $conn->query($claims_query);

// Process form submission for update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_claim'])) {
    $claim_id = $_POST['claim_id'];
    $status = $_POST['status'];
    $comments = $_POST['comments'];
    $resolution_date = $_POST['resolution_date'];

    $sql = "UPDATE Claims 
            SET claim_status='$status', comments='$comments', resolution_date='$resolution_date' 
            WHERE claim_id='$claim_id'";

    if ($conn->query($sql) === TRUE) {
        echo "<p>Claim updated successfully.</p>";
    } else {
        echo "<p>Error: " . $sql . "<br>" . $conn->error . "</p>";
    }
}

// Fetch all claims for display
$sql = "SELECT * FROM Claims";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Claims</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        form {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h2>Review Claims</h2>

    <!-- Display Claims Data -->
    <h3>Claims List</h3>
    <?php
    if ($result->num_rows > 0) {
        echo "<table>
                <tr>
                    <th>Claim ID</th>
                    <th>Policy ID</th>
                    <th>Claim Type</th>
                    <th>Claim Description</th>
                    <th>Claim Status</th>
                    <th>Claim Amount</th>
                    <th>Claim Date</th>
                    <th>Resolution Date</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['claim_id']}</td>
                    <td>{$row['policy_id']}</td>
                    <td>{$row['claim_type']}</td>
                    <td>{$row['claim_description']}</td>
                    <td>{$row['claim_status']}</td>
                    <td>{$row['claim_amount']}</td>
                    <td>{$row['claim_date']}</td>
                    <td>{$row['resolution_date']}</td>
                    <td>
                        <a href='?edit={$row['claim_id']}'>Edit</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No claims found.</p>";
    }
    ?>

    <!-- Edit Claim Form -->
    <?php
    if (isset($_GET['edit'])) {
        $claim_id = $_GET['edit'];
        $sql = "SELECT * FROM Claims WHERE claim_id=$claim_id";
        $result = $conn->query($sql);
        $claim = $result->fetch_assoc();
    ?>

    <h3>Edit Claim</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="hidden" name="claim_id" value="<?php echo $claim['claim_id']; ?>">

        <label for="policy_id">Policy ID:</label>
        <input type="number" id="policy_id" name="policy_id" value="<?php echo $claim['policy_id']; ?>" readonly><br><br>

        <label for="claim_type">Claim Type:</label>
        <input type="text" id="claim_type" name="claim_type" value="<?php echo htmlspecialchars($claim['claim_type']); ?>" readonly><br><br>

        <label for="claim_description">Claim Description:</label>
        <textarea id="claim_description" name="claim_description" rows="4" cols="50" readonly><?php echo htmlspecialchars($claim['claim_description']); ?></textarea><br><br>

        <label for="status">Status:</label>
        <select id="status" name="status" required>
            <option value="submitted" <?php if ($claim['claim_status'] == 'submitted') echo 'selected'; ?>>Submitted</option>
            <option value="under_review" <?php if ($claim['claim_status'] == 'under_review') echo 'selected'; ?>>Under Review</option>
            <option value="approved" <?php if ($claim['claim_status'] == 'approved') echo 'selected'; ?>>Approved</option>
            <option value="rejected" <?php if ($claim['claim_status'] == 'rejected') echo 'selected'; ?>>Rejected</option>
        </select><br><br>

        <label for="claim_amount">Claim Amount:</label>
        <input type="number" id="claim_amount" name="claim_amount" value="<?php echo htmlspecialchars($claim['claim_amount']); ?>" readonly><br><br>

        <label for="claim_date">Claim Date:</label>
        <input type="date" id="claim_date" name="claim_date" value="<?php echo $claim['claim_date']; ?>" readonly><br><br>

        <label for="resolution_date">Resolution Date:</label>
        <input type="date" id="resolution_date" name="resolution_date" value="<?php echo $claim['resolution_date']; ?>"><br><br>

        <label for="comments">Comments:</label>
        <textarea id="comments" name="comments" rows="4" cols="50"><?php echo htmlspecialchars($claim['comments']); ?></textarea><br><br>

        <input type="submit" name="update_claim" value="Update Claim">
    </form>

    <?php
    }
    $conn->close();
    ?>
</body>
</html>
