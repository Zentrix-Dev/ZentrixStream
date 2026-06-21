<?php
session_start();
error_reporting(0);
require '../db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../components/signup.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$saved_items = [];

$res = $conn->query("SELECT media_id, type, title FROM watchlist WHERE user_id=$user_id AND (type='movie' OR type='tv') ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $saved_items[] = $row;
    }
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
        :root { --bg-color: #0b0b0b; --accent: #ff0000; }
        body { background-color: var(--bg-color); color: #fff; font-family: 'Inter', sans-serif; }
        .red-text { color: var(--accent); text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(255,0,0,0.2); border: 1px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <div class="lg:hidden fixed top-0 w-full bg-[#111] border-b border-gray-800 p-4 flex justify-between items-center z-50">
        <h1 class="text-xl font-black red-text tracking-widest uppercase">ZENTRIX</h1>
        <button onclick="toggleSidebar()" class="text-red-500 focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <div class="flex flex-1 w-full min-h-screen">
        
        <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-50 flex flex-col pt-20 lg:pt-0 lg:h-screen lg:sticky lg:top-0 shrink-0">
            <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
                <h1 class="text-2xl font-black red-text tracking-widest uppercase">ZENTRIX</h1>
            </div>
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
                <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🏠 Home</a>
                <a href="../components/signup.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🔒 Signup/Login</a>
                <a href="../components/profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">👨 Profile</a>
                <a href="watchlist.php" class="block px-4 py-2 rounded bg-red-600/10 text-red-500 transition font-bold border border-red-600/30 border-l-4">📌 My List</a>
                <a href="trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🔥 Trending</a>
                <a href="popular-movies.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🎬 Popular Movies</a>
                <a href="popular-tv.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">📺 Popular TV</a>
                <a href="upcoming.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🚀 Upcoming</a>
                <a href="continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🕒 Continue-Watch</a>
            </nav>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <main class="flex-1 w-full min-w-0 pt-20 lg:pt-0">
            <header class="hidden lg:flex p-6 border-b border-gray-800 bg-[#0f0f0f] justify-between items-center z-20">
                <h1 class="text-xl font-bold tracking-[0.2em] red-text uppercase">MY WATCHLIST</h1>
            </header>

            <div class="p-6 max-w-7xl mx-auto w-full">
                
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-red-600 rounded"></span> Saved Movies & TV
                    </h2>
                    <span class="bg-gray-800 text-gray-300 px-3 py-1 rounded-full text-sm font-bold"><?= count($saved_items) ?> Items</span>
                </div>

                <?php if (empty($saved_items)): ?>
                    <div class="bg-[#111] border border-gray-800 rounded-2xl p-12 text-center shadow-xl">
                        <div class="text-5xl mb-4">📌</div>
                        <h3 class="text-xl font-bold text-white mb-2">Your Watchlist is Empty</h3>
                        <p class="text-gray-500 mb-6 max-w-md mx-auto">Keep track of shows you want to watch by clicking the "+ Add to List" button on any video page.</p>
                        <a href="../index.php" class="inline-block bg-red-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-red-500 transition shadow-[0_0_15px_rgba(255,0,0,0.4)]">
                            Browse Media
                        </a>
                    </div>
                <?php else: ?>
                    <div id="watchlist-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
                        <div class="col-span-full py-20 flex flex-col items-center justify-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600 mb-4"></div>
                            <p class="text-red-500 font-bold tracking-widest uppercase animate-pulse">Syncing Database...</p>
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

        const savedItems = <?= json_encode($saved_items) ?>;

        document.addEventListener('DOMContentLoaded', () => {
            if (savedItems.length > 0) fetchWatchlistData();
        });

        async function fetchWatchlistData() {
            const grid = document.getElementById('watchlist-grid');
            grid.innerHTML = ''; 
            
            try {
                // Map array to fetch through your secure backend API
                const fetchPromises = savedItems.map(item => 
                    fetch(`../api.php?route=${item.type}/${item.media_id}`).then(res => res.json())
                );

                // Wait for all to finish simultaneously 
                const results = await Promise.all(fetchPromises);

                let html = '';
                results.forEach((data, index) => {
                    if (data.error || data.status_code) return; 
                    
                    const item = savedItems[index];
                    const title = data.title || data.name || item.title;
                    const image = data.poster_path ? `https://image.tmdb.org/t/p/w300${data.poster_path}` : 'https://via.placeholder.com/300x450/111/ff0000?text=No+Image';
                    const rating = data.vote_average ? data.vote_average.toFixed(1) : 'N/A';
                    const typeLabel = item.type === 'tv' ? 'TV SHOW' : 'MOVIE';
                    
                    html += `
                        <div class="relative group cursor-pointer transition-all duration-300 rounded-lg overflow-hidden poster-hover bg-gray-900 shadow-lg" id="card-${item.media_id}-${item.type}">
                            <button onclick="removeFromWatchlist(${item.media_id}, '${item.type}', event)" class="absolute top-2 right-2 bg-red-600/90 text-white w-8 h-8 rounded-full z-20 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-500 shadow-md" title="Remove">✕</button>
                            
                            <div onclick="window.location.href='watch.php?id=${item.media_id}&type=${item.type}'">
                                <div class="aspect-[2/3] overflow-hidden bg-[#111]">
                                    <img src="${image}" alt="${title.replace(/"/g, '&quot;')}" class="w-full h-full object-cover">
                                </div>
                                <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black via-black/90 to-transparent pt-12">
                                    <div class="flex justify-between items-center mb-1">
                                        <p class="text-[10px] font-bold text-red-500">${typeLabel}</p>
                                        <span class="text-[9px] bg-red-600 text-white px-1 rounded font-bold">★ ${rating}</span>
                                    </div>
                                    <p class="text-sm font-bold truncate text-gray-100 drop-shadow-md mb-1">${title}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                grid.innerHTML = html || '<div class="col-span-full text-center text-red-500 font-bold py-10">No valid data found.</div>';
            } catch(e) {
                grid.innerHTML = '<div class="col-span-full text-center text-red-500 font-bold py-10">Failed to load data.</div>';
            }
        }

        async function removeFromWatchlist(id, type, event) {
            event.stopPropagation(); 
            
            const card = document.getElementById(`card-${id}-${type}`);
            if(card) card.style.opacity = '0.5';

            try {
                const fd = new URLSearchParams();
                fd.append('media_id', id);
                fd.append('type', type); 
                
                const res = await fetch('add_to_watchlist.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: fd.toString()
                });
                
                const data = await res.json();
                
                if (data.status === 'removed') {
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
