<?php
error_reporting(0);
require '../db.php';
header('Content-Type: application/json');

$anime_id = isset($_REQUEST['anime_id']) ? (int)$_REQUEST['anime_id'] : 0;
$episode = isset($_REQUEST['episode']) ? (int)$_REQUEST['episode'] : 0;

$stmt = $conn->prepare("
    SELECT c.comment_text, c.created_at, IFNULL(u.username, 'User') as username 
    FROM anime_comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.anime_id = ? AND c.episode = ? 
    ORDER BY c.created_at DESC
");

$comments = [];
if ($stmt) {
    $stmt->bind_param("ii", $anime_id, $episode);
    $stmt->execute();
    
    // Using bind_result for maximum compatibility
    $stmt->bind_result($comment_text, $created_at, $username);
    while ($stmt->fetch()) {
        $comments[] = [
            'comment_text' => htmlspecialchars($comment_text),
            'created_at' => $created_at,
            'username' => htmlspecialchars($username)
        ];
    }
    $stmt->close();
}

echo json_encode($comments);
?>