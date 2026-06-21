<?php
// 1. Database Configuration
$db_host = "";      // Your database hostname
$db_user = "";      // Your database username
$db_pass = "";          // Your database password
$db_name = "";   // Your database name

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 2. Global Website Settings
$websiteTitle = "ZENTRIX STREAM";
$websiteUrl = ""; // Change this when you go live
$version = "1.0.0";

// 3. Timezone & Sessions
date_default_timezone_set('Asia/Kolkata'); // Adjust to your local timezone
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>ion_status() === PHP_SESSION_NONE) {
    session_start();
}
?> [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $payload = json_encode([
        'query' => $query,
        'variables' => $variables,
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

// 4. Timezone & Sessions
date_default_timezone_set('Asia/Kolkata'); // Adjust to your local timezone
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>ion_status() === PHP_SESSION_NONE) {
    session_start();
}
?>