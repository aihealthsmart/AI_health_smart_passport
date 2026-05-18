<?php
include('db.php');
session_start();

if(!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$role = $_SESSION['role']; // Get the role from session
?>

<!DOCTYPE html>
<html>
<head>
    <title>AI Smart Health - Dashboard</title>
    </head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($user); ?> (<?php echo ucfirst($role); ?>)</h2>

        <?php if ($role == 'doctor' || $role == 'nurse'): ?>
            <div class="box">
                <h3>Staff Actions: Manage Patient Records</h3>
                <form method="POST">
                    <input type="text" name="patient_name" placeholder="Patient Name" required><br>
                    <textarea name="record_data" placeholder="Update medical notes..."></textarea><br>
                    
                    <button type="submit" name="register_patient">Register Patient</button>
                    
                    <?php if ($role == 'doctor'): ?>
                        <button type="submit" name="run_ai" style="background:blue;">Run AI Analysis</button>
                    <?php endif; ?>
                    
                    <button type="submit" name="update">Save Changes</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($role == 'patient'): ?>
            <div class="box">
                <h3>My Medical Record</h3>
                <p>Status: Viewing Only</p>
                <div style="background: #f9f9f9; padding: 15px; color: #333; text-align: left;">
                    <?php 
                        // Fetch records for the logged-in patient only
                        $query = mysqli_query($conn, "SELECT medical_records FROM users WHERE username='$user'");
                        $data = mysqli_fetch_assoc($query);
                        echo $data['medical_records'] ? $data['medical_records'] : "No records found.";
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <br>
        <a href="logout.php" style="color: white;">Logout</a>
    </div>
</body>
</html>