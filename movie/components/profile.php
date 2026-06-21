<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$env = @parse_ini_file(__DIR__ . '/../.env') ?: [];
$tmdbKey = $env['TMDB_API_KEY'] ?? '';

// Fetch user info & check for ADMIN role
$user = [];
$res = $conn->query("SELECT username, email, role FROM users WHERE id=$user_id");
if ($res && $res->num_rows > 0) $user = $res->fetch_assoc();

// --- STATS & TIME ENJOYED ALGORITHM ---
$movies_count = 0; $tv_shows_count = 0; $tv_episodes_approx = 0; $watchlist_count = 0;

$m_res = $conn->query("SELECT COUNT(DISTINCT media_id) as c FROM movie_history WHERE user_id=$user_id AND media_type='movie'");
if($m_res) $movies_count = $m_res->fetch_assoc()['c'];

$t_res = $conn->query("SELECT COUNT(DISTINCT media_id) as c FROM movie_history WHERE user_id=$user_id AND media_type='tv'");
if($t_res) $tv_shows_count = $t_res->fetch_assoc()['c'];

$te_res = $conn->query("SELECT SUM(((season - 1) * 12) + episode) as c FROM movie_history WHERE user_id=$user_id AND media_type='tv'");
if($te_res) {
    $val = $te_res->fetch_assoc()['c'];
    $tv_episodes_approx = $val ? intval($val) : 0;
}

$w_res = $conn->query("SELECT COUNT(*) as c FROM watchlist WHERE user_id=$user_id AND (type='movie' OR type='tv')");
if($w_res) $watchlist_count = $w_res->fetch_assoc()['c'];

// TIME ENJOYED ALGORITHM (Positive Spin)
$total_minutes = ($movies_count * 120) + ($tv_episodes_approx * 45);
$days_enjoyed = floor($total_minutes / 1440);
$hours_enjoyed = floor(($total_minutes % 1440) / 60);

// SAFELY FETCH HISTORY
$orderCol = 'updated_at'; 
$check = $conn->query("SHOW COLUMNS FROM movie_history LIKE 'watched_at'");
if ($check && $check->num_rows > 0) $orderCol = 'watched_at';

$history_items = [];
$hq = $conn->query("SELECT media_id, media_type, season, episode FROM movie_history WHERE user_id=$user_id ORDER BY $orderCol DESC LIMIT 50");
if ($hq) {
    $seen = [];
    while($row = $hq->fetch_assoc()) {
        $key = $row['media_type'] . '_' . $row['media_id'];
        if(!isset($seen[$key])) {
            $history_items[] = $row;
            $seen[$key] = true;
            if(count($history_items) == 6) break; 
        }
    }
}

// MAKE "CONTINUE WATCHING" BUTTON PLAY THE LAST MOVIE
$latest_watch_url = "../pages/continue-watching.php";
if (!empty($history_items)) {
    $latest = $history_items[0];
    $latest_watch_url = "../pages/watch.php?id=" . $latest['media_id'] . "&type=" . $latest['media_type'] . "&season=" . $latest['season'] . "&episode=" . $latest['episode'];
}

