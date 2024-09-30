<?php
// Start the session
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "insurance_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<div class='alert alert-danger text-center' role='alert'>Connection failed: " . htmlspecialchars($conn->connect_error) . "</div>");
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<div class='alert alert-danger text-center' role='alert'>User not logged in.</div>");
}

// Fetch insurance plans for dropdown
$plans_query = "SELECT plan_id, plan_name, plan_type FROM InsurancePlans";
$plans_result = $conn->query($plans_query);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_policy'])) {
        // Create policy
        $user_id = $_SESSION['user_id'];
        $plan_id = $_POST['plan_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        
        // Initialize fields based on plan type
        $extra_fields = '';
        if ($_POST['plan_type'] === 'motor') {
            $vehicle_model = $_POST['vehicle_model'];
            $registration_number = $_POST['registration_number'];
            $extra_fields = ", '$vehicle_model', '$registration_number'";
        } elseif ($_POST['plan_type'] === 'health') {
            $health_condition = $_POST['health_condition'];
            $extra_fields = ", '$health_condition'";
        } elseif ($_POST['plan_type'] === 'home') {
            $property_value = $_POST['property_value'];
            $extra_fields = ", '$property_value'";
        }

        $sql = "INSERT INTO Policies (user_id, plan_id, start_date, end_date" . ($extra_fields ? ", extra_field1, extra_field2" : "") . ")
                VALUES ('$user_id', '$plan_id', '$start_date', '$end_date'" . $extra_fields . ")";

        if ($conn->query($sql) === TRUE) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "<div class='alert alert-danger text-center' role='alert'>Error: " . htmlspecialchars($sql) . "<br>" . htmlspecialchars($conn->error) . "</div>";
        }
    }
}

// Fetch policies data for display
$p_query = "SELECT * FROM Policies WHERE user_id = " . $_SESSION['user_id'];
$p_result = $conn->query($p_query);

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Policy Management | Insurance Company</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 50px auto;
        }
        h2 {
            text-align: center;
            color: #0d6efd;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
        }
    </style>
    <script>
        function updateFormFields() {
            const planSelect = document.getElementById('plan_id');
            const planType = planSelect.options[planSelect.selectedIndex].dataset.type;
            const motorFields = document.getElementById('motor-fields');
            const healthFields = document.getElementById('health-fields');
            const homeFields = document.getElementById('home-fields');

            motorFields.style.display = 'none';
            healthFields.style.display = 'none';
            homeFields.style.display = 'none';

            if (planType === 'motor') {
                motorFields.style.display = 'block';
            } else if (planType === 'health') {
                healthFields.style.display = 'block';
            } else if (planType === 'home') {
                homeFields.style.display = 'block';
            }
        }
    </script>
</head>
<body>

<div class="form-container">
    <h2>Create Policy</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" onsubmit="updateFormFields();">
        <div class="mb-3">
            <label for="plan_id" class="form-label">Insurance Plan:</label>
            <select id="plan_id" name="plan_id" class="form-select" onchange="updateFormFields()" required>
                <option value="" data-type="">Select a plan</option>
                <?php
                // Fetch plans again if needed
                $conn = new mysqli($servername, $username, $password, $dbname);
                $plans_result = $conn->query($plans_query);
                if ($plans_result->num_rows > 0) {
                    while ($row = $plans_result->fetch_assoc()) {
                        echo "<option value='{$row['plan_id']}' data-type='{$row['plan_type']}'>{$row['plan_name']}</option>";
                    }
                }
                $conn->close();
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" id="end_date" name="end_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
        </div>

        <!-- Motor Insurance Fields -->
        <div id="motor-fields" style="display:none;">
            <h3>Motor Insurance Details</h3>
            <div class="mb-3">
                <label for="vehicle_model" class="form-label">Vehicle Model:</label>
                <input type="text" id="vehicle_model" name="vehicle_model" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="registration_number" class="form-label">Registration Number:</label>
                <input type="text" id="registration_number" name="registration_number" class="form-control" required>
            </div>
        </div>

        <!-- Health Insurance Fields -->
        <div id="health-fields" style="display:none;">
            <h3>Health Insurance Details</h3>
            <div class="mb-3">
                <label for="health_condition" class="form-label">Health Condition:</label>
                <input type="text" id="health_condition" name="health_condition" class="form-control" required>
            </div>
        </div>

        <!-- Home Insurance Fields -->
        <div id="home-fields" style="display:none;">
            <h3>Home Insurance Details</h3>
            <div class="mb-3">
                <label for="property_value" class="form-label">Property Value:</label>
                <input type="text" id="property_value" name="property_value" class="form-control" required>
            </div>
        </div>

        <button type="submit" name="create_policy" class="btn btn-primary">Create Policy</button>
    </form>
</div>

<div class="container mt-5">
    <h2>Existing Policies</h2>
    <?php
    // Fetch and display existing policies
    if ($p_result->num_rows > 0) {
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Policy ID</th>
                        <th>User ID</th>
                        <th>Plan ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Extra Field 1</th>
                        <th>Extra Field 2</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>";
        while ($row = $p_result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['policy_id']}</td>
                    <td>{$row['user_id']}</td>
                    <td>{$row['plan_id']}</td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                    <td>{$row['extra_field1']}</td>
                    <td>{$row['extra_field2']}</td>
                    <td>
                        <a href='?edit={$row['policy_id']}' class='btn btn-warning btn-sm'>Edit</a> 
                        <a href='?delete={$row['policy_id']}' class='btn btn-danger btn-sm'>Delete</a>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-info text-center' role='alert'>No policies found.</div>";
    }
    ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
