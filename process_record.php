<?php
include('db.php');
include('ai_engine.php'); // Include the AI logic
session_start();

if (isset($_POST['search_id'])) {
    $search = $_POST['search_id'];
    $sql = "SELECT * FROM patients WHERE national_id = '$search'";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $p_id = $row['national_id'];
        
        // 1. Get the most recent symptoms from history to analyze
        $history_query = mysqli_query($conn, "SELECT symptoms FROM medical_records WHERE patient_id = '$p_id' ORDER BY created_at DESC LIMIT 1");
        $last_record = mysqli_fetch_assoc($history_query);
        $symptoms_to_analyze = $last_record['symptoms'] ?? "";

        // 2. Run AI Prediction
        $ai_result = get_ai_prediction($symptoms_to_analyze);

        echo "<h2>Patient: " . $row['full_name'] . "</h2>";
        
        // DISPLAY AI PREDICTION BOX
        echo "<div style='background: #f0f7ff; border-left: 5px solid #007bff; padding: 15px; margin: 20px 0;'>";
        echo "<h3><i class='fa fa-robot'></i> AI Diagnostic Assistant</h3>";
        echo "<p><b>Predicted Condition:</b> " . $ai_result['disease'] . "</p>";
        echo "<p><b>Suggested Medication:</b> " . $ai_result['medication'] . "</p>";
        echo "<p><small>Confidence Level: " . $ai_result['confidence'] . "</small></p>";
        echo "</div>";

        // 3. Form for Doctor to confirm/edit prescription
        echo "<h3>Finalize Prescription</h3>";
        echo "<form method='POST' action='save_treatment.php'>";
        echo "<input type='hidden' name='p_id' value='$p_id'>";
        echo "<textarea name='notes' style='width:100%; height:100px;'>AI Suggestion: " . $ai_result['medication'] . "</textarea><br>";
        echo "<button type='submit' name='save_btn' style='background: blue; color: white;'>Update Passport</button>";
        echo "</form>";
// --- START HISTORY SECTION ---
echo "<h3>Past Treatments:</h3>";

// Ensure $p_id matches the Namibian ID found during the search
$history_sql = "SELECT * FROM medical_records WHERE patient_id = '$p_id' ORDER BY created_at DESC";
$history_res = mysqli_query($conn, $history_sql);

if (mysqli_num_rows($history_res) > 0) {
    while($row = mysqli_fetch_assoc($history_res)) {
        echo "<div style='border-bottom: 1px solid #ddd; padding: 10px;'>";
        echo "<strong>[" . $row['created_at'] . "]</strong> " . $row['treatment'];
        
        // Show the symptoms that led to this treatment
        if(!empty($row['symptoms'])) {
            echo "<br><small>Symptoms recorded: " . $row['symptoms'] . "</small>";
        }
        echo "</div>";
    }
} else {
    echo "<p style='color: gray;'>No previous treatments found in this passport.</p>";
}
// --- END HISTORY SECTION ---
    }
}
?>