<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Fetch user info & check for ADMIN role
$user = [];
$res = $conn->query("SELECT username, email, role, created_at FROM users WHERE id=$user_id");
if ($res && $res->num_rows > 0) $user = $res->fetch_assoc();

// Auto-detect history table safely
$table = "watch_history"; 
$col_id = "media_id";
$checkTable = $conn->query("SHOW TABLES LIKE 'anime_history'");
if ($checkTable && $checkTable->num_rows > 0) { 
    $table = "anime_history"; 
    $col_id = "anime_id"; 
}

// --- STATS & TIME WASTED ALGORITHM ---
$anime_count = 0; 
$episodes_watched = 0; 
$watchlist_count = 0;

$m_res = $conn->query("SELECT COUNT(DISTINCT $col_id) as c FROM $table WHERE user_id=$user_id");
if($m_res) $anime_count = $m_res->fetch_assoc()['c'];

// Get total episodes based on the latest history entry for each anime
$te_res = $conn->query("SELECT SUM(episode) as c FROM $table WHERE user_id=$user_id");
if($te_res) $episodes_watched = intval($te_res->fetch_assoc()['c']);

$w_res = $conn->query("SELECT COUNT(*) as c FROM watchlist WHERE user_id=$user_id AND type='anime'");
if($w_res) $watchlist_count = $w_res->fetch_assoc()['c'];

// TIME WASTED ALGORITHM (Approx 24 mins per Anime episode)
$total_minutes = $episodes_watched * 24;
$days_enjoyed = floor($total_minutes / 1440);
$hours_enjoyed = floor(($total_minutes % 1440) / 60);

// Otaku Level
$otaku_level = ceil($episodes_watched / 50) + 1;

// SAFELY FETCH HISTORY (Top 20 most recent)
$history_items = [];
$hq = $conn->query("SELECT $col_id as media_id, episode FROM $table WHERE user_id=$user_id ORDER BY id DESC LIMIT 20");
if ($hq) {
    $seen = [];
    while($row = $hq->fetch_assoc()) {
        $key = $row['media_id'];
        if(!isset($seen[$key])) {
            $history_items[] = $row;
            $seen[$key] = true;
            if(count($history_items) == 8) break; // Display top 8 in grid
        }
    }
}

// MAKE "CONTINUE WATCHING" BUTTON PLAY THE LAST ANIME
$latest_watch_url = "continue-watching.php";
if (!empty($history_items)) {
    $latest = $history_items[0];
    $latest_watch_url = "watch.php?id=" . $latest['media_id'] . "&ep=" . $latest['episode'];
}

