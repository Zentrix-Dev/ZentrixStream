<?php
session_start();
// Turn ON error reporting so we can catch database issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/plain'); // Use plain text to bypass strict JSON filters
ob_start(); // Start capturing any hidden PHP errors

try {
    require '../db.php';

    if (!isset($_SESSION['user_id'])) {
        $response = ['status' => 'error', 'message' => 'Not logged in'];
    } else {
        $user_id = intval($_SESSION['user_id']);
        $action = $_REQUEST['action'] ?? '';

        if ($action === 'create_room') {
            $media_id = intval($_REQUEST['media_id']);
            $media_type = $conn->real_escape_string($_REQUEST['media_type']);
            $season = intval($_REQUEST['season'] ?? 1);
            $episode = intval($_REQUEST['episode'] ?? 1);
            
            $room_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
            
            $stmt = $conn->prepare("INSERT INTO watch_rooms (room_code, media_id, media_type, season, episode, host_id, play_status) VALUES (?, ?, ?, ?, ?, ?, 'waiting')");
            if (!$stmt) {
                $response = ['status' => 'error', 'message' => 'SQL Error: ' . $conn->error];
            } else {
                $stmt->bind_param("sisiii", $room_code, $media_id, $media_type, $season, $episode, $user_id);
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'room_code' => $room_code];
                } else {
                    $response = ['status' => 'error', 'message' => 'SQL Execute Error: ' . $stmt->error];
                }
                $stmt->close();
            }
        } 
        elseif ($action === 'sync') {
            $room_code = $conn->real_escape_string($_REQUEST['room_code']);
            
            // Add user to room
            $conn->query("INSERT INTO room_users (room_code, user_id) VALUES ('$room_code', $user_id) ON DUPLICATE KEY UPDATE last_active = CURRENT_TIMESTAMP");
            
            $room_data = $conn->query("SELECT host_id, play_status, season, episode FROM watch_rooms WHERE room_code = '$room_code'")->fetch_assoc();
            
            if (!$room_data) {
                $response = ['status' => 'error', 'message' => 'Room closed.'];
            } else {
                $users = [];
                $u_query = $conn->query("SELECT u.id, u.username, ru.is_ready FROM room_users ru JOIN users u ON ru.user_id = u.id WHERE ru.room_code = '$room_code' ORDER BY ru.id ASC");
                if($u_query) { while ($r = $u_query->fetch_assoc()) $users[] = $r; }
                
                $chat = [];
                $c_query = $conn->query("SELECT m.message, m.created_at, u.username, m.user_id FROM room_chat m JOIN users u ON m.user_id = u.id WHERE m.room_code = '$room_code' ORDER BY m.id ASC");
                if($c_query) { while ($r = $c_query->fetch_assoc()) $chat[] = $r; }

                $response = [
                    'status' => 'success',
                    'play_status' => $room_data['play_status'] ?? 'waiting',
                    'current_season' => intval($room_data['season']),
                    'current_episode' => intval($room_data['episode']),
                    'host_id' => $room_data['host_id'] ?? 0,
                    'users' => $users,
                    'chat' => $chat
                ];
            }
        }
        elseif ($action === 'update_video_config') {
            $room_code = $conn->real_escape_string($_REQUEST['room_code']);
            $server = $conn->real_escape_string($_REQUEST['server']);
            $season = intval($_REQUEST['season'] ?? 1);
            $episode = intval($_REQUEST['episode'] ?? 1);
            
            $conn->query("UPDATE watch_rooms SET play_status = '$server', season = $season, episode = $episode WHERE room_code = '$room_code'");
            $response = ['status' => 'success'];
        }
        elseif ($action === 'send_message') {
            $room_code = $conn->real_escape_string($_REQUEST['room_code']);
            $msg = $conn->real_escape_string(htmlspecialchars($_REQUEST['message']));
            if (!empty($msg)) {
                $conn->query("INSERT INTO room_chat (room_code, user_id, message) VALUES ('$room_code', $user_id, '$msg')");
            }
            $response = ['status' => 'success'];
        }
        elseif ($action === 'toggle_ready') {
            $room_code = $conn->real_escape_string($_REQUEST['room_code']);
            $conn->query("UPDATE room_users SET is_ready = NOT is_ready WHERE room_code = '$room_code' AND user_id = $user_id");
            $response = ['status' => 'success'];
        }
        else {
            $response = ['status' => 'error', 'message' => 'Unknown action requested.'];
        }
    }
} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()];
}

// Catch ANY hidden PHP errors (like missing tables)
$php_errors = ob_get_clean();
if (!empty($php_errors)) {
    $response['php_errors'] = strip_tags($php_errors);
}

// WRAP THE DATA IN AN INDESTRUCTIBLE SHIELD
echo "---ZENTRIX-START---";
echo json_encode($response);
echo "---ZENTRIX-END---";
exit;
?>