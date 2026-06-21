<?php
session_start();
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
// Changed to $_REQUEST so it bypasses POST filters
$media_id = intval($_REQUEST['media_id'] ?? 0);
$type = $conn->real_escape_string($_REQUEST['type'] ?? 'movie');
$title = $conn->real_escape_string($_REQUEST['title'] ?? 'Unknown');

if ($media_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
    exit;
}

$check = $conn->query("SELECT id FROM watchlist WHERE user_id=$user_id AND media_id=$media_id AND type='$type'");

if ($check && $check->num_rows > 0) {
    $conn->query("DELETE FROM watchlist WHERE user_id=$user_id AND media_id=$media_id AND type='$type'");
    echo json_encode(['status' => 'removed']);
} else {
    $conn->query("INSERT INTO watchlist (user_id, media_id, type, title) VALUES ($user_id, $media_id, '$type', '$title')");
    echo json_encode(['status' => 'added']);
}
?>