// FETCH COMMENTS
$user_comments = [];
$cq = $conn->query("SELECT anime_id as media_id, episode, comment_text, created_at FROM anime_comments WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 15");
if ($cq) {
    while($row = $cq->fetch_assoc()) $user_comments[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?>'s Profile - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --accent: #00f3ff; --text-main: #ffffff; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .cyan-text { color: var(--accent); text-shadow: 0 0 10px rgba(0, 243, 255, 0.4); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,243,255,0.2); border: 1px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
        .ep-list-scroll::-webkit-scrollbar-track { background: #111; border-radius: 8px; }
    </style>
</head>
<body class="bg-[#0b0b0b] text-white min-h-screen flex">

    <div class="lg:hidden fixed top-0 w-full bg-[#111] border-b border-gray-800 p-4 flex justify-between items-center z-50">
        <h1 class="text-xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        <button onclick="toggleSidebar()" class="text-[#00f3ff] focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <!-- STANDARDIZED SIDEBAR NAV -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-40 flex flex-col pt-20 lg:pt-0 shrink-0 lg:h-screen lg:sticky lg:top-0">
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
            <h1 class="text-2xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        </div>
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🏠 Home</a>
            <a href="watchlist.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">📌 My List</a>
            <a href="login.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔒 Signup/Login</a>
            <a href="profile.php" class="block px-4 py-2 rounded bg-cyan-500/10 text-[#00f3ff] transition font-bold border border-cyan-500/30 border-l-4">👨 Profile</a>
            <a href="../components/latest.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🆕 Latest</a>
            <a href="../components/new-on.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">✨ New On</a>
            <a href="../components/schedule.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">📅 Schedule</a>
            <a href="../components/trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔥 Trending</a>
            <a href="../components/upcoming.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🚀 Upcoming</a>
            <a href="../components/continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🕒 Continue-Watch</a>
        </nav>
        <div class="p-4 border-t border-gray-800 shrink-0">
            <a href="logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-black bg-cyan-500 hover:bg-cyan-400 rounded-lg transition-colors uppercase tracking-wide">Logout</a>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 w-full min-w-0 flex flex-col pt-20 lg:pt-0">
        <header class="hidden lg:flex p-6 border-b border-gray-800 bg-[#0f0f0f] items-center shrink-0 justify-between z-20">
            <h1 class="text-xl font-bold cyan-text uppercase flex items-center gap-2">
                MY PROFILE
            </h1>
        </header>

        <div class="flex-1 p-4 md:p-8 max-w-7xl mx-auto w-full">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
                
                <!-- LEFT COLUMN: Profile Stats & Info -->
                <div class="col-span-1 space-y-6">
                    <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-cyan-600 to-[#00f3ff]"></div>
                        
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#00f3ff] to-blue-600 flex items-center justify-center text-black font-black text-2xl shadow-[0_0_15px_rgba(0,243,255,0.4)]">
                                <?= strtoupper(substr($user['username'], 0, 1)) ?>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($user['username']) ?></h2>
                                <p class="text-xs font-black text-[#00f3ff] uppercase tracking-wider mt-0.5">Otaku Level <?= $otaku_level ?></p>
                                <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
                                    <p class="text-[9px] font-black text-black bg-[#00f3ff] px-2 py-0.5 rounded shadow-sm uppercase mt-1 inline-block">System Admin</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between items-center text-sm border-b border-gray-800 pb-2">
                                <span class="text-gray-400 font-bold">Total Anime</span>
                                <span class="font-bold text-[#00f3ff] text-lg"><?= $anime_count ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-gray-800 pb-2">
                                <span class="text-gray-400 font-bold">Total Episodes</span>
                                <span class="font-bold text-[#00f3ff] text-lg"><?= $episodes_watched ?></span>
                            </div>
                            <div class="flex justify-between items-center text-sm border-b border-gray-800 pb-2">
                                <span class="text-gray-400 font-bold">Watchlist</span>
                                <span class="font-bold text-[#00f3ff] text-lg"><?= $watchlist_count ?></span>
                            </div>
                        </div>

                        <div class="mb-6 bg-gray-900 rounded-xl p-4 text-center border border-gray-800 relative overflow-hidden">
                            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-widest mb-1">Time Wasted</p>
                            <p class="text-lg font-black text-white relative z-10">
                                <span class="text-[#00f3ff] text-3xl drop-shadow-[0_0_8px_rgba(0,243,255,0.6)]"><?= $days_enjoyed ?></span> Days, 
                                <span class="text-[#00f3ff] text-3xl drop-shadow-[0_0_8px_rgba(0,243,255,0.6)]"><?= $hours_enjoyed ?></span> Hrs
                            </p>
                        </div>

                        <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
                            <a href="admin.php" class="block w-full text-center mb-3 py-2.5 bg-gradient-to-r from-cyan-600 to-[#00f3ff] text-black font-black rounded-lg transition shadow-[0_0_15px_rgba(0,243,255,0.5)] border border-cyan-200 text-sm uppercase tracking-widest">
                                Admin Dashboard
                            </a>
                        <?php endif; ?>

                        <a href="<?= $latest_watch_url ?>" class="block w-full flex justify-center items-center gap-2 mb-3 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-lg transition text-sm border border-gray-700 shadow-md">
                            <svg class="w-4 h-4 text-[#00f3ff]" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Continue Watching
                        </a>
                        
                        <a href="logout.php" class="block w-full text-center py-2 border border-red-600/50 text-red-500 hover:bg-red-600 hover:text-white font-bold rounded-lg transition text-sm uppercase tracking-wider">Logout</a>
                    </div>

                    <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-xl">
                        <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Top Genres</h3>
                        <div id="genre-container" class="flex flex-wrap gap-2">
                            <span class="text-xs text-gray-600 italic">Analyzing history...</span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: History & Comments -->
                <div class="col-span-1 lg:col-span-2 space-y-8">
                    
                    <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-xl">
                        <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                            <h3 class="text-lg font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <span class="w-1 h-5 bg-[#00f3ff] rounded"></span> Recently Watched
                            </h3>
                            <a href="../components/continue-watching.php" class="text-xs font-bold text-[#00f3ff] hover:underline">View All ❯</a>
                        </div>
                        
                        <?php if(empty($history_items)): ?>
                            <div class="text-center py-10 text-gray-500 font-bold bg-gray-900/50 rounded-xl border border-gray-800/50">No Watch History Yet. Go watch some anime!</div>
                        <?php else: ?>
                            <div id="history-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                <div class="col-span-full text-center text-[#00f3ff] text-xs font-bold animate-pulse py-4 uppercase tracking-widest">Syncing with AniList...</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-[#111] border border-gray-800 rounded-2xl p-6 shadow-xl">
                        <h3 class="text-lg font-bold text-white uppercase tracking-wider flex items-center gap-2 mb-6 border-b border-gray-800 pb-4">
                            <span class="w-1 h-5 bg-[#00f3ff] rounded"></span> My Recent Comments
                        </h3>
                        
                        <?php if(empty($user_comments)): ?>
                            <div class="text-center py-8 text-gray-500 font-bold bg-gray-900/50 rounded-xl border border-gray-800/50">You haven't posted any comments yet.</div>
                        <?php else: ?>
                            <div id="user-comments-list" class="space-y-4 max-h-[400px] overflow-y-auto pr-2 ep-list-scroll">
                                <div class="text-center py-4 text-[#00f3ff] font-bold animate-pulse text-sm">Loading Context...</div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
        <?php include '../components/footer.php'; ?>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        const ANILIST_API = 'https://graphql.anilist.co';
        const rawHistory = <?= json_encode($history_items) ?>;
        const rawComments = <?= json_encode($user_comments) ?>;

        // Extract all unique IDs to fetch everything in ONE fast network call
        let uniqueIds = new Set();
        rawHistory.forEach(h => uniqueIds.add(parseInt(h.media_id)));
        rawComments.forEach(c => uniqueIds.add(parseInt(c.media_id)));

        document.addEventListener('DOMContentLoaded', () => {
            if (uniqueIds.size > 0) {
                fetchAniListData(Array.from(uniqueIds));
            } else {
                document.getElementById('genre-container').innerHTML = '<span class="text-xs text-gray-600">Not enough data.</span>';
            }
        });

        async function fetchAniListData(ids) {
            const query = `
            query ($in: [Int]) {
                Page(perPage: 50) {
                    media(id_in: $in, type: ANIME) {
                        id
                        title { english romaji userPreferred }
                        coverImage { large }
                        genres
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
                
                if (data.data && data.data.Page.media) {
                    const aniData = data.data.Page.media;
                    renderHistory(aniData);
                    renderComments(aniData);
                    calculateFavoriteGenres(aniData);
                }
            } catch(e) {
                console.error("Failed to fetch AniList data", e);
            }
        }

        function renderHistory(aniData) {
            const grid = document.getElementById('history-grid');
            if(!grid) return;
            
            grid.innerHTML = rawHistory.map(item => {
                const anime = aniData.find(a => a.id === parseInt(item.media_id));
                if(!anime) return '';
                
                const title = anime.title.english || anime.title.romaji || anime.title.userPreferred;
                const image = anime.coverImage.large || 'https://via.placeholder.com/300x450/111/00f3ff?text=No+Image';
                
                return `
                    <a href="watch.php?id=${item.media_id}&ep=${item.episode}" class="block relative overflow-hidden rounded-lg bg-gray-900 border border-gray-800 hover:border-[#00f3ff] transition shadow-lg hover:scale-105 hover:shadow-[0_0_20px_rgba(0,243,255,0.4)] poster-hover">
                        <div class="absolute top-2 left-2 bg-[#00f3ff] text-black text-[10px] font-black px-2 py-0.5 rounded shadow-[0_0_10px_rgba(0,243,255,0.6)] z-10 tracking-wider">EP ${item.episode}</div>
                        <div class="aspect-[2/3]"><img src="${image}" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-2 bg-gradient-to-t from-black via-black/80 to-transparent pt-10">
                            <p class="text-xs font-bold text-white truncate drop-shadow-md">${title}</p>
                        </div>
                    </a>`;
            }).join('');
        }

        function renderComments(aniData) {
            const container = document.getElementById('user-comments-list');
            if(!container) return;
            
            container.innerHTML = rawComments.map(c => {
                const anime = aniData.find(a => a.id === parseInt(c.media_id));
                const title = anime ? (anime.title.english || anime.title.romaji || anime.title.userPreferred) : 'Unknown Anime';
                const date = new Date(c.created_at).toLocaleDateString();
                
                return `
                <div class="bg-gray-900 p-4 rounded-xl border border-gray-800 hover:border-[#00f3ff]/50 transition">
                    <div class="flex flex-wrap justify-between items-start gap-2 mb-3 border-b border-gray-800 pb-3">
                        <div class="flex items-center flex-wrap gap-2">
                            <a href="watch.php?id=${c.media_id}&ep=${c.episode}" class="font-bold text-[#00f3ff] hover:underline text-sm md:text-base">${title}</a>
                            <span class="text-[10px] font-black text-black bg-white px-2 py-0.5 rounded shadow-sm border border-gray-300">EP ${c.episode}</span>
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap bg-black px-2 py-1 rounded border border-gray-800">${date}</span>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">${c.comment_text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>
                </div>`;
            }).join('');
        }

        function calculateFavoriteGenres(aniData) {
            const genreCounts = {};
            rawHistory.forEach(histItem => {
                const anime = aniData.find(a => a.id === parseInt(histItem.media_id));
                if (anime && anime.genres) {
                    anime.genres.forEach(genre => genreCounts[genre] = (genreCounts[genre] || 0) + 1);
                }
            });
            
            const topGenres = Object.keys(genreCounts).sort((a, b) => genreCounts[b] - genreCounts[a]).slice(0, 4);
            const container = document.getElementById('genre-container');
            
            if (topGenres.length > 0) {
                container.innerHTML = topGenres.map(genre => `<span class="px-3 py-1 bg-gray-800 border border-gray-700 text-[#00f3ff] text-xs font-bold rounded-full">${genre}</span>`).join('');
            } else {
                container.innerHTML = '<span class="text-xs text-gray-600">Not enough data to determine favorite genres.</span>';
            }
        }
    </script>
</body>
</html>