<?php
// Start the session
session_start();

// Database connection parameters
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "insurance_db";

// Create connection using MySQLi
$conn = new mysqli($servername, $username_db, $password_db, $dbname);

// Check connection
if ($conn->connect_error) {
    die("<div class='alert alert-danger text-center' role='alert'>
            Connection failed: " . htmlspecialchars($conn->connect_error) . "
         </div>");
}

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("<div class='alert alert-danger text-center' role='alert'>User not logged in.</div>");
}

// Fetch user role
$user_role = 'user'; // default

$stmt_role = $conn->prepare("SELECT role FROM Users WHERE user_id = ?");
if ($stmt_role) {
    $stmt_role->bind_param("i", $_SESSION['user_id']);
    $stmt_role->execute();
    $stmt_role->bind_result($role);
    if ($stmt_role->fetch()) {
        $user_role = $role;
    }
    $stmt_role->close();
} else {
    die("<div class='alert alert-danger text-center' role='alert'>Error fetching user role.</div>");
}

// Initialize variables for feedback messages
$success_msg = "";
$error_msg = "";

// Fetch Insurance Plans
$plans_query = "SELECT plan_id, plan_name, plan_type FROM InsurancePlans";
$plans_result = $conn->query($plans_query);
$plans = [];
if ($plans_result) {
    while ($row = $plans_result->fetch_assoc()) {
        $plans[] = $row;
    }
    $plans_result->free();
} else {
    $error_msg = "Error fetching insurance plans: " . htmlspecialchars($conn->error);
}

