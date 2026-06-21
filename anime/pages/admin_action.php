<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$role_check = $conn->query("SELECT role FROM users WHERE id = $user_id");
$user_data = $role_check->fetch_assoc();

if (!$user_data || $user_data['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access Denied. Admin only.']);
    exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'delete_comment') {
    $comment_id = intval($_POST['comment_id'] ?? 0);
    // Updated to target anime_comments table
    $stmt = $conn->prepare("DELETE FROM anime_comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => 'Failed to delete comment.']);
    $stmt->close();
} 
elseif ($action === 'delete_user') {
    $target_user_id = intval($_POST['user_id'] ?? 0);
    if ($target_user_id === $user_id) exit(json_encode(['status' => 'error', 'message' => 'You cannot delete yourself!']));
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $target_user_id);
    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => 'Failed to delete user.']);
    $stmt->close();
} 
elseif ($action === 'save_announcement') {
    $text = $conn->real_escape_string($_POST['text']);
    $active = intval($_POST['active']);
    
    // Failsafe: Ensure site_settings table exists
    $conn->query("CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(50) PRIMARY KEY, setting_value TEXT)");
    $conn->query("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('announcement', '')");
    $conn->query("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('announcement_active', '0')");
    
    $conn->query("UPDATE site_settings SET setting_value = '$text' WHERE setting_key = 'announcement'");
    $conn->query("UPDATE site_settings SET setting_value = '$active' WHERE setting_key = 'announcement_active'");
    echo json_encode(['status' => 'success']);
} 
elseif ($action === 'set_role') {
    $target_id = intval($_POST['user_id']);
    if ($target_id === $user_id) exit(json_encode(['status' => 'error', 'message' => 'You cannot change your own role!']));
    
    $new_role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $target_id);
    if ($stmt->execute()) echo json_encode(['status' => 'success']);
    else echo json_encode(['status' => 'error', 'message' => 'Failed to update role.']);
    $stmt->close();
}
else {
    echo json_encode(['status' => 'error', 'message' => 'Unknown action.']);
}
?>