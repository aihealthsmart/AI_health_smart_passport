<?php
session_start();
include('db.php');

if(isset($_GET['id'])) {
    $patient_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = mysqli_query($conn, "SELECT * FROM patients WHERE national_id = '$patient_id'");
    $patient = mysqli_fetch_assoc($query);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Record - <?php echo $patient['full_name']; ?></title>
</head>
<body>
    <h1>Consultation for: <?php echo $patient['full_name']; ?></h1>
    <p>ID: <?php echo $patient['national_id']; ?></p>
    </body>
</html>