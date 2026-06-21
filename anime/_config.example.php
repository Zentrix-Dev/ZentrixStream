<?php
// _config.php
session_start();

// 1. Database Connection
$dbHost = '';  // Your database hostname
$dbUser = '';  // Your database username
$dbPass = '';  // Your database password
$dbName = '';  // Your database name

$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Global Variables
$websiteTitle = "ZENTRIX STREAM";
$websiteUrl = "http://zentrixstream.gt.tc";  // Your website url
$version = "2.0.0";

// 3. AniList GraphQL Fetcher Function
function fetchAniList($query, $variables = []) {
    $url = 'https://graphql.anilist.co';
    $data = json_encode(['query' => $query, 'variables' => $variables]);
    
    $options = [
        'http' => [
            'header'  => [
                "Content-type: application/json",
                "Accept: application/json"
            ],
            'method'  => 'POST',
            'content' => $data,
            'ignore_errors' => true // Allows reading error responses from AniList
        ]
    ];
    
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return json_decode($result, true);
}
?>
