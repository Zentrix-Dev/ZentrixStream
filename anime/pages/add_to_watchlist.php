<?php
session_start();
error_reporting(0);
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit;
}

// FAILSAFE: Auto-create watchlist table
$conn->query("CREATE TABLE IF NOT EXISTS watchlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    media_id INT NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'anime',
    title VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$user_id = $_SESSION['user_id'];
$media_id = isset($_REQUEST['media_id']) ? (int)$_REQUEST['media_id'] : 0;
$type = $_REQUEST['type'] ?? 'anime';
$title = $_REQUEST['title'] ?? 'Unknown';

if ($media_id === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM watchlist WHERE user_id = ? AND media_id = ? AND type = ?");
if ($stmt) {
    $stmt->bind_param("iis", $user_id, $media_id, $type);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $del = $conn->prepare("DELETE FROM watchlist WHERE user_id = ? AND media_id = ? AND type = ?");
        $del->bind_param("iis", $user_id, $media_id, $type);
        $del->execute();
        echo json_encode(['status' => 'removed']);
    } else {
        $stmt->close();
        $ins = $conn->prepare("INSERT INTO watchlist (user_id, media_id, type, title) VALUES (?, ?, ?, ?)");
        $ins->bind_param("iiss", $user_id, $media_id, $type, $title);
        $ins->execute();
        echo json_encode(['status' => 'added']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'DB Error']);
}
?>