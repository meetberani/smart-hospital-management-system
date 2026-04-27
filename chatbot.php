<?php

$apiKey = "AIzaSyBV-OjPbuXTfslpyCfOmQmpu6HoTF0oRn8";

if(isset($_POST['message'])){

    $userMsg = $_POST['message'];

    // ✅ UPDATED MODEL (WORKING)
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=".$apiKey;

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "You are a hospital assistant. Give short helpful answers.\nUser: ".$userMsg]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        echo "Curl Error: " . curl_error($ch);
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if(isset($result['candidates'][0]['content']['parts'][0]['text'])){
        echo $result['candidates'][0]['content']['parts'][0]['text'];
    } else {
        echo "API Error: ";
        print_r($result);
    }

}
?>