<?php
session_start();
mysqli_report(MYSQLI_REPORT_OFF);
require '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: signup.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$saved_ids = [];

// FIXED: Using 'type' instead of 'media_type', and using safe fetch!
$sql = "SELECT media_id FROM watchlist WHERE user_id = ? AND type = 'anime' ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($media_id);
    while ($stmt->fetch()) {
        $saved_ids[] = $media_id;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Watchlist - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --accent: #00f3ff; }
        body { background-color: var(--bg-color); color: #fff; font-family: 'Inter', sans-serif; }
        .cyan-text { color: var(--accent); text-shadow: 0 0 10px rgba(0, 243, 255, 0.4); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,243,255,0.2); border: 1px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <div class="lg:hidden fixed top-0 w-full bg-[#111] border-b border-gray-800 p-4 flex justify-between items-center z-50">
        <h1 class="text-xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        <button onclick="toggleSidebar()" class="text-[#00f3ff] focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <div class="flex flex-1 w-full min-h-screen">
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-50 flex flex-col pt-20 lg:pt-0 lg:h-screen lg:sticky lg:top-0 shrink-0">
            <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
                <h1 class="text-2xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
            </div>
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
                <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🏠 Home</a>
                <a href="signup.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔒 Signup/Login</a>
                <a href="profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">👨 Profile</a>
                <a href="watchlist.php" class="block px-4 py-2 rounded bg-cyan-500/10 text-[#00f3ff] transition font-bold border border-cyan-500/30 border-l-4">📌 My List</a>
                <a href="../components/latest.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🆕 Latest</a>
                <a href="../components/new-on.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">✨ New On</a>
                <a href="../components/schedule.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">📅 Schedule</a>
                <a href="../components/trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔥 Trending</a>
                <a href="../components/popular-tv.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">📺 Popular Anime</a>
                <a href="../components/continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🕒 Continue-Watch</a>
            </nav>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <main class="flex-1 w-full min-w-0 pt-20 lg:pt-0">
            <header class="hidden lg:flex p-6 border-b border-gray-800 bg-[#0f0f0f] justify-between items-center z-20">
                <h1 class="text-xl font-bold tracking-[0.2em] cyan-text uppercase">MY WATCHLIST</h1>
            </header>

            <div class="p-6 max-w-7xl mx-auto w-full">
                
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-[#00f3ff] rounded"></span> Saved Anime
                    </h2>
                    <span class="bg-gray-800 text-gray-300 px-3 py-1 rounded-full text-sm font-bold"><?= count($saved_ids) ?> Items</span>
                </div>

                <?php if (empty($saved_ids)): ?>
                    <div class="bg-[#111] border border-gray-800 rounded-2xl p-12 text-center shadow-xl">
                        <div class="text-5xl mb-4">📌</div>
                        <h3 class="text-xl font-bold text-white mb-2">Your Watchlist is Empty</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Keep track of anime you want to watch by clicking the "+ My List" button on any anime page.</p>
                        <a href="../index.php" class="inline-block bg-[#00f3ff] text-black px-8 py-3 rounded-lg font-bold hover:bg-cyan-400 transition shadow-[0_0_15px_rgba(0,243,255,0.4)]">
                            Browse Anime
                        </a>
                    </div>
                <?php else: ?>
                    <div id="watchlist-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
                        <div class="col-span-full py-20 flex flex-col items-center justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#00f3ff] mb-4"></div>
                            <p class="text-[#00f3ff] font-bold tracking-widest uppercase animate-pulse">Syncing with AniList...</p>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        const ANILIST_API = 'https://graphql.anilist.co';
        const savedIds = <?= json_encode($saved_ids) ?>;

        document.addEventListener('DOMContentLoaded', () => {
            if (savedIds.length > 0) {
                fetchWatchlistData();
            }
        });

        async function fetchWatchlistData() {
            const query = `
            query ($ids: [Int]) {
                Page(page: 1, perPage: 50) {
                    media(id_in: $ids, type: ANIME) {
                        id
                        title { english romaji userPreferred }
                        coverImage { large }
                        format
                        averageScore
                        episodes
                    }
                }
            }`;

            try {
                const response = await fetch(ANILIST_API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ query, variables: { ids: savedIds } })
                });

                const json = await response.json();
                if (json.data && json.data.Page && json.data.Page.media) {
                    renderGrid(json.data.Page.media);
                } else {
                    throw new Error("No media returned");
                }
            } catch (err) {
                console.error(err);
                document.getElementById('watchlist-grid').innerHTML = '<div class="col-span-full text-center text-red-500 font-bold py-10">Failed to load AniList data.</div>';
            }
        }

        function renderGrid(mediaList) {
            const grid = document.getElementById('watchlist-grid');
            grid.innerHTML = '';

            // Sort AniList data to match our Database order (Most recently added first)
            const sortedMedia = [];
            savedIds.forEach(id => {
                const anime = mediaList.find(m => m.id === id);
                if (anime) sortedMedia.push(anime);
            });

            sortedMedia.forEach(anime => {
                const title = anime.title.english || anime.title.romaji || anime.title.userPreferred;
                const image = (anime.coverImage && anime.coverImage.large) || 'https://via.placeholder.com/300x450/111/00f3ff?text=No+Image';
                const format = anime.format || 'TV';
                const rating = anime.averageScore ? (anime.averageScore / 10).toFixed(1) : 'N/A';
                const eps = anime.episodes || '?';

                grid.innerHTML += `
                    <div class="relative group cursor-pointer transition-all duration-300 rounded-lg overflow-hidden poster-hover bg-gray-900 shadow-lg" id="card-${anime.id}">
                        
                        <button onclick="removeFromWatchlist(${anime.id}, event)" class="absolute top-2 right-2 bg-red-600/90 text-white w-8 h-8 rounded-full z-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 shadow-md" title="Remove">
                            ✕
                        </button>
                        
                        <div onclick="window.location.href='watch.php?id=${anime.id}&ep=1'">
                            <div class="aspect-[2/3] overflow-hidden bg-[#111]">
                                <img src="${image}" alt="${title.replace(/"/g, '&quot;')}" class="w-full h-full object-cover">
                            </div>
                            
                            <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black via-black/90 to-transparent pt-12">
                                <div class="flex justify-between items-center mb-1">
                                    <p class="text-[10px] font-bold text-[#00f3ff]">${format}</p>
                                    <span class="text-[9px] bg-cyan-500 text-black px-1 rounded font-bold">★ ${rating}</span>
                                </div>
                                <p class="text-sm font-bold truncate text-gray-100 drop-shadow-md mb-1">${title}</p>
                                <p class="text-[10px] text-gray-400">Episodes: ${eps}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
        }

        async function removeFromWatchlist(id, event) {
            event.stopPropagation(); // Prevents clicking the card and going to watch.php
            
            const card = document.getElementById(`card-${id}`);
            if(card) card.style.opacity = '0.5';

            try {
                const fd = new URLSearchParams();
                fd.append('media_id', id);
                fd.append('type', 'anime'); // Because this is the anime watchlist page
                
                const res = await fetch('add_to_watchlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: fd.toString()
                });
                
                const data = await res.json();
                
                if (data.status === 'removed') {
                    // Remove from DOM with animation
                    card.style.transform = 'scale(0.8)';
                    setTimeout(() => card.remove(), 300);
                } else {
                    alert(data.message || 'Error removing item');
                    card.style.opacity = '1';
                }
            } catch(e) {
                alert('Connection error');
                card.style.opacity = '1';
            }
        }
    </script>
</body>
</html>
