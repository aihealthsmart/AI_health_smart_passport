<?php
session_start();
include('db.php');

// Security Check: Only Doctors and Nurses can see this
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'doctor' && $_SESSION['role'] !== 'nurse')) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Outbreak Tracking - AI Health</title>
    <link rel="stylesheet" href="style.css"> <style>
        /* Reusing your dashboard styles */
        body { font-family: 'Segoe UI', sans-serif; background: #e8f5e9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h2 { color: #003399; border-bottom: 2px solid #ffcc00; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f9fa; color: #555; }
        .alert-high { color: white; background: #dc3545; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .btn-back { display: inline-block; margin-bottom: 20px; text-decoration: none; color: #003399; font-weight: bold; }
    </style>
</head>
<body>

<div class="container">
    <a href="index.php" class="btn-back">&larr; Back to Dashboard</a>
    <h2>Outbreak & Disease Trend Tracking</h2>
    <p>The following table shows the most common diagnoses reported in the last 14 days.</p>

    <table>
        <thead>
            <tr>
                <th>Diagnosis / Problem</th>
                <th>Number of Cases</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Query to find common problems in the last 14 days
            // Note: Make sure your 'medical_records' table has a 'diagnosis' column
            $query = "SELECT diagnosis, COUNT(*) as case_count 
                      FROM medical_records 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) 
                      GROUP BY diagnosis 
                      ORDER BY case_count DESC";
            
            $result = mysqli_query($conn, $query);

            if ($result && mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    $diagnosis = htmlspecialchars($row['diagnosis'] ?: 'Unspecified');
                    $count = $row['case_count'];
                    
                    // Logic to flag potential outbreaks (e.g., more than 5 cases)
                    $status = ($count >= 5) ? '<span class="alert-high">Potential Outbreak</span>' : '<span style="color:green;">Normal</span>';
                    
                    echo "<tr>
                            <td><strong>$diagnosis</strong></td>
                            <td>$count</td>
                            <td>$status</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center;'>No clinical data found for the last 14 days.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>