// FETCH COMMENTS
$user_comments = [];
$cq = $conn->query("SELECT media_id, media_type, season, episode, comment_text, created_at FROM movie_comments WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 10");
if ($cq) {
    while($row = $cq->fetch_assoc()) $user_comments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b0b0b; font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ff0000; border-radius: 10px; }
        .ep-list-scroll::-webkit-scrollbar-track { background: #111; }
    </style>
</head>
<body class="text-white min-h-screen p-4 md:p-8 flex flex-col items-center">

    <div class="max-w-6xl w-full">
        <header class="flex justify-between items-center mb-8 border-b border-gray-800 pb-4">
            <h1 class="text-2xl font-black text-red-500 tracking-widest uppercase">My Profile</h1>
            <a href="../index.php" class="text-sm font-bold text-gray-400 hover:text-red-500 transition">BACK TO HOME ➔</a>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
            
            <div class="col-span-1 space-y-6">
                <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-[0_0_30px_rgba(0,0,0,0.8)] relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-red-600 via-red-500 to-red-900 shadow-[0_0_10px_rgba(255,0,0,0.5)]"></div>
                    
                    <div class="flex items-center gap-4 mb-6">
                        <div class="relative">
                            <div class="absolute inset-0 bg-red-600 rounded-full blur animate-pulse opacity-50"></div>
                            <div class="relative w-16 h-16 rounded-full bg-gradient-to-br from-gray-800 to-black border-2 border-red-600 flex items-center justify-center font-black text-2xl shadow-lg text-white">
                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h2>
                            <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
                                <p class="text-[10px] font-black text-white bg-red-600 px-2 py-0.5 rounded shadow-sm uppercase mt-1 inline-block border border-red-800">Admin</p>
                            <?php else: ?>
                                <p class="text-[10px] font-black text-red-500 uppercase mt-1 tracking-widest">Zentrix Member</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2 mb-6">
                        <div class="bg-gray-900 rounded-lg p-3 text-center border border-gray-800 hover:border-red-600 hover:bg-gray-800 transition duration-300 hover:shadow-[0_0_15px_rgba(255,0,0,0.2)] cursor-default group">
                            <p class="text-xl font-black text-red-500 group-hover:scale-110 transition-transform"><?= $movies_count ?></p>
                            <p class="text-[9px] text-gray-500 uppercase font-bold mt-1 group-hover:text-gray-300">Movies</p>
                        </div>
                        <div class="bg-gray-900 rounded-lg p-3 text-center border border-gray-800 hover:border-red-600 hover:bg-gray-800 transition duration-300 hover:shadow-[0_0_15px_rgba(255,0,0,0.2)] cursor-default group">
                            <p class="text-xl font-black text-red-500 group-hover:scale-110 transition-transform"><?= $tv_shows_count ?></p>
                            <p class="text-[9px] text-gray-500 uppercase font-bold mt-1 group-hover:text-gray-300">TV Shows</p>
                        </div>
                        <div class="bg-gray-900 rounded-lg p-3 text-center border border-gray-800 hover:border-red-600 hover:bg-gray-800 transition duration-300 hover:shadow-[0_0_15px_rgba(255,0,0,0.2)] cursor-default group">
                            <p class="text-xl font-black text-red-500 group-hover:scale-110 transition-transform"><?= $watchlist_count ?></p>
                            <p class="text-[9px] text-gray-500 uppercase font-bold mt-1 group-hover:text-gray-300">Watchlist</p>
                        </div>
                    </div>

                    <div class="mb-6 bg-gradient-to-br from-gray-900 to-[#1a0a0a] rounded-xl p-4 text-center border border-red-900/30 shadow-inner relative overflow-hidden">
                        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI0IiBoZWlnaHQ9IjQiPjxyZWN0IHdpZHRoPSI0IiBoZWlnaHQ9IjQiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSIvPjwvc3ZnPg==')] opacity-20"></div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1 relative z-10">Time Well Spent</p>
                        <p class="text-lg font-black text-white relative z-10">
                            <span class="text-red-500 text-3xl drop-shadow-[0_0_8px_rgba(255,0,0,0.6)]"><?= $days_enjoyed ?></span> Days, 
                            <span class="text-red-500 text-3xl drop-shadow-[0_0_8px_rgba(255,0,0,0.6)]"><?= $hours_enjoyed ?></span> Hrs
                        </p>
                    </div>

                    <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
                        <a href="../pages/admin.php" class="block w-full flex items-center justify-center gap-2 mb-3 py-2.5 bg-gradient-to-r from-red-800 to-red-600 hover:from-red-700 hover:to-red-500 text-white font-black rounded-lg transition shadow-[0_0_15px_rgba(255,0,0,0.5)] border border-red-500 text-sm uppercase tracking-widest">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Admin Dashboard
                        </a>
                    <?php endif; ?>

                    <a href="<?= $latest_watch_url ?>" class="block w-full flex justify-center items-center gap-2 mb-3 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-lg transition text-sm border border-gray-700 shadow-md">
                        <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Continue Watching
                    </a>
                    
                    <a href="../components/logout.php" class="block w-full text-center py-2 border border-gray-800 text-gray-500 hover:bg-red-600/10 hover:border-red-600 hover:text-red-500 font-bold rounded-lg transition text-sm uppercase tracking-wider">Logout</a>
                </div>
            </div>

            <div class="col-span-1 lg:col-span-2 space-y-8">
                
                <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-xl">
                    <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                        <h3 class="text-lg font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1 h-5 bg-red-600 rounded"></span> Recently Watched
                        </h3>
                        <a href="../pages/continue-watching.php" class="text-xs font-bold text-red-500 hover:underline">View All ❯</a>
                    </div>
                    
                    <?php if(empty($history_items)): ?>
                        <div class="text-center py-10 text-gray-500 font-bold bg-gray-900/50 rounded-xl border border-gray-800/50">No Watch History Yet. Go watch some media!</div>
                    <?php else: ?>
                        <div id="history-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
                    <?php endif; ?>
                </div>

                <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-white uppercase tracking-wider flex items-center gap-2 mb-6 border-b border-gray-800 pb-4">
                        <span class="w-1 h-5 bg-red-600 rounded"></span> My Recent Comments
                    </h3>
                    
                    <?php if(empty($user_comments)): ?>
                        <div class="text-center py-8 text-gray-500 font-bold bg-gray-900/50 rounded-xl border border-gray-800/50">You haven't posted any comments yet.</div>
                    <?php else: ?>
                        <div id="user-comments-list" class="space-y-4 max-h-[400px] overflow-y-auto pr-2 ep-list-scroll">
                            <div class="text-center py-4 text-red-500 font-bold animate-pulse text-sm">Loading Comments...</div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

    <script>
        const API_KEY = '<?= $tmdbKey ?>';
        const rawHistory = <?= json_encode($history_items) ?>;
        const rawComments = <?= json_encode($user_comments) ?>;

        document.addEventListener('DOMContentLoaded', () => {
            if (rawHistory.length > 0) fetchHistory();
            if (rawComments.length > 0) fetchUserComments();
        });

        async function fetchHistory() {
            const grid = document.getElementById('history-grid');
            for(let item of rawHistory) {
                try {
                    const res = await fetch(`https://api.themoviedb.org/3/${item.media_type}/${item.media_id}?api_key=${API_KEY}`);
                    const data = await res.json();
                    
                    const title = data.title || data.name || item.title || 'Unknown Title';
                    const image = data.poster_path ? `https://image.tmdb.org/t/p/w300${data.poster_path}` : 'https://via.placeholder.com/300x450/111/ff0000?text=No';
                    const epBadge = item.media_type === 'tv' ? `S${item.season} E${item.episode}` : 'MOVIE';
                    
                    grid.innerHTML += `
                        <a href="../pages/watch.php?id=${item.media_id}&type=${item.media_type}&season=${item.season}&episode=${item.episode}" class="block relative overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-red-600 transition shadow-lg hover:scale-105 hover:shadow-[0_0_20px_rgba(255,0,0,0.4)]">
                            <div class="absolute top-2 left-2 bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded shadow-md z-10 border border-red-800">${epBadge}</div>
                            <div class="aspect-[2/3]"><img src="${image}" class="w-full h-full object-cover opacity-80 hover:opacity-100"></div>
                            <div class="absolute bottom-0 w-full p-2 bg-gradient-to-t from-black to-transparent pt-10"><p class="text-xs font-bold text-white truncate drop-shadow-md">${title}</p></div>
                        </a>`;
                } catch(e) {}
            }
        }

        async function fetchUserComments() {
            const listContainer = document.getElementById('user-comments-list');
            listContainer.innerHTML = ''; 
            
            for(let c of rawComments) {
                try {
                    const res = await fetch(`https://api.themoviedb.org/3/${c.media_type}/${c.media_id}?api_key=${API_KEY}`);
                    const data = await res.json();
                    
                    const title = data.title || data.name || 'Unknown Title';
                    const date = new Date(c.created_at).toLocaleDateString();
                    const epBadge = c.media_type === 'tv' 
                        ? `<span class="text-[10px] font-black text-white bg-red-600 px-2 py-0.5 rounded shadow-sm">S${c.season} E${c.episode}</span>` 
                        : `<span class="text-[10px] font-black text-white bg-red-600 px-2 py-0.5 rounded shadow-sm">MOVIE</span>`;
                    
                    listContainer.innerHTML += `
                    <div class="bg-gray-900 p-4 rounded-xl border border-gray-800 hover:border-red-600/50 transition">
                        <div class="flex flex-wrap justify-between items-start gap-2 mb-3 border-b border-gray-800 pb-3">
                            <div class="flex items-center flex-wrap gap-2">
                                <a href="../pages/watch.php?id=${c.media_id}&type=${c.media_type}&season=${c.season || 1}&episode=${c.episode || 1}" class="font-bold text-red-500 hover:underline text-sm md:text-base">${title}</a>
                                ${epBadge}
                            </div>
                            <span class="text-xs text-gray-500 whitespace-nowrap bg-black px-2 py-1 rounded border border-gray-800">${date}</span>
                        </div>
                        <p class="text-gray-300 text-sm leading-relaxed">${c.comment_text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>
                    </div>`;
                } catch(e) { }
            }
        }
    </script>
</body>
</html>