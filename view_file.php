<?php
session_start();
include('db.php');

// 1. GLOBAL SECURITY & HIJACK PROTECTION
// (If you created a separate security.php, you can use: include('security.php');)
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// 2. PRIVACY LOGIC: Restrict Patient Access
$p_id = mysqli_real_escape_string($conn, $_GET['id']);
$user_role = $_SESSION['role'] ?? '';
$logged_in_user = $_SESSION['username'];

// If the user is a Patient, they can ONLY see the ID that matches their username
if ($user_role == 'patient' && $logged_in_user !== $p_id) {
    // Unauthorized access attempt! Redirect to dashboard
    header("Location: index.php?error=unauthorized_access");
    exit();
}

// 3. Browser/Device Integrity Check
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

// Clear cache to prevent back-button viewing after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// --- DATA FETCHING ---

// 4. Fetch Basic Patient Info
$patient_res = mysqli_query($conn, "SELECT * FROM patients WHERE national_id = '$p_id'");
$patient = mysqli_fetch_assoc($patient_res);

// 5. Fetch Medical History (All records)
$history_res = mysqli_query($conn, "SELECT * FROM medical_records WHERE patient_id = '$p_id' ORDER BY date_recorded DESC");

// 6. Get the Latest Entry for "Current Symptoms"
$latest_res = mysqli_query($conn, "SELECT * FROM medical_records WHERE patient_id = '$p_id' ORDER BY date_recorded DESC LIMIT 1");
$latest = mysqli_fetch_assoc($latest_res);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Medical Record: <?php echo htmlspecialchars($patient['full_name'] ?? 'Unknown'); ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f0f2f5; padding: 40px; }
        .passport-card { max-width: 700px; margin: auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border-top: 5px solid #003399; }
        .section { margin-bottom: 20px; padding: 15px; border-radius: 8px; background: #f9f9f9; }
        .section-title { font-size: 12px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 8px; }
        .data-text { font-size: 16px; color: #333; }
        .history-item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .history-item:last-child { border-bottom: none; }
        .symptoms-highlight { background: #fff9c4; border-left: 4px solid #fbc02d; }
        .back-link { text-decoration: none; color: #003399; font-weight: bold; display: inline-block; margin-top: 20px; }
    </style>
</head>
<body>

<div class="passport-card">
    <h2 style="color: #003399; margin-top: 0;">Medical Passport: <?php echo htmlspecialchars($patient['full_name'] ?? 'Not Found'); ?></h2>
    
    <div class="section">
        <div class="section-title">Patient Identification</div>
        <div class="data-text"><strong>National ID:</strong> <?php echo htmlspecialchars($p_id); ?></div>
    </div>

    <div class="section">
        <div class="section-title">Clinical History & Past Diagnoses</div>
        <div class="data-text">
            <?php if(mysqli_num_rows($history_res) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($history_res)): ?>
                    <div class="history-item">
                        <strong><?php echo date("d M Y", strtotime($row['date_recorded'])); ?>:</strong> 
                        <?php echo htmlspecialchars($row['diagnosis']); ?> 
                        <span style="font-size: 12px; color: #888;">(Ref: <?php echo htmlspecialchars($row['attended_by']); ?>)</span>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <span style="color: #999;">No medical history recorded.</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="section symptoms-highlight">
        <div class="section-title">Current Symptoms (Latest Update)</div>
        <div class="data-text">
            <?php if($latest): ?>
                <?php echo htmlspecialchars($latest['symptoms']); ?>
            <?php else: ?>
                <span style="color: #999;">No symptoms recorded.</span>
            <?php endif; ?>
        </div>
    </div>

    <br>
    <a href="index.php" class="back-link">← Return to Dashboard</a>
</div>

</body>
</html>