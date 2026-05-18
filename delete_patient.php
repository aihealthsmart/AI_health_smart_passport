<?php
session_start();
include('db.php');
if ($_SESSION['role'] == 'doctor') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM patients WHERE national_id = '$id'");
    header("Location: index.php");
} else {
    die("Unauthorized access.");
}
?>