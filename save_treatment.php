<?php
include('db.php');

if(isset($_POST['save_btn'])) {
    $p_id = $_POST['p_id'];
    $treatment = $_POST['notes'];
    $ai_diag = $_POST['ai_prediction'] ?? "Analyzed by AI";

    $sql = "INSERT INTO medical_records (patient_id, treatment, ai_diagnosis) 
            VALUES ('$p_id', '$treatment', '$ai_diag')";
    
    if(mysqli_query($conn, $sql)) {
        echo "<script>alert('AI-Assisted Record Saved!'); window.location='index.php';</script>";
    }
}
?>