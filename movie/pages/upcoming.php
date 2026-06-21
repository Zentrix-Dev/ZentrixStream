<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming - Zentrix Stream</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --bg-color: #0b0b0b; --accent: #ff0000; --text-main: #ffffff; }
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; }
        .red-text { color: var(--accent); text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
        .poster-hover:hover { transform: scale(1.05); box-shadow: 0 10px 20px rgba(255,0,0,0.3); border: 1px solid var(--accent); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 10px; }
    </style>
</head>
<body class="bg-[#0b0b0b] text-white min-h-screen flex">

    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 lg:static lg:w-64 bg-[#111] border-r border-gray-800 transition-transform duration-300 z-50 lg:z-40 flex flex-col lg:h-screen lg:sticky lg:top-0">
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-gray-800 shrink-0">
            <h1 class="text-2xl font-black text-red-600 drop-shadow-[0_0_10px_rgba(255,0,0,0.4)] tracking-widest uppercase">ZENTRIX</h1>
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
            <a href="upcoming.php" class="block px-4 py-2 rounded bg-red-600/10 text-red-500 transition font-bold border border-red-600/30 border-l-4">🚀 Upcoming</a>
            <a href="continue-watching.php" class="block px-4 py-2 rounded hover:bg-gray-800 text-gray-400 hover:text-red-500 transition font-bold">🕒 Continue-Watch</a>
        </nav>
        <div class="p-4 border-t border-gray-800 shrink-0">
            <a href="../components/logout.php" class="flex items-center justify-center w-full px-4 py-2 text-sm font-bold text-white bg-red-600/80 hover:bg-red-600 rounded-lg transition-colors uppercase tracking-wide">Logout</a>
        </div>
    </aside>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <main class="flex-1 flex flex-col relative min-w-0 h-screen overflow-y-auto">
        <header class="p-4 lg:p-6 border-b border-gray-800 flex items-center bg-[#0f0f0f] z-20 shrink-0">
            <button onclick="toggleSidebar()" class="text-red-600 hover:text-white transition focus:outline-none mr-4 lg:hidden">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
            <h1 class="text-xl font-bold red-text uppercase flex items-center gap-2">
                <span class="w-1 h-6 bg-red-600 rounded hidden lg:block"></span> Upcoming Movies
            </h1>
        </header>

        <div class="flex-1 p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" id="grid"></div>
        </div>

        <?php include '../components/footer.php'; ?>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.toggle('hidden');
        }

        async function loadContent() {
            try {
                const res = await fetch(`../api.php?route=movie/upcoming`);
                const data = await res.json();
                
                if(data.error) return;

                const grid = document.getElementById('grid');
                grid.innerHTML = data.results.map(item => `
                    <div class="cursor-pointer transition-all duration-300 rounded-lg overflow-hidden poster-hover relative bg-gray-900 shadow-lg" onclick="window.location.href='watch.php?id=${item.id}&type=movie'">
                        <div class="aspect-[2/3]">
                            <img src="${item.poster_path ? 'https://image.tmdb.org/t/p/w500'+item.poster_path : 'https://via.placeholder.com/500x750/111/ff0000?text=No+Poster'}" class="w-full h-full object-cover" alt="${item.title}" loading="lazy">
                        </div>
                        <div class="p-3 bg-gradient-to-t from-black via-black/80 to-transparent absolute bottom-0 w-full pt-12">
                            <p class="text-xs font-bold truncate drop-shadow-md">${item.title}</p>
                            <p class="text-[9px] text-white bg-red-600 px-1.5 py-0.5 rounded mt-1 inline-block uppercase font-bold tracking-wider">Release: ${item.release_date || 'TBA'}</p>
                        </div>
                    </div>
                `).join('');
            } catch (e) {
                console.error("Failed to load content", e);
            }
        }
        loadContent();
    </script>
</body>
</html>