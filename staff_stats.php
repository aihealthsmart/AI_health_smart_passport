<?php
// REMOVE OR COMMENT OUT THE LINE BELOW
// session_start(); 

include('db.php');

// The rest of your code remains exactly the same
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'doctor' && $_SESSION['role'] != 'nurse')) {
    header("Location: index.php?error=unauthorized");
    exit();
}

$today = date('Y-m-d');
$stats_query = mysqli_query($conn, "SELECT total_attended FROM daily_stats WHERE stat_date = '$today'");
$stats = mysqli_fetch_assoc($stats_query);
$count = $stats['total_attended'] ?? 0;
?>

<div class="stats-banner">
    Total Patients Attended Today: <?php echo $count; ?>
</div>