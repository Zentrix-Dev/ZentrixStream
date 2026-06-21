<?php
session_start();
require 'db.php'; // Connect to DB to check for announcements

$env = @parse_ini_file(__DIR__ . '/.env') ?: [];
$tmdbKey = $env['TMDB_API_KEY'] ?? '';

// Fetch Announcement Settings securely
$ann_text = '';
$ann_active = '0';
$ann_result = $conn->query("SELECT * FROM site_settings WHERE setting_key IN ('announcement', 'announcement_active')");

if ($ann_result) {
    while($row = $ann_result->fetch_assoc()) {
        if($row['setting_key'] === 'announcement') $ann_text = $row['setting_value'];
        if($row['setting_key'] === 'announcement_active') $ann_active = $row['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zentrix Stream - Movies & TV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --card-bg: #141414; --accent: #ff0000; --text-main: #ffffff; --text-dim: #999; --border: #222; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .red-text { color: var(--accent); text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(255,0,0,0.3); border: 1px solid var(--accent); }
        #movie-modal { display: none; overflow-y: auto; background: rgba(0,0,0,0.95); position: fixed; inset: 0; z-index: 100; }
        .modal-body { max-width: 750px; margin: 2rem auto; background: #111; border-radius: 15px; overflow: hidden; border: 1px solid #333; position: relative; }
        .close-btn { position: absolute; right: 15px; top: 15px; background: var(--accent); color: #fff; border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; font-weight: bold; z-index: 101; }
        .meta-table { width: 100%; font-size: 0.85rem; border-collapse: collapse; }
        .meta-table td { padding: 8px 0; border-bottom: 1px solid #222; }
        .meta-table .label { color: var(--text-dim); width: 130px; font-weight: bold; text-transform: uppercase; font-size: 0.75rem; }
        .spotlight-slide { position: absolute; inset: 0; opacity: 0; transition: opacity 0.7s ease-in-out; pointer-events: none; z-index: 1; }
        .spotlight-slide.active { opacity: 1; pointer-events: auto; z-index: 2; }
        .carousel-dot { width: 10px; height: 10px; border-radius: 50%; background: #444; cursor: pointer; transition: all 0.3s ease; }
        .carousel-dot.active { background: var(--accent); box-shadow: 0 0 10px var(--accent); }
        .airing-item:hover { background: #1a1a1a; border-left: 4px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0b0b0b] min-h-screen flex">

    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-50 lg:z-40 flex flex-col lg:h-screen lg:sticky lg:top-0">
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
            <h1 class="text-2xl font-black text-red-600 drop-shadow-[0_0_10px_rgba(255,0,0,0.4)] tracking-widest uppercase">ZENTRIX</h1>
        </div>
        <div class="lg:hidden p-4 flex justify-between items-center border-b border-gray-800">
            <h2 class="text-xl font-bold tracking-widest red-text">MENU</h2>
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white text-3xl font-bold leading-none">&times;</button>
        </div>
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            <a href="index.php" class="block px-4 py-2 rounded bg-red-600/10 text-red-500 transition font-bold border border-red-600/30 border-l-4">🏠 Home</a>
            <a href="pages/watchlist.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">📌 My List</a>
            
            <!-- NEW JOIN PARTY BUTTON -->
            <a href="#" onclick="openJoinModal()" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🎉 Join Party</a>
            
            <a href="components/signup.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🔒 Signup/Login</a>
            <a href="components/profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">👨 Profile</a>
            <a href="pages/trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🔥 Trending</a>
            <a href="pages/popular-movies.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🎬 Popular Movies</a>
            <a href="pages/popular-tv.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">📺 Popular TV</a>
            <a href="pages/upcoming.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🚀 Upcoming</a>
            <a href="pages/continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🕒 Continue-Watch</a>
        </nav>
        <div class="p-4 border-t border-gray-800 shrink-0">
            <a href="components/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-600/80 hover:bg-red-600 rounded-lg transition-colors uppercase tracking-wide">Logout</a>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 w-full flex flex-col relative min-w-0 h-screen overflow-y-auto">
        
        <?php if($ann_active === '1' && !empty($ann_text)): ?>
            <div class="bg-red-600/90 backdrop-blur text-white text-center py-2 px-4 font-bold text-sm tracking-wide z-30 shadow-[0_4px_15px_rgba(255,0,0,0.3)] animate-pulse border-b border-red-800">
                <?= htmlspecialchars($ann_text) ?>
            </div>
        <?php endif; ?>

        <header class="p-6 border-b border-gray-800 bg-[#0f0f0f] z-20 shrink-0">
            <div class="flex flex-col items-center gap-4 text-center">
                <h1 class="text-xl font-bold tracking-[0.2em] red-text uppercase">ZENTRIX STREAM</h1>
                <div class="w-full max-w-2xl flex gap-4 items-center">
                    <button onclick="toggleSidebar()" class="text-red-600 hover:text-white transition shrink-0 lg:hidden">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <form onsubmit="event.preventDefault(); searchContent();" class="w-full flex gap-2">
                        <input type="text" id="searchInput" placeholder="Search Movies or TV Shows..." class="w-full bg-gray-900 border border-gray-700 rounded-full px-6 py-2 focus:outline-none focus:border-red-600 text-white">
                        <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-full font-bold hover:bg-red-700 transition">Search</button>
                    </form>
                </div>
            </div>
        </header>

        <div id="content-area" class="flex-1">
            <div id="spotlight-section" class="relative w-full h-[50vh] md:h-[60vh] min-h-[400px] overflow-hidden">
                <div id="spotlight-slides" class="w-full h-full relative"></div>
                <div id="spotlight-dots" class="absolute right-3 md:right-6 top-1/2 transform -translate-y-1/2 flex flex-col gap-3 z-30"></div>
            </div>

            <div class="p-6 space-y-12">
                <section>
                    <h2 id="section-title" class="text-xl font-bold mb-6 uppercase tracking-wider text-red-600 flex items-center gap-2">
                        <span class="w-1 h-6 bg-red-600 rounded"></span> Trending Now
                    </h2>
                    <div id="trending-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4"></div>
                </section>

                <div id="extra-sections" class="flex flex-col lg:flex-row gap-8">
                    <aside class="w-full lg:w-80">
                        <h2 class="text-xl font-bold mb-6 uppercase tracking-wider text-red-600">Top Airing</h2>
                        <div id="top-airing-list" class="bg-[#111] rounded-xl overflow-hidden border border-gray-800 divide-y divide-gray-800"></div>
                    </aside>
                    <section class="flex-1">
                        <h2 class="text-xl font-bold mb-6 uppercase tracking-wider text-red-600 flex items-center gap-2">
                            <span class="w-1 h-6 bg-red-600 rounded"></span> Latest Movies
                        </h2>
                        <div id="latest-movies-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4"></div>
                    </section>
                </div>
            </div>
        </div>

        <div id="movie-modal" onclick="if(event.target == this) closeModal()">
            <div class="modal-body shadow-2xl">
                <button class="close-btn shadow-lg hover:scale-110 transition" onclick="closeModal()">✕</button>
                <div id="modal-content"></div>
            </div>
        </div>

        <!-- NEW: JOIN PARTY MODAL -->
        <div id="join-party-modal" class="hidden fixed inset-0 bg-black/90 z-[100] flex items-center justify-center p-4">
            <div class="bg-[#111] border border-gray-800 p-6 rounded-xl w-full max-w-sm relative shadow-2xl">
                <button onclick="closeJoinModal()" class="absolute top-4 right-4 text-gray-400 hover:text-red-500 font-bold text-2xl">&times;</button>
                <h3 class="text-xl font-black text-red-600 uppercase tracking-wider mb-2">Join Watch Party</h3>
                <p class="text-xs text-gray-400 mb-6">Enter the 6-character room code from your host.</p>
                <form onsubmit="event.preventDefault(); submitJoinParty();" class="flex flex-col gap-4">
                    <input type="text" id="party-code-input" placeholder="e.g. HFZ2R8" class="bg-gray-900 border border-gray-700 text-white px-4 py-3 rounded-lg focus:outline-none focus:border-red-600 uppercase font-mono text-center tracking-widest font-bold text-lg" maxlength="6" required>
                    <button type="submit" class="bg-red-600 hover:bg-red-500 text-white font-bold py-3 rounded-lg uppercase tracking-widest transition shadow-[0_0_15px_rgba(255,0,0,0.4)] mt-2">Enter Room</button>
                </form>
            </div>
        </div>

        <?php include 'components/footer.php'; ?>
        
    </main>

    <script>
        let slideTimer;

        document.addEventListener('DOMContentLoaded', initData);

        async function initData() {
            try {
                const trendRes = await fetch(`api.php?route=trending/all/day`);
                const trendData = await trendRes.json();
                renderSpotlight(trendData.results.slice(0, 5));
                renderGrid(trendData.results.slice(5, 17), 'trending-grid');

                const airingRes = await fetch(`api.php?route=tv/on_the_air`);
                const airingData = await airingRes.json();
                renderAiringList(airingData.results.slice(0, 10));

                const latestRes = await fetch(`api.php?route=movie/now_playing`);
                const latestData = await latestRes.json();
                renderGrid(latestData.results.slice(0, 12), 'latest-movies-grid');
            } catch (e) {
                console.error("Failed to fetch initial data", e);
            }
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        function renderSpotlight(items) {
            const container = document.getElementById('spotlight-slides');
            const dots = document.getElementById('spotlight-dots');
            container.innerHTML = ''; dots.innerHTML = '';
            items.forEach((item, i) => {
                container.innerHTML += `
                    <div class="spotlight-slide ${i===0?'active':''}" data-index="${i}">
                        <img src="https://image.tmdb.org/t/p/original${item.backdrop_path}" class="w-full h-full object-cover object-top opacity-75">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#0b0b0b]/90 via-[#0b0b0b]/40 to-transparent md:hidden"></div>
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0b0b0b]/90 via-[#0b0b0b]/50 to-transparent hidden md:block w-2/3"></div>
                        <div class="absolute bottom-6 left-4 right-8 md:bottom-12 md:left-12 md:right-auto max-w-2xl z-10 md:p-4">
                            <h2 class="text-2xl sm:text-3xl md:text-5xl lg:text-6xl font-black mb-2 md:mb-4 uppercase tracking-tighter leading-tight drop-shadow-[0_2px_5px_rgba(0,0,0,0.8)]">${item.title || item.name}</h2>
                            <p class="text-gray-200 font-medium line-clamp-2 md:line-clamp-3 mb-4 md:mb-6 text-xs sm:text-sm drop-shadow-[0_1px_3px_rgba(0,0,0,0.8)]">${item.overview}</p>
                            <button onclick="openDetails('${item.id}', '${item.media_type}')" class="bg-red-600 px-5 py-2 md:px-8 md:py-3 rounded-lg font-bold hover:bg-red-700 transition shadow-[0_4px_15px_rgba(255,0,0,0.4)] text-sm md:text-base">DETAILS</button>
                        </div>
                    </div>`;
                dots.innerHTML += `<div class="carousel-dot ${i===0?'active':''}" onclick="showSlide(${i})"></div>`;
            });
            startAutoSlide();
        }

        function renderGrid(items, targetId) {
            const grid = document.getElementById(targetId);
            grid.innerHTML = items.map(item => `
                <div class="cursor-pointer transition-all duration-300 rounded-lg overflow-hidden poster-hover relative bg-gray-900 shadow-lg" onclick="openDetails('${item.id}', '${item.media_type || 'movie'}')">
                    <div class="aspect-[2/3] overflow-hidden">
                        <img src="${item.poster_path ? 'https://image.tmdb.org/t/p/w500'+item.poster_path : 'https://via.placeholder.com/500x750/111/ff0000?text=No+Poster'}" class="w-full h-full object-cover" loading="lazy">
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black via-black/80 to-transparent pt-10">
                        <p class="text-xs font-bold truncate drop-shadow-md">${item.title || item.name}</p>
                        <div class="flex gap-2 mt-1">
                            <span class="text-[9px] bg-red-600 px-1 rounded font-bold text-white">★ ${item.vote_average ? item.vote_average.toFixed(1) : 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderAiringList(items) {
            const list = document.getElementById('top-airing-list');
            list.innerHTML = items.map((item, i) => `
                <div class="airing-item p-3 flex items-center gap-4 cursor-pointer transition-all border-l-4 border-transparent" onclick="openDetails('${item.id}', 'tv')">
                    <span class="text-xl font-black text-gray-700 w-6">${(i+1).toString().padStart(2,'0')}</span>
                    <img src="${item.poster_path ? 'https://image.tmdb.org/t/p/w200'+item.poster_path : 'https://via.placeholder.com/200x300/111/ff0000?text=No'}" class="w-12 h-16 object-cover rounded shadow-md">
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-bold truncate">${item.name}</h4>
                        <div class="flex items-center gap-2 mt-1 text-[10px]">
                            <span class="text-red-500 font-bold">VOTES: ${item.vote_count}</span>
                            <span class="text-gray-500">• TV</span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function openDetails(id, type) {
            const modal = document.getElementById('movie-modal');
            const content = document.getElementById('modal-content');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            content.innerHTML = '<div class="p-20 text-center text-red-600 font-bold animate-pulse uppercase">Fetching Data...</div>';

            try {
                const res = await fetch(`api.php?route=${type}/${id}`);
                const d = await res.json();

                content.innerHTML = `
                    <div class="h-64 relative">
                        <img src="${d.backdrop_path || d.poster_path ? 'https://image.tmdb.org/t/p/original'+(d.backdrop_path || d.poster_path) : 'https://via.placeholder.com/1280x720/111/111'}" class="w-full h-full object-cover opacity-50">
                        <div class="absolute inset-0 bg-gradient-to-t from-[#111] to-transparent"></div>
                        <img src="${d.poster_path ? 'https://image.tmdb.org/t/p/w300'+d.poster_path : 'https://via.placeholder.com/300x450/111/ff0000?text=No+Poster'}" class="absolute bottom-[-30px] left-8 w-28 h-40 rounded shadow-2xl border-2 border-gray-800 object-cover z-20">
                    </div>
                    <div class="px-8 pb-8 pt-12 relative">
                        <h2 class="text-2xl font-black text-red-600 mb-2 uppercase tracking-tighter">${d.title || d.name}</h2>
                        
                        <div class="bg-gray-900/50 p-4 rounded-lg text-sm text-gray-300 leading-relaxed border-l-4 border-red-600 my-4 max-h-40 overflow-y-auto">
                            <strong>OVERVIEW:</strong><br>${d.overview || 'No description available.'}
                        </div>

                        <h4 class="border-b border-gray-800 pb-2 mb-2 font-bold text-gray-400 uppercase text-xs tracking-widest">Technical Data</h4>
                        <table class="meta-table mb-6">
                            <tr><td class="label">Release Date</td><td>${d.release_date || d.first_air_date || 'Unknown'}</td></tr>
                            <tr><td class="label">Status</td><td>${d.status || 'Unknown'}</td></tr>
                            <tr><td class="label">Genres</td><td>${d.genres ? d.genres.map(g => g.name).join(', ') : 'N/A'}</td></tr>
                            <tr><td class="label">Rating</td><td class="text-red-500 font-bold">★ ${d.vote_average ? d.vote_average.toFixed(1) : 'N/A'}</td></tr>
                        </table>

                        <div class="flex gap-4 mt-6">
                            <button onclick="window.location.href='pages/watch.php?id=${id}&type=${type}'" class="flex-1 py-4 bg-red-600 text-white rounded-lg font-black text-lg hover:bg-red-700 shadow-lg shadow-red-900/40 transition-all uppercase">
                                ▶ PLAY
                            </button>
                            <button onclick="addToWatchlist('${id}', '${type}', '${(d.title || d.name || '').replace(/'/g, "\\'")}')" class="py-4 px-6 bg-gray-800 text-white rounded-lg font-bold hover:bg-gray-700 border border-gray-600 transition">
                                + My List
                            </button>
                        </div>
                    </div>`;
            } catch(e) {
                content.innerHTML = '<div class="p-20 text-center text-red-600 font-bold uppercase">Error loading details.</div>';
            }
        }

        async function addToWatchlist(id, type, title) {
            try {
                const res = await fetch(`pages/add_to_watchlist.php?media_id=${id}&type=${type}&title=${encodeURIComponent(title)}`);
                const data = await res.json();
                alert(data.message || data.status);
            } catch(e) {
                console.error(e);
            }
        }

        function closeModal() {
            document.getElementById('movie-modal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function showSlide(index) {
            const slides = document.querySelectorAll('.spotlight-slide');
            const dots = document.querySelectorAll('.carousel-dot');
            slides.forEach((s, i) => {
                s.classList.toggle('active', i === index);
                dots[i].classList.toggle('active', i === index);
            });
        }

        function startAutoSlide() {
            clearInterval(slideTimer);
            let current = 0;
            slideTimer = setInterval(() => {
                current = (current + 1) % 5;
                showSlide(current);
            }, 5000);
        }

        async function searchContent() {
            const query = document.getElementById('searchInput').value;
            if(!query) return;
            
            try {
                const res = await fetch(`api.php?route=search/multi&query=${encodeURIComponent(query)}`);
                const data = await res.json();
                
                document.getElementById('spotlight-section').style.display = 'none';
                document.getElementById('extra-sections').style.display = 'none';
                
                document.getElementById('section-title').innerHTML = `
                    <span class="w-1 h-6 bg-red-600 rounded"></span> Search Results: "${query}"
                `;
                
                renderGrid(data.results.filter(i => i.poster_path), 'trending-grid');
            } catch (e) {
                console.error("Search failed", e);
            }
        }

        // --- NEW JOIN PARTY JAVASCRIPT ---
        function openJoinModal() {
            document.getElementById('join-party-modal').classList.remove('hidden');
            setTimeout(() => document.getElementById('party-code-input').focus(), 100);
            if(window.innerWidth < 1024) toggleSidebar(); 
        }

        function closeJoinModal() {
            document.getElementById('join-party-modal').classList.add('hidden');
            document.getElementById('party-code-input').value = '';
        }

        function submitJoinParty() {
            const code = document.getElementById('party-code-input').value.trim().toUpperCase();
            if(code.length === 6) {
                // Instantly redirect to the watch party page with the entered code
                window.location.href = `pages/watch-party.php?room=${code}`;
            } else {
                alert('Please enter a valid 6-character room code.');
            }
        }
    </script>
</body>
</html>