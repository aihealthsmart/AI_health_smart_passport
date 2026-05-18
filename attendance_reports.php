<?php
session_start();
include('db.php');

// 1. SECURITY CHECK
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'doctor' && $_SESSION['role'] !== 'nurse')) {
    header("Location: index.php");
    exit();
}

$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Attendance Report</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 20px; }
        .report-container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header-flex { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #003399; padding-bottom: 10px; }
        .total-badge { background: #003399; color: white; padding: 10px 20px; border-radius: 50px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .print-btn { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        @media print { .print-btn, .btn-back { display: none; } }
        .error-box { color:red; padding:20px; background:#fff0f0; border:1px solid red; margin-bottom: 20px; border-radius: 5px; }
    </style>
</head>
<body>

<div class="report-container">
    <a href="index.php" class="btn-back" style="text-decoration:none; color:#666;">&larr; Back to Dashboard</a>
    
    <div class="header-flex">
        <div>
            <h2 style="margin:0;">Daily Attendance Report</h2>
            <p style="color:#666;">Date: <?php echo date('D, d M Y'); ?></p>
        </div>
        <button class="print-btn" onclick="window.print()">Print Report</button>
    </div>

    <?php
    // The requested SQL fix: Joining on national_id instead of p.id
    $query = "SELECT m.*, p.full_name, p.national_id 
              FROM medical_records m
              JOIN patients p ON m.user_id = p.national_id 
              WHERE DATE(m.created_at) = CURDATE()
              ORDER BY m.created_at DESC";
    
    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo "<div class='error-box'>
                <strong>Database Error:</strong> Could not link patient data.<br>
                <em>Error Detail: " . mysqli_error($conn) . "</em>
              </div>";
        $count = 0;
    } else {
        $count = mysqli_num_rows($result);
    }
    ?>

    <div style="margin-top:20px;">
        <span class="total-badge">Total Patients Attended: <?php echo $count; ?></span>
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Patient ID</th>
                <th>Patient Name</th>
                <th>Diagnosis</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($count > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $time = date('H:i', strtotime($row['created_at']));
                    $p_id = htmlspecialchars($row['national_id']);
                    $p_name = htmlspecialchars($row['full_name']);
                    $diagnosis = htmlspecialchars($row['diagnosis'] ?? 'General Consultation');
                    
                    echo "<tr>
                            <td>$time</td>
                            <td>$p_id</td>
                            <td>$p_name</td>
                            <td>$diagnosis</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center;'>No patients recorded for today yet.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>