// Handle Create or Update Policy
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Determine action: create or update
    $action = isset($_POST['create_policy']) ? 'create' : (isset($_POST['update_policy']) ? 'update' : '');

    if ($action) {
        // For update actions, ensure only admins can perform them
        if ($action === 'update_policy' && $user_role !== 'admin') {
            $error_msg = "You do not have permission to perform this action.";
        } else {
            // Retrieve and sanitize common form inputs
            $user_id = $_SESSION['user_id'];
            $plan_id = intval($_POST['plan_id']);
            $start_date = $_POST['start_date'];
            $end_date = $_POST['end_date'];

            // Fetch plan type based on plan_id
            $stmt = $conn->prepare("SELECT plan_type FROM InsurancePlans WHERE plan_id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $plan_id);
                $stmt->execute();
                $stmt->bind_result($plan_type);
                if ($stmt->fetch()) {
                    $stmt->close();

                    // Initialize variables for extra fields
                    $vehicle_model = null;
                    $registration_number = null;
                    $health_condition = null;
                    $property_value = null;

                    // Validate and assign extra fields based on plan_type
                    switch ($plan_type) {
                        case 'motor':
                            $vehicle_model = trim($_POST['vehicle_model']);
                            $registration_number = trim($_POST['registration_number']);
                            if (empty($vehicle_model) || empty($registration_number)) {
                                $error_msg = "All motor insurance details are required.";
                            }
                            break;
                        case 'health':
                            $health_condition = trim($_POST['health_condition']);
                            if (empty($health_condition)) {
                                $error_msg = "Health condition is required for health insurance.";
                            }
                            break;
                        case 'home':
                            $property_value = trim($_POST['property_value']);
                            if (empty($property_value) || !is_numeric($property_value)) {
                                $error_msg = "Valid property value is required for home insurance.";
                            }
                            break;
                        default:
                            $error_msg = "Invalid plan type.";
                    }

                    if (empty($error_msg)) {
                        if ($action === 'create') {
                            // Prepare INSERT statement
                            switch ($plan_type) {
                                case 'motor':
                                    $insert_stmt = $conn->prepare("INSERT INTO Policies (user_id, plan_id, start_date, end_date, vehicle_model, registration_number) VALUES (?, ?, ?, ?, ?, ?)");
                                    $insert_stmt->bind_param("iissss", $user_id, $plan_id, $start_date, $end_date, $vehicle_model, $registration_number);
                                    break;
                                case 'health':
                                    $insert_stmt = $conn->prepare("INSERT INTO Policies (user_id, plan_id, start_date, end_date, health_condition) VALUES (?, ?, ?, ?, ?)");
                                    $insert_stmt->bind_param("iisss", $user_id, $plan_id, $start_date, $end_date, $health_condition);
                                    break;
                                case 'home':
                                    $insert_stmt = $conn->prepare("INSERT INTO Policies (user_id, plan_id, start_date, end_date, property_value) VALUES (?, ?, ?, ?, ?)");
                                    $insert_stmt->bind_param("iissd", $user_id, $plan_id, $start_date, $end_date, $property_value);
                                    break;
                            }

                            // Execute the INSERT statement
                            if (isset($insert_stmt) && $insert_stmt->execute()) {
                                $success_msg = "New policy created successfully.";
                            } else {
                                $error_msg = "Error creating policy: " . htmlspecialchars($insert_stmt->error);
                            }

                            if (isset($insert_stmt)) {
                                $insert_stmt->close();
                            }
                        } elseif ($action === 'update') {
                            // Retrieve policy_id
                            $policy_id = intval($_POST['policy_id']);

                            // Prepare UPDATE statement
                            switch ($plan_type) {
                                case 'motor':
                                    $update_stmt = $conn->prepare("UPDATE Policies SET plan_id = ?, start_date = ?, end_date = ?, vehicle_model = ?, registration_number = ? WHERE policy_id = ?");
                                    $update_stmt->bind_param("issssi", $plan_id, $start_date, $end_date, $vehicle_model, $registration_number, $policy_id);
                                    break;
                                case 'health':
                                    $update_stmt = $conn->prepare("UPDATE Policies SET plan_id = ?, start_date = ?, end_date = ?, health_condition = ? WHERE policy_id = ?");
                                    $update_stmt->bind_param("isssi", $plan_id, $start_date, $end_date, $health_condition, $policy_id);
                                    break;
                                case 'home':
                                    $update_stmt = $conn->prepare("UPDATE Policies SET plan_id = ?, start_date = ?, end_date = ?, property_value = ? WHERE policy_id = ?");
                                    $update_stmt->bind_param("iissi", $plan_id, $start_date, $end_date, $property_value, $policy_id);
                                    break;
                            }

                            // Execute the UPDATE statement
                            if (isset($update_stmt) && $update_stmt->execute()) {
                                $success_msg = "Policy updated successfully.";
                            } else {
                                $error_msg = "Error updating policy: " . htmlspecialchars($update_stmt->error);
                            }

                            if (isset($update_stmt)) {
                                $update_stmt->close();
                            }
                        }
                    }
                } else {
                    $error_msg = "Invalid insurance plan selected.";
                    $stmt->close();
                }
            } else {
                $error_msg = "Prepare failed: " . htmlspecialchars($conn->error);
            }
        }
    }}

// Handle Delete Policy
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete'])) {
    $policy_id = intval($_GET['delete']);

    // Only admins can delete policies
    if ($user_role !== 'admin') {
        $error_msg = "You do not have permission to perform this action.";
    } else {
        // Verify that the policy exists
        $stmt = $conn->prepare("SELECT policy_id FROM Policies WHERE policy_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $policy_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->close();
                // Proceed to delete
                $delete_stmt = $conn->prepare("DELETE FROM Policies WHERE policy_id = ?");
                if ($delete_stmt) {
                    $delete_stmt->bind_param("i", $policy_id);
                    if ($delete_stmt->execute()) {
                        $success_msg = "Policy deleted successfully.";
                    } else {
                        $error_msg = "Error deleting policy: " . htmlspecialchars($delete_stmt->error);
                    }
                    $delete_stmt->close();
                } else {
                    $error_msg = "Prepare failed: " . htmlspecialchars($conn->error);
                }
            } else {
                $error_msg = "Policy not found.";
                $stmt->close();
            }
        } else {
            $error_msg = "Prepare failed: " . htmlspecialchars($conn->error);
        }
    }
}

