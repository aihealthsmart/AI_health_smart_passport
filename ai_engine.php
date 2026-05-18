<?php
session_start();
include('db.php');

// 4.2 Security: Only logged-in users can query the data
if (!isset($_SESSION['username'])) {
    die(json_encode(['error' => 'Unauthorized access']));
}

/**
 * Medical Knowledge Base Function
 * Integrated to provide clinical insights via the chat interface
 */
function get_ai_prediction($symptoms) {
    $symptoms = strtolower($symptoms);
    $conditions = [
        "Malaria" => ["keywords" => ["fever", "chills", "sweating", "headache"], "medication" => "Artemether-lumefantrine (Coartem)"],
        "Influenza (Flu)" => ["keywords" => ["cough", "sore throat", "runny nose", "body aches"], "medication" => "Oseltamivir (Tamiflu) and Rest"],
        "Hypertension" => ["keywords" => ["high blood pressure", "dizziness", "blurred vision"], "medication" => "Amlodipine or Lisinopril"],
        "Diabetes" => ["keywords" => ["frequent urination", "excessive thirst", "blurred vision", "fatigue"], "medication" => "Metformin"]
    ];

    foreach ($conditions as $disease => $data) {
        foreach ($data['keywords'] as $keyword) {
            if (strpos($symptoms, $keyword) !== false) {
                return [
                    "disease" => $disease,
                    "medication" => $data['medication'],
                    "confidence" => "High (Based on symptoms)"
                ];
            }
        }
    }
    return [
        "disease" => "Inconclusive",
        "medication" => "Requires further laboratory testing",
        "confidence" => "Low"
    ];
}

$userInput = strtolower(mysqli_real_escape_string($conn, $_POST['query']));
$response = [];

// 4.3 (a) Query: Total Patients
if (strpos($userInput, 'how many patients') !== false) {
    $sql = "SELECT COUNT(*) as total FROM patients";
    $res = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($res);
    $response = [
        'type' => 'text',
        'content' => "There are currently <strong>" . $data['total'] . "</strong> patients registered in the system."
    ];
}

// 4.3 (b) Query: Top Doctor this week (Chart)
elseif (strpos($userInput, 'most appointments') !== false || strpos($userInput, 'doctor') !== false) {
    $sql = "SELECT doctor_name, COUNT(*) as count FROM appointments 
            WHERE YEARWEEK(appointment_date, 1) = YEARWEEK(CURDATE(), 1) 
            GROUP BY doctor_name ORDER BY count DESC LIMIT 5";
    $res = mysqli_query($conn, $sql);
    $labels = []; $counts = [];
    while($row = mysqli_fetch_assoc($res)) {
        $labels[] = $row['doctor_name'];
        $counts[] = $row['count'];
    }
    $response = [
        'type' => 'chart',
        'labels' => $labels,
        'data' => $counts,
        'title' => 'Appointments per Doctor (Current Week)'
    ];
}

// 4.3 (c) Query: Last 7 Days Admissions (Table)
elseif (strpos($userInput, 'admitted') !== false || strpos($userInput, 'last 7 days') !== false) {
    $sql = "SELECT patient_id, admission_date, ward, condition_summary 
            FROM admissions 
            WHERE admission_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ORDER BY admission_date DESC";
    $res = mysqli_query($conn, $sql);
    $tableData = [];
    while($row = mysqli_fetch_assoc($res)) {
        $tableData[] = $row;
    }
    $response = [
        'type' => 'table',
        'content' => $tableData
    ];
} 

/**
 * NEW: AI Symptom Checker Integration
 * Detects if the user is asking about illness or symptoms
 */
elseif (strpos($userInput, 'feel') !== false || strpos($userInput, 'symptom') !== false || strpos($userInput, 'pain') !== false) {
    $prediction = get_ai_prediction($userInput);
    $response = [
        'type' => 'text',
        'content' => "<strong>AI Clinical Insight:</strong><br>
                      Potential Condition: " . $prediction['disease'] . "<br>
                      Suggested Action: " . $prediction['medication'] . "<br>
                      Confidence: " . $prediction['confidence']
    ];
} 

else {
    $response = ['type' => 'text', 'content' => "I'm sorry, I couldn't interpret that. Try asking about patient counts, recent admissions, or describe a patient's symptoms."];
}

echo json_encode($response);