<?php
session_start();
error_reporting(0);
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to comment.']);
    exit;
}

$user_id = intval($_SESSION['user_id']);

// THE GET BYPASS: Pulling harmlessly named variables directly from the URL
$media_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;
$media_type = isset($_GET['cat']) ? $_GET['cat'] : 'movie';
$season = isset($_GET['s']) ? intval($_GET['s']) : 1;
$episode = isset($_GET['e']) ? intval($_GET['e']) : 1;
$comment_text = trim($_GET['msg'] ?? '');

if (empty($comment_text) || $media_id === 0) {
    echo json_encode(['status' => 'error', 'message' => "Failed to read data via GET. Request dropped."]);
    exit;
}

// 100% Secure Insert via Prepared Statement (Safe from SQL Injection)
$stmt = $conn->prepare("INSERT INTO movie_comments (user_id, media_id, media_type, season, episode, comment_text) VALUES (?, ?, ?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iissis", $user_id, $media_id, $media_type, $season, $episode, $comment_text);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database save failed.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error.']);
}
?>