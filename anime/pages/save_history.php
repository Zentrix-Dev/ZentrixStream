<?php
session_start();
error_reporting(0);
require '../db.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];
$anime_id = isset($_REQUEST['anime_id']) ? (int)$_REQUEST['anime_id'] : 0;
$episode = isset($_REQUEST['episode']) ? (int)$_REQUEST['episode'] : 1;

if ($anime_id === 0) exit;

// 1. FAILSAFE: Auto-create history table if missing
$conn->query("CREATE TABLE IF NOT EXISTS watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    media_id INT NOT NULL,
    episode INT NOT NULL DEFAULT 1,
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Auto-detect history table
$table = "watch_history";
$col_id = "media_id";
$checkTable = $conn->query("SHOW TABLES LIKE 'anime_history'");
if ($checkTable && $checkTable->num_rows > 0) { 
    $table = "anime_history"; 
    $col_id = "anime_id";
}

// 2. Delete old record
$del = $conn->prepare("DELETE FROM $table WHERE user_id = ? AND $col_id = ?");
if ($del) {
    $del->bind_param("ii", $user_id, $anime_id);
    $del->execute();
    $del->close();
}

// 3. Insert new record (pushes it to the top of Continue Watching)
$ins = $conn->prepare("INSERT INTO $table (user_id, $col_id, episode) VALUES (?, ?, ?)");
if ($ins) {
    $ins->bind_param("iii", $user_id, $anime_id, $episode);
    $ins->execute();
    $ins->close();
}
?>