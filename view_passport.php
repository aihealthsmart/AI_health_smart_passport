<?php
// Include your database connection
include('db_connect.php');
session_start();

// Get the logged-in user's info
$username = $_SESSION['username'];

// 1. Fetch the patient's ID from the users table
$user_query = mysqli_query($conn, "SELECT id_number FROM users WHERE username = '$username'");
$user_data = mysqli_fetch_assoc($user_query);
$patient_id = $user_data['id_number'];

// 2. Fetch the medical record
$record_query = mysqli_query($conn, "SELECT * FROM medical_records WHERE patient_id = '$patient_id'");
$record = mysqli_fetch_assoc($record_query);
?>

<h2>My Medical Passport</h2>
<div class="record-box">
    <p><strong>Symptoms:</strong> <?php echo $record['symptoms']; ?></p>
    <p><strong>Treatment:</strong> <?php echo $record['treatment']; ?></p>
    <p><strong>AI Diagnosis:</strong> <?php echo $record['ai_diagnosis']; ?></p>
    <p><strong>Medical History:</strong> <?php echo $record['medical_history']; ?></p>
</div>