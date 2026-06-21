<?php
if (session_status() === PHP_SESSION_NONE) session_start();
mysqli_report(MYSQLI_REPORT_OFF); 
require '../db.php'; 

$env = @parse_ini_file(__DIR__ . '/../.env') ?: [];
$tmdbKey = $env['TMDB_API_KEY'] ?? '';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../components/signup.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$dbHistory = [];
$dbError = "";

// Safely determine the order column
$orderCol = 'id'; 
$check = $conn->query("SHOW COLUMNS FROM movie_history LIKE 'watched_at'");
if ($check && $check->num_rows > 0) {
    $orderCol = 'watched_at';
}

// THE FIX: Use SELECT * instead of explicitly naming 'progress'. 
// This prevents the page from crashing if the progress column hasn't been created yet.
$resMovie = $conn->query("SELECT * FROM movie_history WHERE user_id=$user_id ORDER BY $orderCol DESC LIMIT 50");

if ($resMovie) {
    $seen = [];
    while ($row = $resMovie->fetch_assoc()) {
        $key = ($row['media_type'] ?? 'movie') . '_' . ($row['media_id'] ?? 0);
        if(!isset($seen[$key])) {
            $dbHistory[] = [
                'media_id' => $row['media_id'] ?? 0,
                'media_type' => $row['media_type'] ?? 'movie',
                'season' => $row['season'] ?? 1,
                'episode' => $row['episode'] ?? 1,
                // Safely fallback to 0 if the progress column doesn't exist yet
                'progress' => isset($row['progress']) ? intval($row['progress']) : 0
            ];
            $seen[$key] = true;
        }
    }
} else {
    $dbError = $conn->error; // Capture error silently
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch History - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b0b0b; font-family: 'Inter', sans-serif; }
        .red-text { color: #ff0000; text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ff0000; border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0b0b0b] text-white flex flex-col min-h-screen">

    <header class="lg:hidden sticky top-0 z-[60] bg-[#111] h-16 flex justify-between items-center px-4 border-b border-gray-800 shadow-md">
        <h1 class="text-xl font-black red-text tracking-widest uppercase">ZENTRIX</h1>
        <button onclick="toggleSidebar()" class="text-red-600 focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </header>

    <div class="flex flex-1 relative w-full">
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-[70] flex flex-col h-screen lg:sticky lg:top-0 shrink-0">
            <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
                <h1 class="text-2xl font-black red-text drop-shadow-[0_0_10px_rgba(255,0,0,0.4)] tracking-widest uppercase">ZENTRIX</h1>
            </div>
            <div class="lg:hidden p-4 flex justify-between items-center border-b border-gray-800">
                <h2 class="text-xl font-bold tracking-widest red-text">MENU</h2>
                <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white text-3xl font-bold leading-none">&times;</button>
            </div>
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
                <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🏠 Home</a>
                <a href="watchlist.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">📌 My List</a>
                <a href="../components/signup.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🔒 Signup/Login</a>
                <a href="../components/profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">👨 Profile</a>
                <a href="trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🔥 Trending</a>
                <a href="popular-movies.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🎬 Popular Movies</a>
                <a href="popular-tv.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">📺 Popular TV</a>
                <a href="upcoming.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🚀 Upcoming</a>
                <a href="continue-watching.php" class="block px-4 py-2 rounded bg-red-600/10 text-red-500 transition font-bold border border-red-600/30 border-l-4">🕒 Continue-Watch</a>
            </nav>
            <div class="p-4 border-t border-gray-800 shrink-0">
                <a href="../components/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-600/80 hover:bg-red-600 rounded-lg transition-colors uppercase tracking-wide">Logout</a>
            </div>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[65] hidden lg:hidden" onclick="toggleSidebar()"></div>

        <main class="flex-1 w-full min-w-0 flex flex-col">
            <header class="hidden lg:flex h-20 border-b border-gray-800 bg-[#0f0f0f] px-6 items-center z-20 shrink-0">
                <h1 class="text-xl font-bold tracking-[0.2em] red-text uppercase">WATCH HISTORY</h1>
            </header>

            <div class="p-4 md:p-6 max-w-7xl mx-auto w-full flex-1 pb-12">
                <div id="list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
                    <div class="col-span-full flex flex-col items-center justify-center py-20">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600 mb-4"></div>
                        <p class="text-red-500 font-bold animate-pulse tracking-widest uppercase">Syncing Data...</p>
                    </div>
                </div>
                <div id="empty" class="hidden col-span-full flex flex-col items-center justify-center py-24 text-gray-500 bg-[#111] rounded-xl border border-gray-800 shadow-[0_0_15px_rgba(0,0,0,0.5)]">
                    <p class="font-bold text-sm tracking-widest uppercase">No Recent History Found</p>
                    <p id="error-log" class="text-xs text-red-500 mt-2 hidden"></p>
                </div>
            </div>
            <?php include '../components/footer.php'; ?>
        </main>
    </div>

    <script>
        const API_KEY = '<?= $tmdbKey ?>';
        const dbHistory = <?= json_encode($dbHistory) ?>;
        const phpError = <?= json_encode($dbError) ?>;

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const listContainer = document.getElementById('list');
            const emptyContainer = document.getElementById('empty');
            const errorLog = document.getElementById('error-log');

            if (!dbHistory || dbHistory.length === 0) {
                listContainer.innerHTML = '';
                emptyContainer.classList.remove('hidden');
                
                // Show database errors on screen if they happen
                if (phpError && phpError !== "") {
                    errorLog.innerText = "Database Error: " + phpError;
                    errorLog.classList.remove('hidden');
                }
                return;
            }

            const fetchPromises = dbHistory.map(async (item) => {
                try {
                    const res = await fetch(`https://api.themoviedb.org/3/${item.media_type}/${item.media_id}?api_key=${API_KEY}`);
                    if (!res.ok) return null; 
                    const tmdbData = await res.json();
                    return { dbItem: item, data: tmdbData };
                } catch (e) { 
                    console.error("Fetch failed", e);
                    return null; 
                }
            });

            const results = await Promise.all(fetchPromises);
            let html = '';

            results.forEach(res => {
                if (!res) return;
                
                const { dbItem, data } = res;
                
                const title = data.title || data.name || 'Unknown';
                const posterPath = data.poster_path ? `https://image.tmdb.org/t/p/w500${data.poster_path}` : 'https://via.placeholder.com/300x450/111/ff0000?text=No+Poster';
                const watchUrl = `watch.php?id=${dbItem.media_id}&type=${dbItem.media_type}&season=${dbItem.season}&episode=${dbItem.episode}`;
                const progress = dbItem.progress || 0; 
                
                let badgeHtml = '';
                let typeBadge = '';

                if (dbItem.media_type === 'tv') {
                    badgeHtml = `<div class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded shadow-[0_0_10px_rgba(255,0,0,0.5)] uppercase border border-red-800 z-10">S${dbItem.season} E${dbItem.episode}</div>`;
                } else {
                    badgeHtml = `<div class="absolute top-2 right-2 bg-red-600 text-white text-[10px] font-black px-1.5 py-0.5 rounded shadow-[0_0_10px_rgba(255,0,0,0.5)] uppercase border border-red-800 z-10">MOVIE</div>`;
                }
                typeBadge = `<span class="text-[9px] bg-red-600/20 text-red-500 border border-red-600/30 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">${dbItem.media_type}</span>`;

                html += `
                    <a href="${watchUrl}" class="block transition-all duration-300 rounded-lg overflow-hidden relative bg-gray-900 shadow-lg hover:scale-105 hover:shadow-[0_0_20px_rgba(255,0,0,0.4)] hover:border-red-600 border border-gray-800 group flex flex-col h-full">
                        <div class="aspect-[2/3] overflow-hidden relative shrink-0">
                            <img src="${posterPath}" alt="${title.replace(/"/g, '&quot;')}" class="w-full h-full object-cover" loading="lazy">
                            ${badgeHtml}
                            <div class="absolute top-2 left-2 flex items-center gap-1 bg-black/60 backdrop-blur-md px-1.5 py-0.5 rounded border border-gray-700 z-10">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 shadow-[0_0_5px_rgba(34,197,94,0.8)] animate-pulse"></span>
                                <span class="text-[8px] font-bold text-gray-200 uppercase tracking-widest">Cloud</span>
                            </div>
                        </div>
                        
                        <div class="relative flex-1 flex flex-col">
                            <div class="p-3 bg-gradient-to-t from-black via-black/90 to-black/80 flex-1">
                                <p class="text-xs font-bold text-gray-100 truncate drop-shadow-md">${title}</p>
                                <div class="flex justify-between items-center mt-1">
                                    ${typeBadge}
                                    <span class="text-[9px] text-gray-500 font-bold">${progress}%</span>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-gray-800 relative z-20 shrink-0">
                                <div class="h-full bg-red-600 shadow-[0_0_8px_rgba(255,0,0,0.8)] transition-all duration-1000" style="width: ${progress}%;"></div>
                            </div>
                        </div>
                    </a>
                `;
            });
            
            if (html === '') {
                listContainer.innerHTML = '';
                emptyContainer.classList.remove('hidden');
            } else {
                listContainer.innerHTML = html;
            }
        });
    </script>
</body>
</html>