<?php
session_start();
error_reporting(0); // Suppresses HTML errors to prevent breaking JSON output
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to comment.']);
    exit;
}

// FAILSAFE: Auto-create the table if it is missing
$conn->query("CREATE TABLE IF NOT EXISTS anime_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    anime_id INT NOT NULL,
    episode INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$user_id = $_SESSION['user_id'];

// Read raw JSON as a final fallback
$input = json_decode(file_get_contents('php://input'), true);

// OMNI-CHANNEL CAPTURE: Checks POST first, then GET (URL), then JSON
$anime_id = !empty($_POST['anime_id']) ? (int)$_POST['anime_id'] : (!empty($_GET['anime_id']) ? (int)$_GET['anime_id'] : ($input['anime_id'] ?? 0));
$episode = !empty($_POST['episode']) ? (int)$_POST['episode'] : (!empty($_GET['episode']) ? (int)$_GET['episode'] : ($input['episode'] ?? 0));
$comment_text = !empty($_POST['comment_text']) ? trim($_POST['comment_text']) : (!empty($_GET['comment_text']) ? trim($_GET['comment_text']) : trim($input['comment_text'] ?? ''));

if ($anime_id === 0 || $episode === 0 || $comment_text === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data: Missing ID, Episode, or Text.']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO anime_comments (user_id, anime_id, episode, comment_text) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("iiis", $user_id, $anime_id, $episode, $comment_text);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save to database.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database prepare error.']);
}
?>