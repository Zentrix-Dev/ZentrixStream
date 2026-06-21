<?php
$id = $_GET['id'] ?? null;
$ep = $_GET['ep'] ?? 1;

if (!$id) {
    die("<h1 style='color:red; text-align:center; margin-top:50px; font-family:sans-serif;'>Error: No Anime ID provided.</h1>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Anime - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --accent: #00f3ff; --text-main: #ffffff; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .cyan-text { color: var(--accent); text-shadow: 0 0 10px rgba(0, 243, 255, 0.4); }
        .video-container { position: relative; width: 100%; padding-top: 56.25%; background: #000; border-radius: 12px; overflow: hidden; box-shadow: 0 0 30px rgba(0, 243, 255, 0.15); border: 1px solid rgba(0,243,255,0.2); z-index: 40; transition: all 0.5s ease; }
        iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; outline: none; }
        #theatre-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.95); z-index: 35; display: none; opacity: 0; transition: opacity 0.5s ease; }
        body.theatre-active #theatre-overlay { display: block; opacity: 1; }
        body.theatre-active .video-container { transform: scale(1.02); box-shadow: 0 0 50px rgba(0, 243, 255, 0.3); border-color: rgba(0,243,255,0.5); }
        .server-active { background-color: var(--accent); color: #000; font-weight: bold; box-shadow: 0 0 15px rgba(0,243,255,0.4); border: 1px solid var(--accent); }
        .server-inactive { background-color: #1a1a1a; color: #9ca3af; border: 1px solid #333; }
        .server-inactive:hover { color: #fff; border-color: var(--accent); }
        .ep-list-scroll::-webkit-scrollbar { width: 8px; }
        .ep-list-scroll::-webkit-scrollbar-track { background: #111; border-radius: 8px; }
        .ep-list-scroll::-webkit-scrollbar-thumb { background: #333; border-radius: 8px; }
        .ep-list-scroll::-webkit-scrollbar-thumb:hover { background: var(--accent); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,243,255,0.2); border: 1px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0b0b0b] min-h-screen text-white font-sans flex flex-col lg:flex-row">

    <div id="theatre-overlay"></div>

    <div class="lg:hidden sticky top-0 w-full bg-[#111] border-b border-gray-800 p-4 flex justify-between items-center z-50">
        <h1 class="text-xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        <button onclick="toggleSidebar()" class="text-[#00f3ff] focus:outline-none">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
        </button>
    </div>

    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-50 flex flex-col h-screen lg:sticky lg:top-0 shrink-0">
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
            <h1 class="text-2xl font-black cyan-text tracking-widest uppercase">ZENTRIX</h1>
        </div>
        
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🏠 Home</a>
            <a href="watchlist.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">📌 My List</a>
            <a href="login.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔒 Signup/Login</a>
            <a href="profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">👨 Profile</a>
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

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 w-full min-w-0 flex flex-col">
        <header class="hidden lg:flex p-6 border-b border-gray-800 bg-[#0f0f0f] justify-between items-center z-20">
            <h1 class="text-xl font-bold tracking-[0.2em] cyan-text uppercase">WATCHING</h1>
            <a href="../index.php" class="text-sm font-bold text-gray-400 hover:text-[#00f3ff] transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                BACK TO BROWSE
            </a>
        </header>

        <div class="p-4 md:p-6 max-w-6xl mx-auto w-full flex-1">
            <div class="video-container mb-4 bg-black relative">
                <div id="video-loader" class="absolute inset-0 z-20 flex flex-col items-center justify-center bg-black/80 backdrop-blur-sm hidden">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-[#00f3ff] mb-4"></div>
                    <p class="text-[#00f3ff] font-bold text-sm tracking-widest animate-pulse uppercase">Connecting Server...</p>
                </div>
                <iframe id="video-frame" allowfullscreen allow="autoplay; fullscreen; encrypted-media; picture-in-picture" class="z-10"></iframe>
            </div>

            <div class="flex justify-between items-center bg-gray-900 border border-gray-800 p-3 rounded-lg mb-8 shadow-md">
                <div class="flex gap-2">
                    <button onclick="navigateEp(-1)" id="prev-btn" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-bold transition flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed">❮ PREV</button>
                    <button onclick="navigateEp(1)" id="next-btn" class="px-4 py-2 bg-[#00f3ff] hover:bg-cyan-400 text-black rounded text-xs font-bold transition flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed shadow-[0_0_10px_rgba(0,243,255,0.3)]">NEXT ❯</button>
                </div>
                <button onclick="toggleTheatreMode()" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded text-xs font-bold transition flex items-center gap-2 border border-gray-700 hover:border-[#00f3ff]">
                    THEATRE MODE
                </button>
            </div>

            <div class="bg-[#111] p-6 rounded-xl border border-gray-800 shadow-xl mb-8">
                <div class="flex flex-col md:flex-row justify-between items-start gap-6 mb-8 border-b border-gray-800 pb-6">
                    <div class="flex flex-col gap-4">
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest border-l-2 border-[#00f3ff] pl-2"><span class="text-[#00f3ff]">Note:</span> If a Server isn't working, try another!</span>
                        <div class="flex flex-wrap gap-2">
                            <button onclick="changeServer('megaplay')" id="btn-megaplay" class="px-5 py-2 rounded transition text-xs server-active">MEGAPLAY</button>
                            <button onclick="changeServer('vidnest')" id="btn-vidnest" class="px-5 py-2 rounded transition text-xs server-inactive">VIDNEST</button>
                            <button onclick="changeServer('animepahe')" id="btn-animepahe" class="px-5 py-2 rounded transition text-xs server-inactive">ANIMEPAHE</button>
                            <button onclick="changeServer('vidplays')" id="btn-vidplays" class="px-5 py-2 rounded transition text-xs server-inactive">VIDPLAYS</button>
                            <button onclick="changeServer('filmu')" id="btn-filmu" class="px-5 py-2 rounded transition text-xs server-inactive">FILMU</button>
                            <button onclick="changeServer('4animo')" id="btn-4animo" class="px-5 py-2 rounded transition text-xs server-inactive">4ANIMO</button>
                        </div>
                        <div id="lang-selector">
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest block mb-2 mt-2 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-[#00f3ff]"></span> Language</span>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="changeLang('sub')" id="btn-sub" class="px-6 py-2 rounded transition text-xs server-active tracking-wider">SUB</button>
                                <button onclick="changeLang('dub')" id="btn-dub" class="px-6 py-2 rounded transition text-xs server-inactive tracking-wider">DUB</button>
                            </div>
                        </div>
                    </div>
                    <button onclick="addToWatchlist()" id="watchlist-btn" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-bold border border-gray-600 hover:border-[#00f3ff] transition shadow-md whitespace-nowrap self-start mt-4 lg:mt-0">+ My List</button>
                </div>

                <div>
                    <h2 id="media-title" class="text-2xl md:text-3xl font-black text-white uppercase tracking-wider mb-2">Loading...</h2>
                    <div class="flex items-center gap-3 mb-4">
                        <span id="media-status" class="text-xs text-[#00f3ff] font-bold tracking-widest uppercase"></span>
                        <span id="media-rating" class="text-xs bg-gray-800 text-white px-2 py-1 rounded font-bold"></span>
                    </div>
                    <p id="media-desc" class="text-sm text-gray-400 line-clamp-3 leading-relaxed"></p>
                </div>
            </div>

            <div class="mb-10">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <h3 class="text-lg font-bold text-white uppercase tracking-wider flex items-center gap-2"><span class="w-1 h-5 bg-[#00f3ff] rounded"></span> Select Episode</h3>
                    <select id="ep-range-selector" onchange="changeEpRange()" class="font-bold text-sm bg-gray-900 border border-gray-700 text-white p-2 rounded focus:outline-none focus:border-[#00f3ff] hidden"></select>
                </div>
                <div class="bg-[#111] border border-gray-800 rounded-xl overflow-hidden shadow-lg">
                    <div id="dynamic-episode-list" class="max-h-[400px] overflow-y-auto ep-list-scroll p-2 space-y-2">
                        <div class="text-center py-10 text-gray-500 animate-pulse text-sm font-bold">Loading Episodes...</div>
                    </div>
                </div>
            </div>

            <div class="bg-[#111] p-4 md:p-6 rounded-xl border border-gray-800 shadow-xl mb-10 w-full block">
                <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-6 flex items-center gap-2"><span class="w-1 h-5 bg-[#00f3ff] rounded"></span> Community Discussion</h3>
                <form id="comment-form" class="mb-8 relative">
                    <textarea id="comment-text" class="w-full bg-gray-900 border border-gray-700 rounded-lg p-4 text-white focus:outline-none focus:border-[#00f3ff] resize-none" rows="3" placeholder="What did you think of this episode?" required></textarea>
                    <button type="submit" class="absolute bottom-4 right-4 bg-[#00f3ff] text-black px-6 py-2 rounded-lg font-bold hover:bg-cyan-400 transition shadow-[0_0_10px_rgba(0,243,255,0.4)]">Post</button>
                </form>
                <div id="comments-list" class="space-y-4 max-h-[500px] overflow-y-auto pr-2 ep-list-scroll">
                    <div class="text-center text-gray-500 text-sm font-bold">Loading comments...</div>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2"><span class="w-1 h-5 bg-[#00f3ff] rounded"></span> You May Also Like</h3>
                <div id="recommendations-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4"></div>
            </div>
        </div>
        <?php include '../components/footer.php'; ?>
    </main>

    <script>
        const ANILIST_API = 'https://graphql.anilist.co';
        const animeId = "<?= $id ?>";
        let currentEp = parseInt("<?= $ep ?>") || 1;
        let fullTitle = ""; let totalEpisodesAvailable = 1;
        let currentServer = "megaplay"; let currentLang = "sub";        
        let isTheatreMode = false; let globalMediaData = null; 
        const EPISODE_CHUNK_SIZE = 50; 

        document.addEventListener('DOMContentLoaded', () => { fetchAnimeData(); loadComments(); });
        function toggleSidebar() { document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar-overlay').classList.toggle('hidden'); }
        function toggleTheatreMode() { isTheatreMode = !isTheatreMode; document.body.classList.toggle('theatre-active'); if(isTheatreMode) window.scrollTo({ top: 0, behavior: 'smooth' }); }

        // --- BULLETPROOF COMMENTS FIX (Passing via URL & Body to avoid host drop) ---
        document.getElementById('comment-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const text = document.getElementById('comment-text').value.trim();
            if(!text) return;
            
            const fd = new FormData();
            fd.append('anime_id', animeId);
            fd.append('episode', currentEp);
            fd.append('comment_text', text);
            
            try {
                // Attach the parameters directly to the URL as a backup mechanism
                const safeText = encodeURIComponent(text);
                const res = await fetch(`post_comment.php?anime_id=${animeId}&episode=${currentEp}&comment_text=${safeText}`, { 
                    method: 'POST', 
                    body: fd
                });
                const data = await res.json();
                if(data.status === 'success') { document.getElementById('comment-text').value = ''; loadComments(); } 
                else { alert(data.message || 'Error posting comment.'); }
            } catch(err) { alert('Error connecting to server.'); }
        });

        async function loadComments() {
            const list = document.getElementById('comments-list');
            list.innerHTML = '<div class="text-center text-gray-500 text-sm font-bold animate-pulse">Loading comments...</div>';
            try {
                const res = await fetch(`get_comments.php?anime_id=${animeId}&episode=${currentEp}`);
                const comments = await res.json();
                if (comments.length === 0) { list.innerHTML = '<div class="text-center text-gray-600 text-sm italic py-4">Be the first to comment!</div>'; return; }
                list.innerHTML = comments.map(c => `<div class="bg-gray-900 p-4 rounded-lg border border-gray-800"><div class="flex justify-between items-center mb-2"><span class="font-bold text-[#00f3ff] text-sm">${c.username}</span><span class="text-xs text-gray-500">${new Date(c.created_at).toLocaleDateString()}</span></div><p class="text-gray-300 text-sm whitespace-pre-wrap">${c.comment_text}</p></div>`).join('');
            } catch(err) { list.innerHTML = '<div class="text-red-500 text-sm">Failed to load comments.</div>'; }
        }

        async function fetchAnimeData() {
            const query = `query($id: Int) { Media(id: $id, type: ANIME) { title { romaji english userPreferred } description episodes status averageScore format coverImage { extraLarge large } bannerImage nextAiringEpisode { episode } streamingEpisodes { title thumbnail } recommendations(sort: RATING_DESC, perPage: 10) { nodes { mediaRecommendation { id title{romaji english} coverImage{large} } } } } }`;
            try {
                const res = await fetch(ANILIST_API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ query, variables: { id: parseInt(animeId) } }) });
                const data = await res.json();
                const media = data.data.Media; globalMediaData = media;
                fullTitle = media.title.english || media.title.romaji || media.title.userPreferred;
                document.getElementById('media-title').innerText = fullTitle; document.getElementById('media-desc').innerHTML = media.description ? media.description.replace(/<[^>]*>?/gm, '') : 'No description available.';
                document.getElementById('media-status').innerText = `STATUS: ${media.status}`; document.getElementById('media-rating').innerText = media.averageScore ? `★ ${(media.averageScore/10).toFixed(1)}` : 'N/A';
                totalEpisodesAvailable = media.episodes || (media.nextAiringEpisode ? media.nextAiringEpisode.episode - 1 : 24);
                updateNavButtons();
                const rangeSelector = document.getElementById('ep-range-selector');
                if (totalEpisodesAvailable > EPISODE_CHUNK_SIZE) {
                    let optionsHtml = ''; const totalChunks = Math.ceil(totalEpisodesAvailable / EPISODE_CHUNK_SIZE);
                    for (let i = 0; i < totalChunks; i++) {
                        const start = (i * EPISODE_CHUNK_SIZE) + 1; const end = Math.min((i + 1) * EPISODE_CHUNK_SIZE, totalEpisodesAvailable);
                        optionsHtml += `<option value="${i}" ${(currentEp >= start && currentEp <= end) ? 'selected' : ''}>Episodes ${start} - ${end}</option>`;
                    }
                    rangeSelector.innerHTML = optionsHtml; rangeSelector.classList.remove('hidden');
                } else { rangeSelector.classList.add('hidden'); }
                renderVisualEpisodes();
                let recHtml = '';
                if(media.recommendations && media.recommendations.nodes) {
                    media.recommendations.nodes.forEach(node => {
                        const rec = node.mediaRecommendation; if(!rec) return;
                        recHtml += `<div class="cursor-pointer rounded-lg overflow-hidden poster-hover relative bg-gray-900 shadow-lg" onclick="window.location.href='watch.php?id=${rec.id}&ep=1'"><div class="aspect-[2/3]"><img src="${rec.coverImage.large}" class="w-full h-full object-cover"></div><div class="p-2 absolute bottom-0 w-full bg-gradient-to-t from-black to-transparent pt-10 text-xs font-bold text-white truncate drop-shadow-md">${rec.title.english || rec.title.romaji}</div></div>`;
                    });
                }
                document.getElementById('recommendations-grid').innerHTML = recHtml;
                loadVideo(); saveHistory();
            } catch (error) { document.getElementById('media-title').innerText = "Error Loading Data"; document.getElementById('dynamic-episode-list').innerHTML = '<div class="text-red-500 font-bold text-center py-10">Failed to load episodes.</div>'; }
        }

        function changeEpRange() { renderVisualEpisodes(); }

        function renderVisualEpisodes() {
            if (!globalMediaData) return;
            const listContainer = document.getElementById('dynamic-episode-list'); listContainer.innerHTML = ''; 
            const fallbackBanner = globalMediaData.bannerImage || (globalMediaData.coverImage && globalMediaData.coverImage.extraLarge) || 'https://via.placeholder.com/300x170/111/00f3ff?text=No+Image';
            const rangeSelector = document.getElementById('ep-range-selector');
            const chunkIndex = !rangeSelector.classList.contains('hidden') ? parseInt(rangeSelector.value) : 0;
            const startEp = (chunkIndex * EPISODE_CHUNK_SIZE) + 1; const endEp = Math.min((chunkIndex + 1) * EPISODE_CHUNK_SIZE, totalEpisodesAvailable);
            let html = '';
            for(let i = startEp; i <= endEp; i++) {
                let epTitle = `Episode ${i}`; let epThumb = fallbackBanner; let epDesc = `Watch episode ${i} of ${fullTitle}.`;
                if (globalMediaData.streamingEpisodes && globalMediaData.streamingEpisodes.length >= i) {
                    const epData = globalMediaData.streamingEpisodes[i-1];
                    if (epData && epData.title) epTitle = epData.title.replace(/Episode \d+ - /i, ''); 
                    if (epData && epData.thumbnail) epThumb = epData.thumbnail;
                }
                const isActive = (currentEp === i);
                const activeClasses = isActive ? 'border-[#00f3ff] bg-gray-800 shadow-[0_0_15px_rgba(0,243,255,0.15)]' : 'border-transparent bg-gray-900/50 hover:bg-gray-800 hover:border-[#00f3ff]/50';
                const playingBadge = isActive ? `<div class="absolute top-2 left-2 bg-[#00f3ff] text-black text-[9px] font-black px-1.5 py-0.5 rounded shadow-md uppercase tracking-widest z-10 animate-pulse flex items-center gap-1"><span class="w-1.5 h-1.5 bg-black rounded-full block"></span> Playing</div>` : '';
                html += `<div id="ep-card-${i}" onclick="playEp(${i})" class="flex items-center gap-3 md:gap-5 p-2 md:p-3 rounded-xl transition duration-300 cursor-pointer border group ${activeClasses}"><div class="relative w-28 md:w-44 aspect-video rounded-lg overflow-hidden shrink-0 shadow-md bg-black"><img src="${epThumb}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">${playingBadge}<div class="absolute inset-0 bg-black/40 group-hover:bg-transparent transition-colors"></div><div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity"><div class="bg-[#00f3ff]/90 rounded-full p-2 shadow-[0_0_15px_rgba(0,243,255,0.8)] backdrop-blur-sm"><svg class="w-5 h-5 md:w-6 md:h-6 text-black" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg></div></div><div class="absolute bottom-1 right-1 bg-black/90 backdrop-blur-md px-1.5 py-0.5 text-[10px] font-bold text-white rounded border border-gray-700">EP ${i}</div></div><div class="flex-1 min-w-0 py-1"><h4 class="text-sm md:text-base font-bold text-gray-100 truncate group-hover:text-[#00f3ff] transition-colors ${isActive ? 'text-[#00f3ff]' : ''}">${epTitle}</h4><p class="text-xs text-gray-500 mt-1 line-clamp-2 hidden md:-webkit-box md:max-w-2xl">${epDesc}</p></div></div>`;
            }
            listContainer.innerHTML = html; setTimeout(() => { const activeCard = document.getElementById(`ep-card-${currentEp}`); if (activeCard) activeCard.scrollIntoView({ behavior: 'smooth', block: 'center' }); }, 300);
        }

        function updateNavButtons() { document.getElementById('prev-btn').disabled = (currentEp <= 1); document.getElementById('next-btn').disabled = (currentEp >= totalEpisodesAvailable); }
        function navigateEp(direction) { let newEp = currentEp + direction; if (newEp >= 1 && newEp <= totalEpisodesAvailable) playEp(newEp); }

        function changeServer(serverName) {
            currentServer = serverName;
            ['megaplay', 'vidnest', 'animepahe', 'vidplays', 'filmu', '4animo'].forEach(s => { const btn = document.getElementById(`btn-${s}`); if(btn) btn.className = (s === serverName) ? 'px-5 py-2 rounded transition text-xs server-active' : 'px-5 py-2 rounded transition text-xs server-inactive'; });
            loadVideo();
        }

        function changeLang(lang) {
            currentLang = lang;
            ['sub', 'dub'].forEach(l => { const btn = document.getElementById(`btn-${l}`); if (btn) btn.className = (l === lang) ? 'px-6 py-2 rounded transition text-xs server-active tracking-wider' : 'px-6 py-2 rounded transition text-xs server-inactive tracking-wider'; });
            loadVideo();
        }

        function loadVideo() {
            const iframe = document.getElementById('video-frame'); const loader = document.getElementById('video-loader');
            iframe.classList.add('hidden'); loader.classList.remove('hidden');
            let embedUrl = "";
            switch(currentServer) {
                case 'megaplay': embedUrl = `https://megaplay.buzz/stream/ani/${animeId}/${currentEp}/${currentLang}`; break;
                case 'vidnest': embedUrl = `https://vidnest.fun/anime/${animeId}/${currentEp}/${currentLang}`; break;
                case 'animepahe': embedUrl = `https://vidnest.fun/animepahe/${animeId}/${currentEp}/${currentLang}`; break;
                case 'vidplays': embedUrl = `https://vidplays.fun/embed/anime/${animeId}/${currentEp}/${currentLang}`; break;
                case 'filmu': embedUrl = `https://embed.filmu.in/anime/${animeId}/1/${currentEp}`; break;
                case '4animo': embedUrl = `https://cdn.4animo.xyz/embed/hd-1/ani/${animeId}/${currentEp}/${currentLang}?k=1`; break;
            }
            iframe.src = embedUrl; iframe.onload = () => { loader.classList.add('hidden'); iframe.classList.remove('hidden'); };
        }

        function playEp(ep) {
            currentEp = parseInt(ep); const newUrl = window.location.pathname + `?id=${animeId}&ep=${currentEp}`; window.history.pushState({path:newUrl},'',newUrl);
            updateNavButtons();
            const rangeSelector = document.getElementById('ep-range-selector');
            if (!rangeSelector.classList.contains('hidden')) {
                const chunkIndex = Math.floor((currentEp - 1) / EPISODE_CHUNK_SIZE);
                if (parseInt(rangeSelector.value) !== chunkIndex) { rangeSelector.value = chunkIndex; renderVisualEpisodes(); } else { renderVisualEpisodes(); }
            } else { renderVisualEpisodes(); }
            loadVideo(); saveHistory(); loadComments();
            document.querySelector('.video-container').scrollIntoView({ behavior: 'smooth', block: 'end' });
        }

        // --- BULLETPROOF HISTORY FIX ---
        function saveHistory() {
            const fd = new FormData();
            fd.append('anime_id', animeId);
            fd.append('episode', currentEp);
            fetch(`save_history.php?anime_id=${animeId}&episode=${currentEp}`, { method: 'POST', body: fd });
        }

        // --- BULLETPROOF WATCHLIST FIX ---
        async function addToWatchlist() {
            const btn = document.getElementById('watchlist-btn'); btn.innerText = 'Wait...'; 
            try {
                const fd = new FormData();
                fd.append('media_id', animeId);
                fd.append('type', 'anime');
                fd.append('title', fullTitle || "Unknown Anime");

                const res = await fetch(`add_to_watchlist.php?media_id=${animeId}&type=anime&title=${encodeURIComponent(fullTitle || "Unknown Anime")}`, { method: 'POST', body: fd });
                const data = await res.json();
                if(data.status === 'added') { btn.className = 'bg-[#00f3ff] text-black px-6 py-2.5 rounded-lg font-bold shadow-[0_0_15px_rgba(0,243,255,0.4)] whitespace-nowrap transition'; btn.innerText = '✓ Added to List'; } 
                else if(data.status === 'removed') { btn.className = 'bg-gray-800 hover:bg-gray-700 text-white px-6 py-2.5 rounded-lg font-bold border border-gray-600 hover:border-[#00f3ff] transition shadow-md whitespace-nowrap'; btn.innerText = '+ My List'; } 
                else { alert(data.message); btn.innerText = '+ My List'; }
            } catch(e) { alert("Error connecting to database."); btn.innerText = '+ My List'; }
        }
    </script>
</body>
</html>