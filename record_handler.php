<?php
include 'db.php'; // Includes the connection code above

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture data from your form
    $patient_id = $_POST['national_id']; 
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];

    // SQL to insert into medical_records table
    $sql = "INSERT INTO medical_records (patient_id, diagnosis, treatment_plan) 
            VALUES ('$patient_id', '$diagnosis', '$treatment')";

    if (mysqli_query($conn, $sql)) {
        echo "Record successfully linked to Patient: " . $patient_id;
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>