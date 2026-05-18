<?php
session_start();
include('db.php');

// --- 1. GLOBAL SECURITY & HIJACK PROTECTION ---
if (!isset($_SESSION['username']) || ($_SESSION['role'] != 'doctor' && $_SESSION['role'] != 'nurse')) {
    header("Location: login.php");
    exit();
}

// --- 2. CAPTURE PATIENT ID FIRST ---
if (isset($_GET['id'])) {
    $p_id = mysqli_real_escape_string($conn, $_GET['id']);
} else {
    die("Error: No Patient ID provided.");
}

// Fetch Patient Details
$patient_query = mysqli_query($conn, "SELECT * FROM patients WHERE national_id = '$p_id'");
$patient = mysqli_fetch_assoc($patient_query);

// Handle Form Submission
if (isset($_POST['save_record'])) {
    $symptoms = mysqli_real_escape_string($conn, $_POST['symptoms']);
    $diagnosis = mysqli_real_escape_string($conn, $_POST['diagnosis']);
    $provider = $_SESSION['username'];

    $insert = "INSERT INTO medical_records (patient_id, symptoms, diagnosis, attended_by, date_recorded) 
               VALUES ('$p_id', '$symptoms', '$diagnosis', '$provider', NOW())";
    
    if (mysqli_query($conn, $insert)) {
        $today = date('Y-m-d');
        mysqli_query($conn, "INSERT INTO daily_stats (stat_date, total_attended) VALUES ('$today', 1) 
                             ON DUPLICATE KEY UPDATE total_attended = total_attended + 1");
        
        $_SESSION['msg'] = "Record Saved Successfully!";
        header("Location: edit_record.php?id=" . urlencode($p_id));
        exit();
    }
}

$display_msg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Patient Record | Smart Health</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 40px auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-back { 
            display: inline-block; background: #5f6368; color: white; padding: 8px 15px; 
            text-decoration: none; border-radius: 4px; margin-bottom: 15px; font-size: 14px; font-weight: bold;
        }
        .ai-status-text { color: #0078d4; font-weight: 600; font-size: 14px; margin-top: 8px; }
        textarea { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        .btn-save { background: #003399; color: white; padding: 12px 25px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 20px; }
        label { font-weight: bold; display: block; margin-top: 15px; margin-bottom: 5px; }
    </style>
</head>
<body>

<button onclick="history.back()" style="background: #5f6368; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 10px; font-weight: bold;">
    &larr; Back
</button>

<div class="container">
    <a href="index.php" class="btn-back">&larr; Return to Dashboard</a>
    
    <h2>Consultation for: <?php echo htmlspecialchars($patient['full_name'] ?? 'Unknown'); ?></h2>
    <p>National ID: <strong><?php echo htmlspecialchars($p_id); ?></strong></p>
    <hr>

    <h3>New Medical Entry</h3>
    <?php if($display_msg): ?>
        <p style="color: green; font-weight:bold;"><?php echo $display_msg; ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <label>Signs and Symptoms:</label>
        <textarea name="symptoms" id="symptomsInput" rows="4" onkeyup="predictAI(this.value)" placeholder="Enter symptoms (e.g., headache, high fever, chills)..."></textarea>
        
        <label>Diagnosis / Medical Prediction: <span id="aiStatus" class="ai-status-text"></span></label>
        <textarea name="diagnosis" id="diagnosisBox" rows="2" placeholder="AI will suggest a diagnosis here..."></textarea>
        
        <button type="submit" name="save_record" class="btn-save">Finalize and Save Record</button>
    </form>
</div>

<script>
let typingTimer;
const doneTypingInterval = 1000; // Wait 1 second after user stops typing

function predictAI(val) {
    const status = document.getElementById('aiStatus');
    const diagBox = document.getElementById('diagnosisBox');
    
    clearTimeout(typingTimer);
    
    if (val.length > 5) {
        status.innerHTML = "(AI is analyzing symptoms...)";
        
        typingTimer = setTimeout(() => {
            fetch('gemini_engine.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'symptoms=' + encodeURIComponent(val)
            })
            .then(response => response.json())
            .then(data => {
                if(data.prediction) {
                    diagBox.value = data.prediction;
                    status.innerHTML = "(AI Prediction Updated)";
                } else {
                    status.innerHTML = "(AI could not determine diagnosis)";
                }
            })
            .catch(error => {
                console.error('Error:', error);
                status.innerHTML = "(AI Connection Offline)";
            });
        }, doneTypingInterval);
    } else {
        status.innerHTML = "";
    }
}
</script>

</body>
</html>