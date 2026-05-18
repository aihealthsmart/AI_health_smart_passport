<?php
session_start();
// 1. ALWAYS include your database connection first
include('db.php'); 

// 2. Security Check - Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Live Bed Management - AI Health Passport</title>
    <style>
        :root { --primary: #003399; --accent: #ffcc00; --bg: #e8f5e9; --sidebar: #1a1a2e; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; background: var(--bg); display: flex; }
        .sidebar { width: 260px; background: var(--sidebar); color: white; height: 100vh; position: fixed; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        .nav-links { list-style: none; padding: 0; margin-top: 20px; }
        .nav-links li a { color: #f4f4f4; text-decoration: none; padding: 15px 25px; display: block; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .nav-links li.active a { background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent); }
        .top-bar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; margin: 20px; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        .count-badge { font-weight: bold; color: var(--primary); font-size: 1.1em; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="color: var(--accent); margin: 0;">AI Health Passport</h3>
        </div>
        <ul class="nav-links">
            <li><a href="dashboard.php">My Dashboard</a></li>
            <li><a href="index.php">Patient Records</a></li>
            <li class="active"><a href="bed_management.php">Bed Management</a></li>
            <li><a href="consultations.php">Consultations</a></li>
            <li><a href="logout.php" style="color: #ff6666;">Sign Out</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <header class="top-bar">
            <span>Logged in as: <b><?php echo htmlspecialchars($username); ?></b></span>
            <button onclick="history.back()" style="cursor:pointer; padding: 5px 15px; border: 1px solid #ccc; border-radius: 4px; background: #fff;">&larr; Back</button>
        </header>

        <div class="card">
            <h3 style="color: var(--primary); margin-top: 0;">Live Bed Occupancy</h3>
            <p style="font-size: 0.9em; color: #666; margin-bottom: 20px;">
                <b>Note:</b> These numbers represent patients currently assigned to wards in the database.
            </p>
            
            <table>
                <thead>
                    <tr style="background: #f9f9f9;">
                        <th>Ward Name</th>
                        <th>Total Capacity</th>
                        <th>Live Occupancy</th>
                        <th>Available</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /** * LIVE QUERY: 
                     * This subquery counts the number of patients in the 'patients' table 
                     * whose 'assigned_ward' column matches the 'ward_name' in 'bed_management'.
                     */
                    $sql = "SELECT 
                                b.ward_name, 
                                b.total_beds, 
                                (SELECT COUNT(*) FROM patients WHERE assigned_ward = b.ward_name) AS live_count 
                            FROM bed_management b";
                    
                    $result = mysqli_query($conn, $sql);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $total = (int)$row['total_beds'];
                            $occupied = (int)$row['live_count'];
                            $available = $total - $occupied;
                            
                            // Safe percentage calculation to avoid division by zero
                            $percentage = ($total > 0) ? ($occupied / $total) * 100 : 0;

                            // Dynamic Status Indicator
                            if ($percentage >= 95) {
                                $status = "<b style='color:red;'>Full</b>";
                            } elseif ($percentage >= 80) {
                                $status = "<b style='color:orange;'>Near Limit</b>";
                            } else {
                                $status = "<b style='color:green;'>Normal</b>";
                            }

                            echo "<tr>
                                    <td>" . htmlspecialchars($row['ward_name']) . "</td>
                                    <td>{$total}</td>
                                    <td class='count-badge'>{$occupied}</td>
                                    <td>{$available}</td>
                                    <td>{$status}</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding: 30px;'>No ward data found. Please check your bed_management table.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>