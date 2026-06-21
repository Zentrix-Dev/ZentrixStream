<?php
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php';
header('Content-Type: application/json');

$media_id = $_GET['media_id'] ?? 0;
$media_type = $_GET['media_type'] ?? 'movie';
$season = $_GET['season'] ?? 1;
$episode = $_GET['episode'] ?? 1;

$stmt = $conn->prepare("
    SELECT c.comment_text, c.created_at, u.username 
    FROM movie_comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.media_id = ? AND c.media_type = ? AND c.season = ? AND c.episode = ? 
    ORDER BY c.created_at DESC
");

$comments = [];
if ($stmt) {
    $stmt->bind_param("isii", $media_id, $media_type, $season, $episode);
    $stmt->execute();
    $stmt->bind_result($comment_text, $created_at, $username);
    while ($stmt->fetch()) {
        $comments[] = ['comment_text' => $comment_text, 'created_at' => $created_at, 'username' => $username];
    }
    $stmt->close();
}
echo json_encode($comments);
?>
