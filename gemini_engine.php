<?php
header('Content-Type: application/json');

if (isset($_POST['symptoms'])) {
    $symptoms = $_POST['symptoms'];
    $apiKey = "YOUR_GEMINI_API_KEY_HERE"; // Get this from Google AI Studio
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    // The Prompt: Telling the AI to behave like a medical assistant
    $payload = [
        "contents" => [[
            "parts" => [[
                "text" => "Act as a medical diagnostic assistant. Based on these symptoms: '$symptoms', provide a likely medical diagnosis in 5 words or less. Do not include conversational text."
            ]]
        ]]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $prediction = $data['candidates'][0]['content']['parts'][0]['text'] ?? "Unable to predict";

    echo json_encode(['prediction' => trim($prediction)]);
}
?>