// Handle Edit Policy (Fetch existing data)
$edit_policy = null;
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['edit'])) {
    $policy_id = intval($_GET['edit']);

    // Only admins can edit policies
    if ($user_role !== 'admin') {
        $error_msg = "You do not have permission to perform this action.";
    } else {
        // Fetch policy details
        $stmt = $conn->prepare("SELECT * FROM Policies WHERE policy_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $policy_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $edit_policy = $result->fetch_assoc();
            } else {
                $error_msg = "Policy not found.";
            }

            $stmt->close();
        } else {
            $error_msg = "Prepare failed: " . htmlspecialchars($conn->error);
        }
    }
}

// Fetch Policies for display
$display_policies = [];
if ($user_role === 'admin') {
    // Admin: Fetch all policies
    $stmt = $conn->prepare("SELECT * FROM Policies");
} else {
    // Regular user: Fetch only their policies
    $stmt = $conn->prepare("SELECT * FROM Policies WHERE user_id = ?");
}

if ($stmt) {
    if ($user_role === 'admin') {
        $stmt->execute();
    } else {
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
    }
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $display_policies[] = $row;
    }
    $stmt->close();
} else {
    $error_msg = "Error fetching policies: " . htmlspecialchars($conn->error);
}

// Close the connection at the end
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
            max-width: 800px;
            margin: 50px auto;
        }
        h2 {
            text-align: center;
            color: #0d6efd;
            margin-bottom: 30px;
        }
        .table th {
            background-color: #0d6efd;
            color: white;
        }
        .action-buttons a {
            margin-right: 5px;
        }
    </style>
    <script>
        function updateFormFields() {
            const planSelect = document.getElementById('plan_id');
            const selectedOption = planSelect.options[planSelect.selectedIndex];
            const planType = selectedOption.dataset.type;

            // Hide all fields
            const fields = ['motor-fields', 'health-fields', 'home-fields'];
            fields.forEach(id => {
                document.getElementById(id).style.display = 'none';
                // Also disable inputs to prevent submission
                const inputs = document.querySelectorAll(`#${id} input`);
                inputs.forEach(input => input.disabled = true);
            });

            if (planType) {
                const activeFields = `${planType}-fields`;
                document.getElementById(activeFields).style.display = 'block';
                // Enable inputs
                const inputs = document.querySelectorAll(`#${activeFields} input`);
                inputs.forEach(input => input.disabled = false);
            }
        }

        // Function to prefill the edit form
        function prefillForm(policy) {
            const planSelect = document.getElementById('plan_id');
            planSelect.value = policy.plan_id;
            updateFormFields();

            document.getElementById('start_date').value = policy.start_date;
            document.getElementById('end_date').value = policy.end_date;

            if (policy.plan_type === 'motor') {
                document.getElementById('vehicle_model').value = policy.vehicle_model;
                document.getElementById('registration_number').value = policy.registration_number;
            } else if (policy.plan_type === 'health') {
                document.getElementById('health_condition').value = policy.health_condition;
            } else if (policy.plan_type === 'home') {
                document.getElementById('property_value').value = policy.property_value;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($edit_policy): ?>
                const policy = <?php echo json_encode($edit_policy); ?>;
                prefillForm(policy);
            <?php endif; ?>
        });
    </script>
</head>
<body>

