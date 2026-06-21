<?php
session_start();
require '../db.php';

$tmdbId = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'movie'; 
$season = $_GET['season'] ?? 1;
$episode = $_GET['episode'] ?? 1;

if (!$tmdbId) die("<h1 style='color:red; text-align:center; margin-top:50px; font-family:sans-serif;'>Error: No Media ID provided.</h1>");

$resume_time = 0;
if (isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $hist_check = $conn->query("SELECT current_time FROM movie_history WHERE user_id=$uid AND media_id=$tmdbId AND media_type='$type' AND season=$season AND episode=$episode");
    if ($hist_check && $hist_check->num_rows > 0) {
        $row = $hist_check->fetch_assoc();
        $resume_time = isset($row['current_time']) ? intval($row['current_time']) : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watching - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --accent: #ff0000; }
        body { background-color: var(--bg-color); color: #fff; font-family: 'Inter', sans-serif; }
        .red-text { color: var(--accent); text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
        .video-container { position: relative; width: 100%; padding-top: 56.25%; background: #000; border-radius: 12px; overflow: hidden; box-shadow: 0 0 30px rgba(255, 0, 0, 0.15); border: 1px solid rgba(255,0,0,0.2); z-index: 40; }
        iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; outline: none; }
        #theatre-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 35; display: none; opacity: 0; transition: opacity 0.5s ease; }
        body.theatre-active #theatre-overlay { display: block; opacity: 1; }
        body.theatre-active .video-container { transform: scale(1.02); z-index: 50; box-shadow: 0 0 50px rgba(255, 0, 0, 0.3); border-color: rgba(255,0,0,0.5); }
        .server-active { background-color: var(--accent); color: #fff; font-weight: bold; box-shadow: 0 0 15px rgba(255,0,0,0.4); border: 1px solid var(--accent); }
        .server-inactive { background-color: #1a1a1a; color: #9ca3af; border: 1px solid #333; }
        .server-inactive:hover { color: #fff; border-color: var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
        .ep-list-scroll::-webkit-scrollbar-track { background: #111; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

    <div id="theatre-overlay"></div>

    <header class="lg:hidden sticky top-0 z-[60] bg-[#111] h-16 border-b border-gray-800 flex justify-between items-center px-4 shadow-md">
        <h1 class="text-xl font-black red-text tracking-widest uppercase">ZENTRIX</h1>
        <button onclick="toggleSidebar()" class="text-red-500 focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </header>

    <div class="flex flex-1 w-full relative min-h-screen">
        
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
                <a href="continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🕒 Continue-Watch</a>
            </nav>
            <div class="p-4 border-t border-gray-800 shrink-0">
                <a href="../components/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-600/80 hover:bg-red-600 rounded-lg transition-colors uppercase tracking-wide">Logout</a>
            </div>
        </aside>

        <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[65] hidden lg:hidden" onclick="toggleSidebar()"></div>

        <main class="flex-1 w-full min-w-0 flex flex-col">
            <header class="hidden lg:flex h-20 border-b border-gray-800 bg-[#0f0f0f] px-6 justify-between items-center z-20 shrink-0">
                <h1 class="text-xl font-bold tracking-[0.2em] red-text uppercase">WATCHING</h1>
                <a href="../index.php" class="text-sm font-bold text-gray-400 hover:text-red-500 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    BACK TO BROWSE
                </a>
            </header>

            <div class="p-4 md:p-6 lg:p-8 max-w-6xl mx-auto w-full flex-1">
                
                <div class="video-container mb-6 bg-black relative">
                    <div id="video-loader" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/80 backdrop-blur-sm hidden">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-red-600 mb-4"></div>
                        <p class="text-red-500 font-bold tracking-widest animate-pulse uppercase text-sm">Connecting...</p>
                    </div>
                    <iframe id="video-frame" allowfullscreen allow="autoplay; fullscreen; encrypted-media; picture-in-picture" class="z-10"></iframe>
                </div>

                <div class="flex justify-between items-center bg-gray-900 border border-gray-800 p-3 rounded-lg mb-8 shadow-md">
                    <div class="flex gap-2" id="tv-controls" style="display: <?= $type === 'tv' ? 'flex' : 'none' ?>;">
                        <button onclick="navigateEp(-1)" id="prev-btn" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-bold transition flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">
                            ❮ PREV
                        </button>
                        <button onclick="navigateEp(1)" id="next-btn" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded text-xs font-bold transition flex items-center gap-1 shadow-[0_0_10px_rgba(255,0,0,0.3)] disabled:opacity-50 disabled:cursor-not-allowed">
                            NEXT ❯
                        </button>
                    </div>
                    <div style="display: <?= $type === 'movie' ? 'block' : 'none' ?>;"></div> 
                    <button onclick="toggleTheatreMode()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-bold transition flex items-center gap-2 border border-gray-700 hover:border-red-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg>
                        THEATRE MODE
                    </button>
                </div>

                <div class="bg-[#111] p-6 rounded-xl border border-gray-800 shadow-xl mb-8">
                    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-6 border-b border-gray-800 pb-6">
                        
                        <div class="flex flex-col gap-3 w-full xl:w-auto">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest border-l-2 border-red-600 pl-2">
                                <span class="text-red-500">Note:</span> Try different servers if one fails!
                            </span>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="changeServer('peachify')" id="btn-peachify" class="px-5 py-2 rounded transition text-xs server-active">PEACHIFY</button>
                                <button onclick="changeServer('vidzen')" id="btn-vidzen" class="px-5 py-2 rounded transition text-xs server-inactive">VIDZEN</button>
                                <button onclick="changeServer('vidnest')" id="btn-vidnest" class="px-5 py-2 rounded transition text-xs server-inactive">VIDNEST</button>
                                <button onclick="changeServer('vixsrc')" id="btn-vixsrc" class="px-5 py-2 rounded transition text-xs server-inactive">VIXSRC</button>
                                <button onclick="changeServer('filmu')" id="btn-filmu" class="px-5 py-2 rounded transition text-xs server-inactive">FILMU</button>
                            </div>
                        </div>

                        <div class="flex gap-3 w-full xl:w-auto">
                            <button onclick="createWatchParty()" class="flex-1 xl:flex-none justify-center bg-purple-600 hover:bg-purple-500 text-white px-5 py-2.5 rounded-lg font-bold border border-purple-500 transition shadow-[0_0_15px_rgba(147,51,234,0.4)] whitespace-nowrap flex items-center gap-2 uppercase tracking-wider text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                Watch Party
                            </button>

                            <button id="watchlist-btn" onclick="toggleWatchlist()" class="flex-1 xl:flex-none justify-center bg-gray-800 hover:bg-gray-700 text-white px-5 py-2.5 rounded-lg font-bold border border-gray-600 hover:border-red-600 transition shadow-md whitespace-nowrap flex items-center gap-2 text-xs uppercase tracking-wider">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span id="watchlist-text">Add to List</span>
                            </button>
                        </div>
                    </div>
                    
                    <h2 id="media-title" class="text-2xl md:text-3xl font-black text-white uppercase tracking-wider mb-3">Loading...</h2>
                    <p id="media-overview" class="text-sm text-gray-400 line-clamp-3 leading-relaxed"></p>
                </div>

                <?php if($type === 'tv'): ?>
                <div class="mb-10 w-full block clear-both">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <h3 class="text-lg font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <span class="w-1 h-5 bg-red-600 rounded"></span> Season Episodes
                        </h3>
                        <select id="season-selector" onchange="changeSeason()" class="font-bold text-sm bg-gray-900 border border-gray-700 text-white p-2 rounded focus:outline-none focus:border-red-600">
                            <option value="<?= $season ?>">Season <?= $season ?></option>
                        </select>
                    </div>
                    
                    <div class="bg-[#111] border border-gray-800 rounded-xl overflow-hidden shadow-lg">
                        <div id="dynamic-episode-list" class="max-h-[500px] overflow-y-auto ep-list-scroll p-2 space-y-2">
                            <div class="text-center py-10 text-gray-500 animate-pulse text-sm font-bold">Loading Episodes...</div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-[#111] p-4 md:p-6 rounded-xl border border-gray-800 shadow-xl mb-10 w-full block clear-both">
                    <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-6 flex items-center gap-2">
                        <span class="w-1 h-5 bg-red-600 rounded"></span> Community Discussion
                    </h3>
                    
                    <form id="comment-form" class="mb-8 relative">
                        <textarea id="comment-text" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-4 text-white focus:outline-none focus:border-red-600 resize-none" rows="3" placeholder="What did you think of this <?= $type === 'movie' ? 'movie' : 'episode' ?>?" required></textarea>
                        <button type="submit" class="absolute bottom-4 right-4 bg-red-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-red-500 transition shadow-[0_0_10px_rgba(255,0,0,0.4)]">Post</button>
                    </form>

                    <div id="comments-list" class="space-y-4 max-h-[500px] overflow-y-auto pr-2 ep-list-scroll">
                        <div class="text-center text-gray-500 text-sm font-bold animate-pulse">Loading comments...</div>
                    </div>
                </div>

                <div class="mb-8 block w-full clear-both">
                    <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span class="w-1 h-5 bg-red-600 rounded"></span> You May Also Like
                    </h3>
                    <div id="recommendations-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <div class="col-span-full py-6 text-gray-500 font-bold text-sm animate-pulse text-center">Loading Recommendations...</div>
                    </div>
                </div>

            </div>
            
            <?php include '../components/footer.php'; ?>
        </main>
    </div>

    <script>
        const tmdbId = "<?= $tmdbId ?>";
        const mediaType = "<?= $type ?>";
        
        let currentSeason = parseInt("<?= $season ?>") || 1;
        let currentEpisode = parseInt("<?= $episode ?>") || 1;
        let totalEpisodesAvailable = 1;
        
        let currentServer = 'peachify';
        let fullMediaTitle = "";
        let isTheatreMode = false;

        let exactTimeSeconds = <?= $resume_time ?>; 
        let mediaDurationSeconds = mediaType === 'movie' ? 7200 : 2700; 
        let progressInterval = null;

        document.addEventListener('DOMContentLoaded', () => {
            loadVideo();
            fetchMediaDetails();
            fetchRecommendations();
            loadComments(); 
            if (mediaType === 'tv') fetchSeasonEpisodes();
            
            startProgressTracker();
        });

        function startProgressTracker() {
            if(progressInterval) clearInterval(progressInterval);
            progressInterval = setInterval(() => {
                if (!document.hidden) {
                    exactTimeSeconds += 10; 
                    saveWatchHistory(exactTimeSeconds, mediaDurationSeconds);
                }
            }, 10000); 
        }

        function saveWatchHistory(currentSecs, durationSecs) {
            const params = new URLSearchParams();
            params.append('media_id', tmdbId);
            params.append('media_type', mediaType);
            params.append('season', currentSeason);
            params.append('episode', currentEpisode);
            params.append('time', Math.floor(currentSecs));
            params.append('duration', Math.floor(durationSecs));

            fetch(`save_history.php?${params.toString()}`, { method: 'GET', keepalive: true }).catch(e => console.error(e));
        }

        window.addEventListener('beforeunload', () => {
            saveWatchHistory(exactTimeSeconds, mediaDurationSeconds);
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        function toggleTheatreMode() {
            isTheatreMode = !isTheatreMode;
            if(isTheatreMode) {
                document.body.classList.add('theatre-active');
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                document.body.classList.remove('theatre-active');
            }
        }

        document.getElementById('comment-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const text = document.getElementById('comment-text').value.trim();
            if(!text) return;

            const params = new URLSearchParams();
            params.append('item_id', tmdbId);
            params.append('cat', mediaType);
            params.append('s', currentSeason);
            params.append('e', currentEpisode);
            params.append('msg', text); 

            try {
                const res = await fetch(`save_note.php?${params.toString()}`, { method: 'GET' });
                const data = await res.json();
                if(data.status === 'success') {
                    document.getElementById('comment-text').value = '';
                    loadComments(); 
                } else {
                    alert(data.message || 'Error posting comment.');
                }
            } catch(err) {
                alert('Connection dropped entirely by the server.');
            }
        });

        async function loadComments() {
            const list = document.getElementById('comments-list');
            try {
                const res = await fetch(`get_movie_comments.php?media_id=${tmdbId}&media_type=${mediaType}&season=${currentSeason}&episode=${currentEpisode}`);
                const comments = await res.json();
                if (comments.length === 0) {
                    list.innerHTML = '<div class="text-center text-gray-600 text-sm italic py-4">Be the first to comment!</div>';
                    return;
                }
                list.innerHTML = comments.map(c => `
                    <div class="bg-gray-900 p-4 rounded-lg border border-gray-800">
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold text-red-500 text-sm">${c.username}</span>
                            <span class="text-xs text-gray-500">${new Date(c.created_at).toLocaleDateString()}</span>
                        </div>
                        <p class="text-gray-300 text-sm whitespace-pre-wrap">${c.comment_text.replace(/</g, "&lt;").replace(/>/g, "&gt;")}</p>
                    </div>
                `).join('');
            } catch(err) {
                list.innerHTML = '<div class="text-red-500 text-sm">Failed to load comments.</div>';
            }
        }

        async function fetchMediaDetails() {
            try {
                const res = await fetch(`../api.php?route=${mediaType}/${tmdbId}`);
                const data = await res.json();
                
                fullMediaTitle = data.title || data.name;
                document.getElementById('media-title').innerText = fullMediaTitle;
                document.getElementById('media-overview').innerText = data.overview || 'No synopsis available.';
                
                if (mediaType === 'movie' && data.runtime > 0) {
                    mediaDurationSeconds = data.runtime * 60;
                } else if (mediaType === 'tv' && data.episode_run_time && data.episode_run_time.length > 0) {
                    mediaDurationSeconds = data.episode_run_time[0] * 60;
                }
                
                if (mediaType === 'tv' && data.seasons) {
                    const select = document.getElementById('season-selector');
                    select.innerHTML = data.seasons.filter(s => s.season_number > 0).map(s => 
                        `<option value="${s.season_number}" ${s.season_number === currentSeason ? 'selected' : ''}>Season ${s.season_number}</option>`
                    ).join('');
                }
            } catch (err) {}
        }

        function changeSeason() {
            currentSeason = parseInt(document.getElementById('season-selector').value);
            currentEpisode = 1; 
            const newUrl = window.location.pathname + `?id=${tmdbId}&type=tv&season=${currentSeason}&episode=${currentEpisode}`;
            window.history.pushState({path:newUrl},'',newUrl);
            exactTimeSeconds = 0; 
            startProgressTracker();
            fetchSeasonEpisodes();
            loadVideo();
            loadComments();
        }

        async function fetchSeasonEpisodes() {
            if (mediaType !== 'tv') return;
            const listContainer = document.getElementById('dynamic-episode-list');
            listContainer.innerHTML = '<div class="text-center py-10 text-gray-500 animate-pulse text-sm font-bold">Loading Episodes...</div>';

            try {
                const res = await fetch(`../api.php?route=tv/${tmdbId}/season/${currentSeason}`);
                const data = await res.json();
                
                if (data.episodes && data.episodes.length > 0) {
                    totalEpisodesAvailable = data.episodes.length;
                    updateNavButtons();

                    listContainer.innerHTML = data.episodes.map(ep => {
                        const isActive = (currentEpisode === ep.episode_number);
                        const activeClasses = isActive 
                            ? 'border-red-600 bg-gray-800 shadow-[0_0_15px_rgba(255,0,0,0.15)]' 
                            : 'border-transparent bg-gray-900/50 hover:bg-gray-800 hover:border-red-600/50';

                        const playingBadge = isActive 
                            ? `<div class="absolute top-2 left-2 bg-red-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded shadow-md uppercase tracking-widest z-10 animate-pulse flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full block"></span> Playing
                               </div>` 
                            : '';

                        const thumbUrl = ep.still_path ? `https://image.tmdb.org/t/p/w300${ep.still_path}` : 'https://via.placeholder.com/300x170/111/ff0000?text=No+Image';

                        return `
                            <div id="ep-card-${ep.episode_number}" onclick="playEpisode(${ep.episode_number})" class="flex items-center gap-3 md:gap-5 p-2 md:p-3 rounded-xl transition duration-300 cursor-pointer border group ${activeClasses}">
                                <div class="relative w-28 md:w-44 aspect-video rounded-lg overflow-hidden shrink-0 shadow-md bg-black">
                                    <img src="${thumbUrl}" alt="Thumbnail" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">
                                    ${playingBadge}
                                    <div class="absolute inset-0 bg-black/40 group-hover:bg-transparent transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <div class="bg-red-600/90 rounded-full p-2 shadow-[0_0_15px_rgba(255,0,0,0.8)] backdrop-blur-sm">
                                            <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                    </div>
                                    <div class="absolute bottom-1 right-1 bg-black/90 backdrop-blur-md px-1.5 py-0.5 text-[10px] font-bold text-white rounded border border-gray-700">EP ${ep.episode_number}</div>
                                </div>
                                <div class="flex-1 min-w-0 py-1">
                                    <h4 class="text-sm md:text-base font-bold text-gray-100 truncate group-hover:text-red-500 transition-colors ${isActive ? 'text-red-500' : ''}">
                                        ${ep.episode_number}. ${ep.name}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1 line-clamp-2 hidden md:-webkit-box md:max-w-2xl">${ep.overview || 'No synopsis available.'}</p>
                                </div>
                            </div>
                        `;
                    }).join('');

                    setTimeout(() => {
                        const activeCard = document.getElementById(`ep-card-${currentEpisode}`);
                        if (activeCard) activeCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 500);

                } else {
                    listContainer.innerHTML = '<div class="text-center py-10 text-gray-500 font-bold">No episodes found for this season.</div>';
                }
            } catch(e) {
                listContainer.innerHTML = '<div class="text-center py-10 text-red-500 font-bold">Failed to load episodes.</div>';
            }
        }

        function updateNavButtons() {
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            if(prevBtn) prevBtn.disabled = (currentEpisode <= 1);
            if(nextBtn) nextBtn.disabled = (currentEpisode >= totalEpisodesAvailable);
        }

        function navigateEp(direction) {
            let newEp = currentEpisode + direction;
            if (newEp >= 1 && newEp <= totalEpisodesAvailable) playEpisode(newEp);
        }

        function playEpisode(epNum) {
            currentEpisode = parseInt(epNum);
            const newUrl = window.location.pathname + `?id=${tmdbId}&type=tv&season=${currentSeason}&episode=${currentEpisode}`;
            window.history.pushState({path:newUrl},'',newUrl);
            exactTimeSeconds = 0; 
            startProgressTracker();
            fetchSeasonEpisodes(); 
            loadVideo();
            loadComments();
        }

        async function fetchRecommendations() {
            try {
                const res = await fetch(`../api.php?route=${mediaType}/${tmdbId}/recommendations`);
                const data = await res.json();
                const grid = document.getElementById('recommendations-grid');
                if (data.results && data.results.length > 0) {
                    grid.innerHTML = data.results.slice(0, 10).map(item => `
                        <a href="watch.php?id=${item.id}&type=${mediaType}" class="block group relative overflow-hidden rounded-lg bg-gray-900 transition-all duration-300 hover:scale-105 hover:border-red-600 border border-gray-800 shadow-lg">
                            <div class="aspect-[2/3] bg-[#111] relative">
                                <img src="${item.poster_path ? 'https://image.tmdb.org/t/p/w300' + item.poster_path : 'https://via.placeholder.com/300x450/111/ff0000?text=No+Poster'}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition">
                            </div>
                            <div class="absolute bottom-0 p-3 w-full bg-gradient-to-t from-black via-black/80 to-transparent pt-10">
                                <div class="text-xs font-bold truncate text-white drop-shadow-md">${item.title || item.name}</div>
                            </div>
                        </a>
                    `).join('');
                } else {
                    grid.innerHTML = '<div class="col-span-full py-6 text-gray-500 font-bold text-sm text-center">No recommendations available.</div>';
                }
            } catch (e) {}
        }

        function changeServer(serverName) {
            currentServer = serverName;
            ['peachify', 'vidzen', 'vidnest', 'vixsrc', 'filmu'].forEach(s => {
                const btn = document.getElementById(`btn-${s}`);
                if(btn) btn.className = s === serverName ? 'px-5 py-2 rounded transition text-xs server-active' : 'px-5 py-2 rounded transition text-xs server-inactive';
            });
            loadVideo();
        }

        function loadVideo() {
            const iframe = document.getElementById('video-frame');
            const loader = document.getElementById('video-loader');
            
            iframe.classList.add('hidden');
            loader.classList.remove('hidden');

            let embedUrl = "";
            let tParam = exactTimeSeconds > 10 ? `?t=${exactTimeSeconds}` : ''; 
            
            if (mediaType === 'movie') {
                switch(currentServer) {
                    case 'peachify': embedUrl = `https://peachify.top/embed/movie/${tmdbId}${tParam}`; break;
                    case 'vidzen': embedUrl = `https://vidzen.fun/movie/${tmdbId}${tParam}`; break;
                    case 'vidnest': embedUrl = `https://vidnest.fun/movie/${tmdbId}${tParam}`; break;
                    case 'vixsrc': embedUrl = `https://vixsrc.to/movie/${tmdbId}${tParam}`; break;
                    case 'filmu': embedUrl = `https://embed.filmu.in/movie/${tmdbId}`; break;
                }
            } else {
                switch(currentServer) {
                    case 'peachify': embedUrl = `https://peachify.top/embed/tv/${tmdbId}/${currentSeason}/${currentEpisode}${tParam}`; break;
                    case 'vidzen': embedUrl = `https://vidzen.fun/tv/${tmdbId}/${currentSeason}/${currentEpisode}${tParam}`; break;
                    case 'vidnest': embedUrl = `https://vidnest.fun/tv/${tmdbId}/${currentSeason}/${currentEpisode}${tParam}`; break;
                    case 'vixsrc': embedUrl = `https://vixsrc.to/tv/${tmdbId}/${currentSeason}/${currentEpisode}${tParam}`; break;
                    case 'filmu': embedUrl = `https://embed.filmu.in/tv/${tmdbId}/${currentSeason}/${currentEpisode}`; break;
                }
            }
            
            iframe.src = embedUrl;
            iframe.onload = () => {
                loader.classList.add('hidden');
                iframe.classList.remove('hidden');
            };
        }

        // --- FIXED: ADD TO WATCHLIST GET REQUEST ---
        async function toggleWatchlist() {
            const btnText = document.getElementById('watchlist-text');
            const btn = document.getElementById('watchlist-btn');
            btnText.innerHTML = 'Wait...';

            try {
                // Use GET parameters directly in the URL to bypass strict firewalls
                const params = new URLSearchParams({
                    media_id: tmdbId,
                    type: mediaType,
                    title: fullMediaTitle,
                    _t: Date.now()
                });

                const res = await fetch(`add_to_watchlist.php?${params.toString()}`);
                const data = await res.json();
                
                if (data.status === 'added') {
                    btn.className = 'flex-1 xl:flex-none justify-center bg-red-600 hover:bg-red-500 text-white px-5 py-2.5 rounded-lg font-bold border border-red-600 transition shadow-[0_0_15px_rgba(255,0,0,0.4)] whitespace-nowrap flex items-center gap-2 text-xs uppercase tracking-wider';
                    btnText.innerHTML = '✓ Added';
                } else if (data.status === 'removed') {
                    btn.className = 'flex-1 xl:flex-none justify-center bg-gray-800 hover:bg-gray-700 text-white px-5 py-2.5 rounded-lg font-bold border border-gray-600 hover:border-red-600 transition shadow-md whitespace-nowrap flex items-center gap-2 text-xs uppercase tracking-wider';
                    btnText.innerHTML = 'Add to List';
                } else {
                    alert(data.message || 'Error updating list.');
                    btnText.innerHTML = 'Add to List';
                }
            } catch(e) {
                alert("Error connecting. Are you logged in?");
                btnText.innerHTML = 'Add to List';
            }
        }

        // --- FIXED: WATCH PARTY CREATION GET REQUEST WITH SHIELD ---
        async function createWatchParty() {
            if(!confirm("Create a private room to watch with friends?")) return;
            
            // Bypass InfinityFree's POST blocker by putting data directly in the URL
            const params = new URLSearchParams({
                action: 'create_room',
                media_id: tmdbId,
                media_type: mediaType,
                season: currentSeason,
                episode: currentEpisode,
                _t: Date.now()
            });

            try {
                const res = await fetch(`party_action.php?${params.toString()}`);
                const text = await res.text(); 
                
                try {
                    // Slice through the shield to get the pure JSON data inside
                    const match = text.match(/---ZENTRIX-START---(.*?)---ZENTRIX-END---/s);
                    
                    if (!match) {
                        alert("Server error or tracking script blocked the response.");
                        console.error("RAW OUTPUT:", text);
                        return;
                    }

                    const data = JSON.parse(match[1]);
                    
                    if(data.status === 'success') {
                        // Success! Redirect to the newly created room!
                        window.location.href = `watch-party.php?room=${data.room_code}`;
                    } else {
                        alert("Server Rejected: " + (data.message || "Unknown error"));
                    }
                } catch(err) {
                    console.error("RAW OUTPUT:", text);
                    alert("CRASH LOG:\n\n" + text.substring(0, 150));
                }
            } catch(e) {
                alert("Network connection error. Check your internet.");
            }
        }
    </script>
</body>
</html>