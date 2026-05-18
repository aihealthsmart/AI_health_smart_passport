<?php
session_start();
include('db.php'); 

// 1. AUTHENTICATION CHECK
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. DEFINE VARIABLES
$username = $_SESSION['username'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? ''; 

// 3. SECURITY & CACHE
if (!isset($_SESSION['user_agent'])) {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
} else {
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_unset();
        session_destroy();
        header("Location: login.php?error=session_breach");
        exit();
    }
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// 3.5 DASHBOARD STATISTICS
$session_user_id = $_SESSION['user_id'] ?? 0; 
$attended_today = 0;

if ($user_role == 'doctor' || $user_role == 'nurse') {
    $stats_query = "SELECT COUNT(*) as attended_count FROM medical_records WHERE user_id = '$session_user_id' AND DATE(created_at) = CURDATE()";
    $stats_result = mysqli_query($conn, $stats_query);
    if ($stats_result) {
        $stats_row = mysqli_fetch_assoc($stats_result);
        $attended_today = $stats_row['attended_count'];
    }
}

// 4. HANDLE REGISTRATION LOGIC
if (isset($_POST['register']) && ($user_role == 'doctor' || $user_role == 'nurse')) {
    $name = mysqli_real_escape_string($conn, $_POST['p_name']);
    $id_num = mysqli_real_escape_string($conn, $_POST['nam_id']);
    $age = mysqli_real_escape_string($conn, $_POST['p_age']); 
    $plain_password = $_POST['p_pass']; 
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
    
    $check_id = mysqli_query($conn, "SELECT national_id FROM patients WHERE national_id = '$id_num'");
    if(mysqli_num_rows($check_id) > 0) {
        $msg = "Error: A patient with this ID is already registered.";
    } else {
        $reg_sql = "INSERT INTO patients (national_id, full_name, age, password) VALUES ('$id_num', '$name', '$age', '$hashed_password')";
        if (mysqli_query($conn, $reg_sql)) {
            $msg = "Patient Registered Successfully!";
        } else {
            $msg = "Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Health Dashboard</title>
    <style>
        :root {
            --primary: #003399;
            --accent: #ffcc00;
            --bg: #e8f5e9;
            --sidebar: #1a1a2e;
        }
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; background: var(--bg); display: flex; }
        .sidebar { width: 260px; background: var(--sidebar); color: white; height: 100vh; position: fixed; overflow-y: auto; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); min-height: 100vh; }
        .nav-links { list-style: none; padding: 0; margin-top: 20px; }
        .nav-links li a { color: #f4f4f4; text-decoration: none; padding: 15px 25px; display: block; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .nav-links li a:hover, .nav-links li.active a { background: rgba(255,255,255,0.1); border-left: 4px solid var(--accent); }
        .top-bar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card { background: white; margin: 20px; padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stat-card { background: white; padding: 15px 25px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #28a745; display: inline-block; margin: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 15px; border-bottom: 2px solid #eee; color: #555; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        .btn-action { text-decoration: none; font-weight: bold; padding: 5px 10px; border-radius: 4px; }
        .btn-view { color: var(--primary); }
        .btn-edit { color: #28a745; }
        .btn-delete { color: #dc3545; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <div style="padding: 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h3 style="color: var(--accent); margin: 0;">AI Health Passport</h3>
        </div>
        <ul class="nav-links">
            <?php if ($user_role == 'nurse' || $user_role == 'doctor'): ?>
                <li><a href="dashboard.php">My Dashboard</a></li>
                <li class="<?php echo !isset($_GET['action']) && basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <a href="index.php">Patient Records</a>
                </li>
                <li class="<?php echo (isset($_GET['action']) && $_GET['action'] == 'register') ? 'active' : ''; ?>">
                    <a href="index.php?action=register">Register New Patient</a>
                </li>
                <li><a href="consultations.php">Consultations</a></li>
                <li><a href="bed_management.php">Bed Management</a></li>
                <li><a href="outbreak_reports.php">Outbreak Tracking</a></li>
                <li><a href="attendance_reports.php">Daily Attendance Report</a></li>

            <?php elseif ($user_role == 'patient'): ?>
                <li><a href="my_health.php">My Health Passport</a></li>
                <li><a href="appointments.php">My Appointments</a></li>
                <li><a href="prescriptions.php">My Prescriptions</a></li>
                <li><a href="lab_results.php">Lab Results</a></li>
            <?php endif; ?>

            <li><a href="logout.php" style="color: #ff4d4d;">Sign Out</a></li>
        </ul>
    </nav>

    <div class="main-content">
        <header class="top-bar">
            <span>Logged in as: <b><?php echo htmlspecialchars($username); ?> (<?php echo ucfirst($user_role); ?>)</b></span>
            <button onclick="history.back()" style="cursor:pointer; padding: 5px 15px;">&larr; Back</button>
        </header>

        <?php if ($user_role == 'doctor' || $user_role == 'nurse'): ?>
            <div class="stat-card">
                <p style="margin: 0; color: #666; font-size: 14px; font-weight: bold;">Attended Today</p>
                <h2 style="margin: 5px 0 0 0; color: var(--primary);"><?php echo $attended_today; ?></h2>
            </div>
        <?php endif; ?>

        <?php 
        if (isset($_GET['action']) && $_GET['action'] == 'register' && ($user_role == 'doctor' || $user_role == 'nurse')): 
        ?>
            <div class="card" style="max-width: 600px; margin: 20px auto;">
                <h3 style="color: var(--primary);">Register New Patient</h3>
                <?php if(isset($msg)) echo "<p style='color:green;'>$msg</p>"; ?>
                <form method="POST" action="index.php?action=register">
                    <input type="text" name="p_name" placeholder="Full Name" required style="width:100%; padding:10px; margin-bottom:10px;">
                    <input type="number" name="p_age" placeholder="Age" required style="width:100%; padding:10px; margin-bottom:10px;">
                    <input type="text" name="nam_id" placeholder="Namibian ID" required style="width:100%; padding:10px; margin-bottom:10px;">
                    <input type="password" name="p_pass" placeholder="Password" required style="width:100%; padding:10px; margin-bottom:15px;">
                    <button type="submit" name="register" style="width:100%; padding:12px; background:green; color:white; border:none; border-radius:5px; cursor:pointer;">Register Patient</button>
                </form>
                <br>
                <a href="index.php" style="text-decoration:none; color:#666;">&larr; Back to Patient List</a>
            </div>

        <?php else: ?>
            <div class="card">
                <h3 style="color: var(--primary);">Patient Records List</h3>
                
                <?php if ($user_role != 'patient'): ?>
                <div class="search-container" style="margin-bottom: 20px; display: flex; align-items: center;">
                    <input type="text" id="recordSearch" placeholder="Search by ID or Name..." 
                           style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;">
                    <button style="background: none; border: none; cursor: pointer; margin-left: -35px;">🔍</button>
                </div>
                <?php endif; ?>

                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Full Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($user_role == 'patient') {
                            $stmt = $conn->prepare("SELECT * FROM patients WHERE national_id = ?");
                            $stmt->bind_param("s", $username);
                            $stmt->execute();
                            $res = $stmt->get_result();
                        } else {
                            $res = mysqli_query($conn, "SELECT * FROM patients ORDER BY full_name ASC");
                        }

                        if ($res && mysqli_num_rows($res) > 0) {
                            while($row = mysqli_fetch_assoc($res)) {
                                $p_id = htmlspecialchars($row['national_id']);
                                $p_name = htmlspecialchars($row['full_name']);
                                
                                echo "<tr>
                                        <td>$p_id</td>
                                        <td>$p_name</td>
                                        <td>
                                            <a href='view_file.php?id=$p_id' class='btn-action btn-view'>View File</a>";
                                
                                if ($user_role == 'doctor' || $user_role == 'nurse') {
                                    echo " | <a href='edit_record.php?id=$p_id' class='btn-action btn-edit'>Edit</a>";
                                }
                                if ($user_role == 'doctor') {
                                    echo " | <a href='delete_user.php?id=$p_id' class='btn-action btn-delete' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
                                }
                                
                                echo "</td></tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align:center;'>No records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

<script>
// Keep your existing search functionality
const searchInput = document.getElementById('recordSearch');
if(searchInput) {
    searchInput.addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.querySelector("table").getElementsByTagName("tr");

        for (let i = 1; i < rows.length; i++) { 
            let idColumn = rows[i].getElementsByTagName("td")[0];
            let nameColumn = rows[i].getElementsByTagName("td")[1];
            
            if (idColumn || nameColumn) {
                let textValue = (idColumn.textContent || idColumn.innerText) + 
                                (nameColumn.textContent || nameColumn.innerText);
                
                if (textValue.toUpperCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });
}
</script>

<?php if (isset($_SESSION['role'])): ?>
    <?php include('assistant_interface.php'); ?>
<?php endif; ?>

</body>
</html>