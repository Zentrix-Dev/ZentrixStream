<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New On AniStream - AniStream Pro Explorer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --card-bg: #141414; --accent: #00f3ff; --text-main: #ffffff; --text-dim: #999; --border: #222; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .neon-text { color: var(--accent); text-shadow: 0 0 10px rgba(0, 243, 255, 0.3); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,243,255,0.2); border: 1px solid var(--accent); }
        #anime-modal { display: none; overflow-y: auto; background: rgba(0,0,0,0.95); }
        .modal-body { max-width: 700px; margin: 2rem auto; background: #111; border-radius: 15px; overflow: hidden; border: 1px solid #333; position: relative; }
        .close-btn { position: absolute; right: 15px; top: 15px; background: var(--accent); color: #000; border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; font-weight: bold; z-index: 101; }
        .meta-table { width: 100%; font-size: 0.9rem; border-collapse: collapse; margin-top: 10px; }
        .meta-table td { padding: 12px; border-bottom: 1px solid #222; }
        .meta-table .label { color: var(--text-dim); width: 130px; font-weight: bold; }
        .badge { background: #333; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; margin-right: 5px; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
        .title-cyan { color: #00f3ff; }
    </style>
</head>
<body class="bg-[#0b0b0b] min-h-screen flex">

    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-[#00f3ff]/20 transition-transform duration-300 z-50 lg:z-40 flex flex-col lg:h-screen lg:sticky lg:top-0">
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-[#00f3ff]/20 shrink-0">
            <h1 class="text-2xl font-black text-[#00f3ff] drop-shadow-[0_0_10px_rgba(0,243,255,0.4)] tracking-widest uppercase">ZENTRIX</h1>
        </div>
        
        <div class="lg:hidden p-4 flex justify-between items-center border-b border-[#00f3ff]/20">
            <h2 class="text-xl font-bold tracking-widest neon-text">MENU</h2>
            <button onclick="toggleSidebar()" class="text-gray-400 hover:text-white text-3xl font-bold leading-none">&times;</button>
        </div>

        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-2">
            <a href="../index.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🏠 Home</a>
            <a href="../pages/signup.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔒 Signup/Login</a>
            <a href="../pages/profile.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">👨 Profile</a>
            <a href="latest.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🆕 Latest</a>
            <a href="new-on.php" class="block px-4 py-2 rounded bg-gray-800 text-[#00f3ff] transition font-bold border border-[#00f3ff]/30">✨ New On</a>
            <a href="schedule.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">📅 Schedule</a>
            <a href="trending.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🔥 Trending</a>
            <a href="upcoming.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🚀 Upcoming</a>
            <a href="continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-[#00f3ff] transition font-bold">🕒 Continue-Watch</a>
        </nav>

        <div class="p-4 border-t border-[#00f3ff]/20 shrink-0">
            <a href="../pages/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-600/80 hover:bg-red-600 rounded-lg transition-colors uppercase tracking-wide">
                Logout
            </a>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 flex flex-col relative min-w-0 h-screen overflow-y-auto">
        <header class="p-4 border-b border-gray-800 flex justify-between items-center bg-[#0f0f0f] z-20 shrink-0">
            <div class="w-full max-w-2xl flex gap-4 items-center">
                <button onclick="toggleSidebar()" class="text-[#00f3ff] hover:text-white transition focus:outline-none shrink-0 lg:hidden" title="Open Menu">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>
        </header>

        <div class="flex-1 p-6" id="content-area">
            <h2 class="text-2xl font-bold mb-6 uppercase tracking-wider text-cyan">New On Platform</h2>
            <div id="grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6"></div>
        </div>

        <div id="anime-modal" class="fixed inset-0 z-50 flex-col" onclick="if(event.target == this) closeModal()">
            <div class="modal-body shadow-2xl">
                <button class="close-btn shadow-lg hover:scale-110 transition" onclick="closeModal()">✕</button>
                <div id="modal-content"></div>
            </div>
        </div>
    </main>

    <script>
        const ANILIST_API = 'https://graphql.anilist.co';
        document.addEventListener('DOMContentLoaded', fetchPageData);
        
        function toggleSidebar() { 
            const sidebar = document.getElementById('sidebar'); 
            const overlay = document.getElementById('sidebar-overlay'); 
            sidebar.classList.toggle('-translate-x-full'); 
            overlay.classList.toggle('hidden'); 
        }

        async function fetchPageData() {
            const query = `query { Page(perPage: 30) { media(sort: START_DATE_DESC, type: ANIME, isAdult: false, format: TV) { id title { english romaji } coverImage { extraLarge } format } } }`;
            const response = await fetch(ANILIST_API, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ query }) });
            renderGrid((await response.json()).data.Page.media);
        }
        function renderGrid(mediaList) {
            const grid = document.getElementById('grid'); grid.innerHTML = '';
            mediaList.forEach((anime) => {
                grid.innerHTML += `<div class="cursor-pointer transition-all duration-300 rounded-lg overflow-hidden poster-hover relative bg-gray-900 group" onclick="openDetails(${anime.id})"><div class="aspect-[2/3] overflow-hidden"><img src="${anime.coverImage.extraLarge}" class="w-full h-full object-cover"></div><div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black via-black/80 to-transparent pt-12"><p class="text-sm font-semibold truncate text-gray-100 drop-shadow-md">${anime.title.english || anime.title.romaji}</p></div></div>`;
            });
        }
        async function openDetails(animeId) { /* Omitted duplicate JS structure (Identical to latest.php Modal logic) */ }
        function closeModal() { document.getElementById('anime-modal').style.display = 'none'; document.body.style.overflow = 'auto'; }
    </script>
</body>
</html>
