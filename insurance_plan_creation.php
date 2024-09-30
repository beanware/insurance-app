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

// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo "Access denied. Please log in as an admin.";
    exit;
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_plan'])) {
        // Add new plan
        $plan_name = $_POST['plan_name'];
        $plan_type = $_POST['plan_type'];
        $coverage_amount = $_POST['coverage_amount'];
        $premium_amount = $_POST['premium_amount'];
        $deductible = $_POST['deductible'];
        $coverage_details = $_POST['coverage_details'];
        
        $sql = "INSERT INTO InsurancePlans (plan_name, coverage_description, premium, type, coverage_amount, deductible)
                VALUES ('$plan_name', '$coverage_details', '$premium_amount', '$plan_type', '$coverage_amount', '$deductible')";

        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
    } elseif (isset($_POST['update_plan'])) {
        // Update existing plan
        $plan_id = $_POST['plan_id'];
        $plan_name = $_POST['plan_name'];
        $plan_type = $_POST['plan_type'];
        $coverage_amount = $_POST['coverage_amount'];
        $premium_amount = $_POST['premium_amount'];
        $deductible = $_POST['deductible'];
        $coverage_details = $_POST['coverage_details'];
        
        $sql = "UPDATE InsurancePlans 
                SET plan_name='$plan_name', coverage_description='$coverage_details', premium='$premium_amount', type='$plan_type', 
                    coverage_amount='$coverage_amount', deductible='$deductible'
                WHERE plan_id=$plan_id";

        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
        }
    }
} elseif (isset($_GET['delete'])) {
    // Delete plan
    $plan_id = $_GET['delete'];
    $sql = "DELETE FROM InsurancePlans WHERE plan_id=$plan_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to the same page
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    }
}

// Fetch plans data for display
$sql = "SELECT * FROM InsurancePlans";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Insurance Plans Management</title>
</head>
<body>
    <h2>Insurance Plans Management</h2>
    
    <!-- Create Insurance Plan Form -->
    <h3>Create New Insurance Plan</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <label for="plan_name">Plan Name:</label>
        <input type="text" id="plan_name" name="plan_name" required><br><br>

        <label for="plan_type">Plan Type:</label>
        <select id="plan_type" name="plan_type" required>
            <option value="comprehensive">Comprehensive</option>
            <option value="third_party">Third Party</option>
        </select><br><br>

        <label for="coverage_amount">Coverage Amount (in USD):</label>
        <input type="number" id="coverage_amount" name="coverage_amount" step="0.01" required><br><br>

        <label for="premium_amount">Premium Amount (in USD):</label>
        <input type="number" id="premium_amount" name="premium_amount" step="0.01" required><br><br>

        <label for="deductible">Deductible Amount (in USD):</label>
        <input type="number" id="deductible" name="deductible" step="0.01" required><br><br>

        <label for="coverage_details">Coverage Details:</label>
        <textarea id="coverage_details" name="coverage_details" required></textarea><br><br>

        <input type="submit" name="create_plan" value="Create Plan">
    </form>
    
    <!-- Display Insurance Plans Data -->
    <h3>Insurance Plans List</h3>
    <?php
    if ($result->num_rows > 0) {
        echo "<table border='1'>
                <tr>
                    <th>Plan ID</th>
                    <th>Plan Name</th>
                    <th>Plan Type</th>
                    <th>Coverage Amount</th>
                    <th>Premium Amount</th>
                    <th>Deductible</th>
                    <th>Coverage Details</th>
                    <th>Actions</th>
                </tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['plan_id']}</td>
                    <td>{$row['plan_name']}</td>
                    <td>{$row['type']}</td>
                    <td>{$row['coverage_amount']}</td>
                    <td>{$row['premium']}</td>
                    <td>{$row['deductible']}</td>
                    <td>{$row['coverage_description']}</td>
                    <td>
                        <a href='?edit={$row['plan_id']}'>Edit</a> | 
                        <a href='?delete={$row['plan_id']}'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No insurance plans found.";
    }
    ?>

    <?php
    // Fetch and display edit form if 'edit' query parameter is present
    if (isset($_GET['edit'])) {
        $plan_id = $_GET['edit'];
        $sql = "SELECT * FROM InsurancePlans WHERE plan_id=$plan_id";
        $result = $conn->query($sql);
        $plan = $result->fetch_assoc();
    ?>

    <!-- Edit Insurance Plan Form -->
    <h3>Edit Insurance Plan</h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="hidden" name="plan_id" value="<?php echo $plan['plan_id']; ?>">
        <label for="plan_name">Plan Name:</label>
        <input type="text" id="plan_name" name="plan_name" value="<?php echo $plan['plan_name']; ?>" required><br><br>

        <label for="plan_type">Plan Type:</label>
        <select id="plan_type" name="plan_type" required>
            <option value="comprehensive" <?php if ($plan['type'] == 'comprehensive') echo 'selected'; ?>>Comprehensive</option>
            <option value="third_party" <?php if ($plan['type'] == 'third_party') echo 'selected'; ?>>Third Party</option>
        </select><br><br>

        <label for="coverage_amount">Coverage Amount (in USD):</label>
        <input type="number" id="coverage_amount" name="coverage_amount" step="0.01" value="<?php echo $plan['coverage_amount']; ?>" required><br><br>

        <label for="premium_amount">Premium Amount (in USD):</label>
        <input type="number" id="premium_amount" name="premium_amount" step="0.01" value="<?php echo $plan['premium']; ?>" required><br><br>

        <label for="deductible">Deductible Amount (in USD):</label>
        <input type="number" id="deductible" name="deductible" step="0.01" value="<?php echo $plan['deductible']; ?>" required><br><br>

        <label for="coverage_details">Coverage Details:</label>
        <textarea id="coverage_details" name="coverage_details" required><?php echo $plan['coverage_description']; ?></textarea><br><br>

        <input type="submit" name="update_plan" value="Update Plan">
    </form>

    <?php
    }
    $conn->close();
    ?>
</body>
</html>
