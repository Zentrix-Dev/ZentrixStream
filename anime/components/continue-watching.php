<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require '../db.php'; 

// Force Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$dbHistory = [];

// Auto-detect which table to use
$table = "watch_history";
$col_id = "media_id";
$checkTable = $conn->query("SHOW TABLES LIKE 'anime_history'");
if ($checkTable && $checkTable->num_rows > 0) {
    $table = "anime_history"; 
    $col_id = "anime_id";
}

// Fetch history cleanly from auto-repaired table
$stmt = $conn->prepare("SELECT $col_id as media_id, episode FROM $table WHERE user_id = ? ORDER BY id DESC LIMIT 20");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $id = $row['media_id'];
            $ep = $row['episode'];

            if (!empty($id) && is_numeric($id)) {
                $dbHistory[] = [
                    'anime_id' => $id,
                    'episode' => $ep
                ];
            }
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime History - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --accent: #00f3ff; --text-main: #ffffff; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .cyan-text { color: var(--accent); text-shadow: 0 0 10px rgba(0, 243, 255, 0.4); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,243,255,0.2); border: 1px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0b0b0b] text-white min-h-screen flex">

    <div class="lg:hidden fixed top-0 w-full bg-[#111] border-b border-gray-800 p-4 flex justify-between items-center z-50">
        <h1 class="text-xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        <button id="mobile-menu-btn" class="text-cyan-400 focus:outline-none" onclick="toggleSidebar()">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-40 flex flex-col pt-20 lg:pt-0">
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800">
            <h1 class="text-2xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        </div>
        
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">🏠 Home</a>
            <a href="../pages/watchlist.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">📌 My List</a>
            <a href="../pages/login.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">🔒 Signup/Login</a>
            <a href="../pages/profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">👨 Profile</a>
            <a href="latest.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">🆕 Latest</a>
            <a href="new-on.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">✨ New On</a>
            <a href="schedule.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">📅 Schedule</a>
            <a href="trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">🔥 Trending</a>
            <a href="upcoming.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-cyan-400 transition font-bold">🚀 Upcoming</a>
            <a href="continue-watching.php" class="block px-4 py-2 rounded bg-cyan-500/10 text-cyan-400 transition font-bold border border-cyan-500/30 border-l-4">🕒 Continue-Watch</a>
        </nav>

        <div class="p-4 border-t border-gray-800">
            <a href="../pages/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-black bg-cyan-500 hover:bg-cyan-400 rounded-lg transition-colors uppercase tracking-wide">
                Logout
            </a>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 flex flex-col relative min-w-0 h-screen overflow-y-auto pt-20 lg:pt-0">
        <header class="hidden lg:flex p-6 border-b border-gray-800 bg-[#0f0f0f] items-center shrink-0">
            <h1 class="text-xl font-bold cyan-text uppercase flex items-center gap-2">
                <span class="w-1 h-6 bg-cyan-400 rounded"></span> Anime History
            </h1>
        </header>

        <div class="flex-1 p-6">
            <div id="loading" class="text-center text-cyan-400 font-bold animate-pulse py-10">Syncing with AniList...</div>
            <div id="list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 hidden"></div>
            
            <div id="empty" class="hidden col-span-full flex flex-col items-center justify-center py-24 text-gray-500 bg-[#111] rounded-xl border border-gray-800 shadow-[0_0_15px_rgba(0,0,0,0.5)]">
                <p class="font-bold text-sm tracking-widest uppercase">No Recent History Found</p>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </main>

    <script>
        const ANILIST_API = 'https://graphql.anilist.co';
        const dbHistory = <?= json_encode($dbHistory ?: []) ?>;

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const listContainer = document.getElementById('list');
            const emptyState = document.getElementById('empty');
            const loadingState = document.getElementById('loading');

            if (dbHistory.length === 0) {
                loadingState.classList.add('hidden');
                emptyState.classList.remove('hidden');
                return;
            }

            const ids = dbHistory.map(item => parseInt(item.anime_id));

            const query = `
            query ($in: [Int]) {
                Page(page: 1, perPage: 50) {
                    media(id_in: $in, type: ANIME) {
                        id
                        title { romaji english userPreferred }
                        coverImage { extraLarge large }
                        episodes
                        status
                        nextAiringEpisode { episode }
                    }
                }
            }`;

            try {
                const res = await fetch(ANILIST_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ query, variables: { in: ids } })
                });

                const data = await res.json();
                if (!data.data || !data.data.Page.media.length) throw new Error("No media returned from AniList");

                const aniDataMap = {};
                data.data.Page.media.forEach(anime => aniDataMap[anime.id] = anime);

                let html = '';
                
                dbHistory.forEach(dbItem => {
                    const aniData = aniDataMap[dbItem.anime_id];
                    if (!aniData) return; 

                    const title = aniData.title.english || aniData.title.romaji || aniData.title.userPreferred;
                    const posterPath = aniData.coverImage.extraLarge || aniData.coverImage.large;
                    
                    const watchedEps = parseInt(dbItem.episode) || 1;
                    const totalEps = aniData.episodes || (aniData.nextAiringEpisode ? aniData.nextAiringEpisode.episode - 1 : 12);
                    let progress = Math.min((watchedEps / totalEps) * 100, 100);
                    
                    let newEpBadge = aniData.status === 'RELEASING' ? `<div class="absolute top-2 right-2 bg-cyan-400 text-black text-[9px] font-black px-2 py-1 rounded shadow-[0_0_10px_rgba(0,243,255,0.8)] uppercase tracking-widest border border-cyan-400 z-10 animate-pulse">NEW EP</div>` : '';
                    
                    let watchUrl = `../pages/watch.php?id=${dbItem.anime_id}&ep=${dbItem.episode}`;

                    html += `
                        <div class="cursor-pointer transition-all duration-300 rounded-lg overflow-hidden poster-hover relative bg-gray-900 shadow-lg" onclick="window.location.href='${watchUrl}'">
                            <div class="aspect-[2/3] overflow-hidden relative">
                                <img src="${posterPath}" class="w-full h-full object-cover">
                                ${newEpBadge}
                                <div class="absolute bottom-2 right-2 bg-cyan-400/90 backdrop-blur-md text-black text-[11px] font-black px-2 py-0.5 rounded uppercase tracking-widest z-10 shadow-[0_0_10px_rgba(0,243,255,0.5)]">
                                    EP ${dbItem.episode}
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 h-1.5 bg-gray-800/80 z-10">
                                    <div class="h-full bg-cyan-400 shadow-[0_0_8px_rgba(0,243,255,0.8)]" style="width: ${progress}%"></div>
                                </div>
                            </div>
                            <div class="p-3 bg-gradient-to-t from-black via-black/80 to-transparent pt-6">
                                <p class="text-xs font-bold text-gray-100 truncate">${title}</p>
                            </div>
                        </div>
                    `;
                });

                loadingState.classList.add('hidden');
                
                if (html === '') {
                    emptyState.classList.remove('hidden');
                } else {
                    listContainer.innerHTML = html;
                    listContainer.classList.remove('hidden');
                }

            } catch (err) {
                loadingState.classList.add('hidden');
                emptyState.innerHTML = '<p class="font-bold text-sm tracking-widest uppercase text-red-500">Error syncing data.</p>';
                emptyState.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>