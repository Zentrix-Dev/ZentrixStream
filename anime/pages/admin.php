<?php
session_start();
require '../db.php';

// --- STRICT ADMIN SECURITY CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$role_check = $conn->query("SELECT role FROM users WHERE id = $user_id");
$user_data = $role_check->fetch_assoc();

if (!$user_data || $user_data['role'] !== 'admin') {
    die("<div style='background-color:#0b0b0b; color:#00f3ff; height:100vh; display:flex; align-items:center; justify-content:center; font-family:sans-serif; text-transform:uppercase; font-weight:900; letter-spacing:2px; text-shadow:0 0 15px rgba(0,243,255,0.5);'>Access Denied. You are not an Admin.</div>");
}
// -----------------------------------

// Stats
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'] ?? 0;
// Target anime_comments
$totalComments = $conn->query("SELECT COUNT(*) as count FROM anime_comments")->fetch_assoc()['count'] ?? 0;

// Announcements
$ann_result = $conn->query("SELECT * FROM site_settings");
$announcement = [];
if($ann_result) {
    while($row = $ann_result->fetch_assoc()) {
        $announcement[$row['setting_key']] = $row['setting_value'];
    }
}
$ann_text = $announcement['announcement'] ?? '';
$ann_active = ($announcement['announcement_active'] ?? '0') === '1';

// Auto-detect history table
$table = "watch_history"; $col_id = "media_id";
$checkTable = $conn->query("SHOW TABLES LIKE 'anime_history'");
if ($checkTable && $checkTable->num_rows > 0) { $table = "anime_history"; $col_id = "anime_id"; }

// Trending (Top 5 Local Anime)
$trending_array = [];
$trendingStats = $conn->query("SELECT $col_id as media_id, COUNT(*) as views FROM $table GROUP BY $col_id ORDER BY views DESC LIMIT 5");
if ($trendingStats) {
    while($row = $trendingStats->fetch_assoc()) {
        $trending_array[] = $row;
    }
}

