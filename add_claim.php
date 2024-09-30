<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insurance_db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    die("Unauthorized access. You must be an admin to view this page.");
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_claim'])) {
        // Add claim
        $policy_id = $_POST['policy_id'];
        $claim_type = $_POST['claim_type'];
        $claim_description = $_POST['claim_description'];
        $claim_amount = $_POST['claim_amount'];
        $claim_date = $_POST['claim_date'];
        $resolution_date = $_POST['resolution_date'];
        $document_proof = $_POST['document_proof'];

        // Default status is 'submitted'
        $claim_status = 'submitted';

        $sql = "INSERT INTO Claims (policy_id, claim_type, claim_description, claim_status, claim_amount, claim_date, resolution_date, document_proof)
                VALUES ('$policy_id', '$claim_type', '$claim_description', '$claim_status', '$claim_amount', '$claim_date', '$resolution_date', '$document_proof')";

        if ($conn->query($sql) === TRUE) {
            echo "New claim added successfully<br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
    } elseif (isset($_POST['update_claim'])) {
        // Update claim
        $claim_id = $_POST['claim_id'];
        $policy_id = $_POST['policy_id'];
        $claim_type = $_POST['claim_type'];
        $claim_description = $_POST['claim_description'];
        $claim_amount = $_POST['claim_amount'];
        $claim_date = $_POST['claim_date'];
        $resolution_date = $_POST['resolution_date'];
        $document_proof = $_POST['document_proof'];

        $sql = "UPDATE Claims SET policy_id='$policy_id', claim_type='$claim_type', claim_description='$claim_description', claim_amount='$claim_amount', claim_date='$claim_date', resolution_date='$resolution_date', document_proof='$document_proof'
                WHERE claim_id=$claim_id";

        if ($conn->query($sql) === TRUE) {
            echo "Claim updated successfully<br>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
    }
} elseif (isset($_GET['delete'])) {
    // Delete claim
    $claim_id = $_GET['delete'];
    $sql = "DELETE FROM Claims WHERE claim_id=$claim_id";

    if ($conn->query($sql) === TRUE) {
        echo "Claim deleted successfully<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    }
}

// Fetch claims data for display
$sql = "SELECT * FROM Claims";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claims Management</title>
</head>
<body>
    <h2>Claims Management</h2>
    
    <!-- Add Claim Form -->
    <h3>Add New Claim</h3>
    <form action="claims_management.php" method="post">
        <label for="policy_id">Policy ID:</label>
        <input type="number" id="policy_id" name="policy_id" required><br><br>
        
        <label for="claim_type">Claim Type:</label>
        <select id="claim_type" name="claim_type" required>
            <option value="damage">Damage</option>
            <option value="theft">Theft</option>
            <option value="loss">Loss</option>
            <option value="mechanical">Mechanical</option>
            <option value="water">Water</option>
        </select><br><br>
        
        <label for="claim_description">Claim Description:</label>
        <textarea id="claim_description" name="claim_description" rows="4" cols="50" required></textarea><br><br>
        
        <label for="claim_amount">Claim Amount:</label>
        <input type="number" id="claim_amount" name="claim_amount" step="0.01"><br><br>
        
        <label for="claim_date">Claim Date:</label>
        <input type="date" id="claim_date" name="claim_date" required><br><br>
        
        <label for="resolution_date">Resolution Date:</label>
        <input type="date" id="resolution_date" name="resolution_date"><br><br>
        
        <label for="document_proof">Document Proof (Link to Document):</label>
        <input type="text" id="document_proof" name="document_proof"><br><br>
        
        <input type="submit" name="add_claim" value="Add Claim">
    </form>

    <!-- Display Claims Data -->
    <h3>Claims List</h3>
    <?php
    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Claim ID</th>
                    <th>Policy ID</th>
                    <th>Claim Type</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Claim Date</th>
                    <th>Resolution Date</th>
                    <th>Document Proof</th>
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
                    <td><a href='{$row['document_proof']}'>View Document</a></td>
                    <td>
                        <a href='?edit={$row['claim_id']}'>Edit</a> | 
                        <a href='?delete={$row['claim_id']}'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No claims found.";
    }
    ?>

    <?php
    // Fetch and display edit form if 'edit' query parameter is present
    if (isset($_GET['edit'])) {
        $claim_id = $_GET['edit'];
        $sql = "SELECT * FROM Claims WHERE claim_id=$claim_id";
        $result = $conn->query($sql);
        $claim = $result->fetch_assoc();
    ?>

    <!-- Edit Claim Form -->
    <h3>Edit Claim</h3>
    <form action="claims_management.php" method="post">
        <input type="hidden" name="claim_id" value="<?php echo $claim['claim_id']; ?>">
        <label for="policy_id">Policy ID:</label>
        <input type="number" id="policy_id" name="policy_id" value="<?php echo $claim['policy_id']; ?>" required><br><br>
        
        <label for="claim_type">Claim Type:</label>
        <select id="claim_type" name="claim_type" required>
            <option value="damage" <?php if ($claim['claim_type'] == 'damage') echo 'selected'; ?>>Damage</option>
            <option value="theft" <?php if ($claim['claim_type'] == 'theft') echo 'selected'; ?>>Theft</option>
            <option value="loss" <?php if ($claim['claim_type'] == 'loss') echo 'selected'; ?>>Loss</option>
            <option value="mechanical" <?php if ($claim['claim_type'] == 'mechanical') echo 'selected'; ?>>Mechanical</option>
            <option value="water" <?php if ($claim['claim_type'] == 'water') echo 'selected'; ?>>Water</option>
        </select><br><br>
        
        <label for="claim_description">Claim Description:</label>
        <textarea id="claim_description" name="claim_description" rows="4" cols="50" required><?php echo $claim['claim_description']; ?></textarea><br><br>
        
        <!-- Status field is not included in the edit form -->
        
        <label for="claim_amount">Claim Amount:</label>
        <input type="number" id="claim_amount" name="claim_amount" step="0.01" value="<?php echo $claim['claim_amount']; ?>"><br><br>
        
        <label for="claim_date">Claim Date:</label>
        <input type="date" id="claim_date" name="claim_date" value="<?php echo $claim['claim_date']; ?>" required><br><br>
        
        <label for="resolution_date">Resolution Date:</label>
        <input type="date" id="resolution_date" name="resolution_date" value="<?php echo $claim['resolution_date']; ?>"><br><br>
        
        <label for="document_proof">Document Proof (Link to Document):</label>
        <input type="text" id="document_proof" name="document_proof" value="<?php echo $claim['document_proof']; ?>"><br><br>
        
        <input type="submit" name="update_claim" value="Update Claim">
    </form>
    <?php
    }
    ?>

</body>
</html>

<?php
$conn->close();
?>
