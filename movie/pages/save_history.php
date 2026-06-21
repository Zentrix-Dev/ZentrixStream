<?php
session_start();
error_reporting(0); 
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php';

if (!isset($_SESSION['user_id'])) exit("Not logged in");

$user_id = intval($_SESSION['user_id']);

$media_id = isset($_GET['media_id']) ? intval($_GET['media_id']) : 0;
$media_type = isset($_GET['media_type']) ? $_GET['media_type'] : 'movie';
$season = isset($_GET['season']) ? intval($_GET['season']) : 1;
$episode = isset($_GET['episode']) ? intval($_GET['episode']) : 1;
$progress = isset($_GET['progress']) ? intval($_GET['progress']) : 0; 

if ($media_id === 0) exit("Invalid ID");

// Create table if missing
$conn->query("CREATE TABLE IF NOT EXISTS `movie_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `media_type` varchar(20) NOT NULL DEFAULT 'movie',
  `season` int(11) DEFAULT 1,
  `episode` int(11) DEFAULT 1,
  `watched_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)");

// Safely add column ONLY if it doesn't already exist
$checkCol = $conn->query("SHOW COLUMNS FROM movie_history LIKE 'progress'");
if ($checkCol && $checkCol->num_rows == 0) {
    $conn->query("ALTER TABLE movie_history ADD COLUMN progress INT(3) DEFAULT 0");
}

$stmt = $conn->prepare("SELECT id FROM movie_history WHERE user_id=? AND media_id=? AND media_type=?");
if ($stmt) {
    $stmt->bind_param("iis", $user_id, $media_id, $media_type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Safely update to the highest progress percentage
        $up_stmt = $conn->prepare("UPDATE movie_history SET season=?, episode=?, watched_at=CURRENT_TIMESTAMP, progress=GREATEST(IFNULL(progress, 0), ?) WHERE user_id=? AND media_id=? AND media_type=?");
        $up_stmt->bind_param("iiiiis", $season, $episode, $progress, $user_id, $media_id, $media_type);
        $up_stmt->execute();
        $up_stmt->close();
    } else {
        $in_stmt = $conn->prepare("INSERT INTO movie_history (user_id, media_id, media_type, season, episode, progress) VALUES (?, ?, ?, ?, ?, ?)");
        $in_stmt->bind_param("iisiii", $user_id, $media_id, $media_type, $season, $episode, $progress);
        $in_stmt->execute();
        $in_stmt->close();
    }
    $stmt->close();
    echo "Progress ($progress%) Saved";
}
?>