<div class="form-container">
    <h2>Policy Management</h2>

    <!-- Display Success Message -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Display Error Message -->
    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_msg); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Create or Edit Policy Form -->
    <h3><?php echo $edit_policy ? 'Edit Policy' : 'Create New Policy'; ?></h3>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <?php if ($edit_policy): ?>
            <input type="hidden" name="policy_id" value="<?php echo htmlspecialchars($edit_policy['policy_id']); ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label for="plan_id" class="form-label">Insurance Plan:</label>
            <select id="plan_id" name="plan_id" class="form-select" onchange="updateFormFields()" required>
                <option value="" data-type="">Select a plan</option>
                <?php foreach ($plans as $plan): ?>
                    <option value="<?php echo htmlspecialchars($plan['plan_id']); ?>" data-type="<?php echo htmlspecialchars($plan['plan_type']); ?>">
                        <?php echo htmlspecialchars($plan['plan_name']); ?>
                    </option>
                <?php endforeach; ?>
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
            <h4>Motor Insurance Details</h4>
            <div class="mb-3">
                <label for="vehicle_model" class="form-label">Vehicle Model:</label>
                <input type="text" id="vehicle_model" name="vehicle_model" class="form-control" disabled required>
            </div>
            <div class="mb-3">
                <label for="registration_number" class="form-label">Registration Number:</label>
                <input type="text" id="registration_number" name="registration_number" class="form-control" disabled required>
            </div>
        </div>

        <!-- Health Insurance Fields -->
        <div id="health-fields" style="display:none;">
            <h4>Health Insurance Details</h4>
            <div class="mb-3">
                <label for="health_condition" class="form-label">Health Condition:</label>
                <input type="text" id="health_condition" name="health_condition" class="form-control" disabled required>
            </div>
        </div>

        <!-- Home Insurance Fields -->
        <div id="home-fields" style="display:none;">
            <h4>Home Insurance Details</h4>
            <div class="mb-3">
                <label for="property_value" class="form-label">Property Value (KES):</label>
                <input type="number" id="property_value" name="property_value" class="form-control" disabled required>
            </div>
        </div>

        <button type="submit" name="<?php echo $edit_policy ? 'update_policy' : 'create_policy'; ?>" class="btn <?php echo $edit_policy ? 'btn-warning' : 'btn-primary'; ?>">
            <?php echo $edit_policy ? 'Update Policy' : 'Create Policy'; ?>
        </button>
        <?php if ($edit_policy): ?>
            <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="btn btn-secondary">Cancel</a>
        <?php endif; ?>
    </form>
</div>

<div class="container mt-5">
    <h2>Existing Policies</h2>
    <?php if (!empty($display_policies)): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Policy ID</th>
                    <th>Plan Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Details</th>
                    <?php if ($user_role === 'admin'): ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($display_policies as $policy): ?>
                    <?php
                        // Fetch plan details
                        $plan_name = "N/A";
                        $plan_type = "N/A";
                        // Create a new connection for fetching plan details
                        $stmt_plan = new mysqli($servername, $username_db, $password_db, $dbname);
                        if ($stmt_plan->connect_error) {
                            $plan_name = "N/A";
                            $plan_type = "N/A";
                        } else {
                            $stmt_p = $stmt_plan->prepare("SELECT plan_name, plan_type FROM InsurancePlans WHERE plan_id = ?");
                            if ($stmt_p) {
                                $stmt_p->bind_param("i", $policy['plan_id']);
                                $stmt_p->execute();
                                $stmt_p->bind_result($pn, $pt);
                                if ($stmt_p->fetch()) {
                                    $plan_name = $pn;
                                    $plan_type = $pt;
                                }
                                $stmt_p->close();
                            }
                            $stmt_plan->close();
                        }

                        // Prepare details based on plan_type
                        $details = "";
                        switch ($plan_type) {
                            case 'motor':
                                $details = "Vehicle Model: " . htmlspecialchars($policy['vehicle_model']) . "<br>Registration Number: " . htmlspecialchars($policy['registration_number']);
                                break;
                            case 'health':
                                $details = "Health Condition: " . htmlspecialchars($policy['health_condition']);
                                break;
                            case 'home':
                                $details = "Property Value: KES " . number_format($policy['property_value'], 2);
                                break;
                            default:
                                $details = "N/A";
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($policy['policy_id']); ?></td>
                        <td><?php echo htmlspecialchars($plan_name); ?></td>
                        <td><?php echo htmlspecialchars($policy['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($policy['end_date']); ?></td>
                        <td><?php echo $details; ?></td>
                        <?php if ($user_role === 'admin'): ?>
                            <td class="action-buttons">
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?edit=<?php echo htmlspecialchars($policy['policy_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?delete=<?php echo htmlspecialchars($policy['policy_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this policy?');">Delete</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info text-center" role="alert">No policies found.</div>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