// Comments from anime_comments
$commentsQuery = $conn->query("
    SELECT c.id, c.comment_text, c.created_at, c.anime_id as media_id, c.episode, u.username 
    FROM anime_comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    ORDER BY c.created_at DESC LIMIT 50
");

// Users
$usersQuery = $conn->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b0b0b; font-family: 'Inter', sans-serif; color: #fff; }
        .cyan-text { color: #00f3ff; text-shadow: 0 0 10px rgba(0, 243, 255, 0.4); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #00f3ff; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <header class="h-16 border-b border-gray-800 bg-[#111] flex justify-between items-center px-6 sticky top-0 z-50 shadow-md">
        <h1 class="text-xl font-black cyan-text tracking-widest uppercase">ZENTRIX <span class="text-black text-xs bg-[#00f3ff] px-2 py-0.5 rounded shadow-[0_0_10px_rgba(0,243,255,0.6)] ml-2">ADMIN</span></h1>
        <a href="../index.php" class="text-sm font-bold text-gray-400 hover:text-[#00f3ff] transition">Exit to Site ➔</a>
    </header>

    <div class="flex-1 max-w-7xl mx-auto w-full p-4 md:p-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-lg flex items-center justify-between">
                <div>
                    <h3 class="text-gray-400 font-bold uppercase tracking-widest text-xs mb-1">Total Users</h3>
                    <p class="text-3xl font-black text-white"><?= number_format($totalUsers) ?></p>
                </div>
                <div class="bg-cyan-500/20 text-[#00f3ff] p-3 rounded-lg">👥</div>
            </div>
            <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-lg flex items-center justify-between">
                <div>
                    <h3 class="text-gray-400 font-bold uppercase tracking-widest text-xs mb-1">Total Comments</h3>
                    <p class="text-3xl font-black text-white"><?= number_format($totalComments) ?></p>
                </div>
                <div class="bg-cyan-500/20 text-[#00f3ff] p-3 rounded-lg">💬</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <div class="bg-[#111] border border-gray-800 rounded-xl p-6 shadow-xl">
                <h2 class="text-lg font-bold text-[#00f3ff] mb-4 uppercase">📢 Global Announcement</h2>
                <textarea id="ann-text" class="w-full bg-gray-900 text-white p-3 rounded mb-3 border border-gray-700 focus:border-[#00f3ff] focus:outline-none resize-none h-24" placeholder="Type banner message here..."><?= htmlspecialchars($ann_text) ?></textarea>
                <div class="flex justify-between items-center">
                    <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                        <input type="checkbox" id="ann-active" class="w-4 h-4 accent-[#00f3ff]" <?= $ann_active ? 'checked' : '' ?>> 
                        <span class="font-bold">Display Banner</span>
                    </label>
                    <button onclick="saveAnnouncement()" class="bg-[#00f3ff] text-black hover:bg-cyan-400 px-6 py-2 rounded font-bold text-sm shadow-[0_0_10px_rgba(0,243,255,0.4)] transition">Update</button>
                </div>
            </div>

            <div class="bg-[#111] border border-gray-800 rounded-xl p-6 shadow-xl flex flex-col">
                <h2 class="text-lg font-bold text-[#00f3ff] mb-4 uppercase">🔥 Top Streamed (Local)</h2>
                <div id="trending-container" class="space-y-3 flex-1 overflow-y-auto max-h-[200px] ep-list-scroll pr-2">
                    <div class="text-center py-6 text-gray-500 animate-pulse text-sm font-bold">Fetching Titles from AniList...</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-[#111] border border-gray-800 rounded-xl overflow-hidden shadow-xl">
                <div class="bg-[#0f0f0f] border-b border-gray-800 px-6 py-4">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-[#00f3ff]">Recent Comments</h2>
                </div>
                <div class="p-4 space-y-4 max-h-[500px] overflow-y-auto ep-list-scroll">
                    <?php if($commentsQuery && $commentsQuery->num_rows > 0): ?>
                        <?php while($c = $commentsQuery->fetch_assoc()): ?>
                            <div class="bg-gray-900 border border-gray-700 rounded-lg p-4 transition" id="comment-<?= $c['id'] ?>">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="font-bold text-white"><?= htmlspecialchars($c['username'] ?? 'Unknown') ?></span>
                                        <span class="text-[10px] text-[#00f3ff] ml-2 font-bold uppercase">EP <?= $c['episode'] ?></span>
                                        <span class="text-[10px] text-gray-500 ml-2"><?= date('M j, Y g:i A', strtotime($c['created_at'])) ?></span>
                                    </div>
                                    <button onclick="deleteComment(<?= $c['id'] ?>)" class="text-gray-500 hover:text-red-500 transition focus:outline-none font-bold">✕</button>
                                </div>
                                <p class="text-sm text-gray-300 whitespace-pre-wrap"><?= htmlspecialchars($c['comment_text']) ?></p>
                                <a href="watch.php?id=<?= $c['media_id'] ?>&ep=<?= $c['episode'] ?>" class="text-[10px] uppercase text-[#00f3ff] hover:underline mt-2 inline-block font-bold" target="_blank">View Context ➔</a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-10 font-bold">No comments found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-[#111] border border-gray-800 rounded-xl overflow-hidden shadow-xl">
                <div class="bg-[#0f0f0f] border-b border-gray-800 px-6 py-4">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-[#00f3ff]">User Management</h2>
                </div>
                <div class="overflow-x-auto max-h-[500px] overflow-y-auto ep-list-scroll">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-900 border-b border-gray-800 text-[10px] uppercase tracking-wider text-gray-400">
                                <th class="p-4">User</th>
                                <th class="p-4">Role</th>
                                <th class="p-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-300">
                            <?php if($usersQuery && $usersQuery->num_rows > 0): ?>
                                <?php while($u = $usersQuery->fetch_assoc()): ?>
                                    <tr class="border-b border-gray-800/50 hover:bg-gray-800/30 transition" id="user-<?= $u['id'] ?>">
                                        <td class="p-4">
                                            <div class="font-bold text-white"><?= htmlspecialchars($u['username']) ?></div>
                                            <div class="text-[10px] text-gray-500"><?= date('M j, Y', strtotime($u['created_at'])) ?></div>
                                        </td>
                                        <td class="p-4">
                                            <?php if($u['id'] !== $user_id): ?>
                                                <select onchange="changeRole(<?= $u['id'] ?>, this.value)" class="bg-gray-900 border border-gray-700 text-xs text-white p-1 rounded focus:outline-none focus:border-[#00f3ff] cursor-pointer">
                                                    <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                                    <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                </select>
                                            <?php else: ?>
                                                <span class="bg-cyan-500/20 text-[#00f3ff] px-2 py-1 rounded text-[10px] font-bold uppercase border border-cyan-500/30">You (Admin)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-4 text-right">
                                            <?php if($u['id'] !== $user_id): ?>
                                                <button onclick="deleteUser(<?= $u['id'] ?>, '<?= addslashes($u['username']) ?>')" class="bg-red-600 hover:bg-red-500 text-white px-3 py-1 rounded text-[10px] font-bold transition shadow-md">BAN</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center py-10 text-gray-500 font-bold">No users found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- ANILIST BULK FETCH LOGIC ---
        const ANILIST_API = 'https://graphql.anilist.co';
        const trendingData = <?= json_encode($trending_array) ?>;

        async function loadTrending() {
            const container = document.getElementById('trending-container');
            if (!trendingData || trendingData.length === 0) {
                container.innerHTML = '<div class="text-gray-500 text-center py-6 font-bold text-sm">No streaming history yet.</div>';
                return;
            }

            const ids = trendingData.map(item => parseInt(item.media_id));
            const query = `
            query ($in: [Int]) {
                Page {
                    media(id_in: $in, type: ANIME) {
                        id title { english romaji } coverImage { large } format
                    }
                }
            }`;

            try {
                const res = await fetch(ANILIST_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query, variables: { in: ids } })
                });
                const data = await res.json();
                
                const aniMap = {};
                if(data.data && data.data.Page.media) {
                    data.data.Page.media.forEach(m => aniMap[m.id] = m);
                }

                let html = '';
                for (const item of trendingData) {
                    const ani = aniMap[item.media_id];
                    if(!ani) continue;
                    
                    const title = ani.title.english || ani.title.romaji || `Unknown ID: ${item.media_id}`;
                    const poster = ani.coverImage.large || 'https://via.placeholder.com/200x300/111/00f3ff?text=No+Image';

                    html += `
                        <div class="bg-gray-900 border border-gray-800 rounded-lg p-2 flex items-center gap-3">
                            <img src="${poster}" class="w-10 h-14 object-cover rounded shadow-md">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-sm text-white truncate">${title}</h4>
                                <span class="text-[9px] bg-gray-800 text-cyan-400 px-1.5 py-0.5 rounded uppercase font-bold border border-gray-700">${ani.format || 'TV'}</span>
                            </div>
                            <div class="text-right pr-2">
                                <p class="text-xl font-black text-[#00f3ff] leading-none">${item.views}</p>
                                <p class="text-[9px] text-gray-500 uppercase tracking-widest">Views</p>
                            </div>
                        </div>
                    `;
                }
                container.innerHTML = html;
            } catch(e) {
                console.error("AniList Fetch Failed", e);
                container.innerHTML = '<div class="text-red-500 text-center py-6 font-bold text-sm">Failed to connect to AniList API.</div>';
            }
        }
        loadTrending();

        // --- DASHBOARD ACTIONS ---
        async function saveAnnouncement() {
            const fd = new URLSearchParams();
            fd.append('action', 'save_announcement');
            fd.append('text', document.getElementById('ann-text').value);
            fd.append('active', document.getElementById('ann-active').checked ? 1 : 0);
            
            await fetch('admin_action.php', { method: 'POST', body: fd });
            alert("Banner Updated! Refresh your homepage to see it.");
        }

        async function changeRole(userId, newRole) {
            if(!confirm(`Are you sure you want to change this user's role to ${newRole.toUpperCase()}?`)) {
                location.reload(); 
                return;
            }
            const fd = new URLSearchParams();
            fd.append('action', 'set_role');
            fd.append('user_id', userId);
            fd.append('role', newRole);
            
            const res = await fetch('admin_action.php', { method: 'POST', body: fd });
            const data = await res.json();
            if(data.status !== 'success') alert(data.message);
        }

        async function deleteComment(id) {
            if(!confirm("Permanently delete this comment?")) return;
            const fd = new URLSearchParams();
            fd.append('action', 'delete_comment');
            fd.append('comment_id', id);
            
            await fetch('admin_action.php', { method: 'POST', body: fd });
            const el = document.getElementById(`comment-${id}`);
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        }

        async function deleteUser(id, username) {
            if(!confirm(`DANGER: Permanently BAN user '${username}'?`)) return;
            const fd = new URLSearchParams();
            fd.append('action', 'delete_user');
            fd.append('user_id', id);
            
            await fetch('admin_action.php', { method: 'POST', body: fd });
            const el = document.getElementById(`user-${id}`);
            el.style.backgroundColor = '#5c1414'; 
            setTimeout(() => el.remove(), 400);
        }
    </script>
</body>
</html>