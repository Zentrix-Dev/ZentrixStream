<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZENTRIX | Ultimate Streaming</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #050505; 
            overflow-x: hidden;
            margin: 0;
        }
        
        /* Custom Glowing Text */
        .glow-text-cyan { text-shadow: 0 0 15px rgba(0, 243, 255, 0.5); }
        .glow-text-red { text-shadow: 0 0 15px rgba(255, 0, 60, 0.5); }
        
        /* Ambient Background Glows */
        .bg-glow-cyan {
            position: fixed; top: 10%; left: -10%; width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(0,243,255,0.08) 0%, rgba(0,0,0,0) 70%);
            z-index: -1; pointer-events: none;
        }
        .bg-glow-red {
            position: fixed; bottom: 10%; right: -10%; width: 50vw; height: 50vw;
            background: radial-gradient(circle, rgba(255,0,60,0.08) 0%, rgba(0,0,0,0) 70%);
            z-index: -1; pointer-events: none;
        }

        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .animate-float { animation: float 6s ease-in-out infinite; }
        .animate-float-delayed { animation: float 6s ease-in-out 3s infinite; }

        .fade-in-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Loader Styles */
        #loader {
            position: fixed; inset: 0; z-index: 9999;
            background-color: #050505;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.6s ease-out, visibility 0.6s ease-out;
        }
        .spinner {
            position: relative; width: 80px; height: 80px;
        }
        .ring-cyan {
            position: absolute; inset: 0;
            border: 4px solid transparent; border-top-color: #00f3ff; border-left-color: #00f3ff;
            border-radius: 50%; animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .ring-red {
            position: absolute; inset: 8px;
            border: 4px solid transparent; border-bottom-color: #ff003c; border-right-color: #ff003c;
            border-radius: 50%; animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite reverse;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="text-white selection:bg-[#ff003c] selection:text-white">

    <div id="loader">
        <div class="spinner mb-6">
            <div class="ring-cyan"></div>
            <div class="ring-red"></div>
        </div>
        <div class="font-black tracking-[0.4em] uppercase text-sm text-white animate-pulse">
            Loading <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00f3ff] to-[#ff003c]">Universe</span>
        </div>
    </div>

    <div class="bg-glow-cyan"></div>
    <div class="bg-glow-red"></div>

    <div id="main-content" class="fade-in-up">
        <main class="relative z-10 flex flex-col items-center justify-center min-h-[85vh] px-4 text-center">
            
            <div class="mb-14">
                <h1 class="text-5xl md:text-8xl font-black tracking-tighter uppercase mb-4 drop-shadow-2xl">
                    <span class="text-white">ZENTRIX</span> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00f3ff] to-[#ff003c]">STREAM</span>
                </h1>
                <p class="text-gray-400 text-sm md:text-lg font-semibold tracking-widest uppercase max-w-2xl mx-auto">
                    The Ultimate Dual-Portal Streaming Experience
                </p>
            </div>

            <div class="flex flex-col md:flex-row gap-6 w-full max-w-4xl px-4">
                
                <a href="anime/index.php" class="animate-float group relative flex-1 bg-white/[0.03] backdrop-blur-md border border-white/10 p-10 rounded-3xl hover:border-[#00f3ff]/50 transition-all duration-500 overflow-hidden hover:shadow-[0_0_40px_rgba(0,243,255,0.15)]">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#00f3ff]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-20 h-20 bg-[#00f3ff]/10 rounded-full flex items-center justify-center mb-6 border border-[#00f3ff]/20 group-hover:scale-110 transition-transform duration-500 group-hover:border-[#00f3ff] group-hover:shadow-[0_0_20px_rgba(0,243,255,0.4)]">
                            <svg class="w-10 h-10 text-[#00f3ff]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-black text-white tracking-widest uppercase glow-text-cyan group-hover:text-[#00f3ff] transition-colors">Anime</h2>
                        <p class="text-xs text-gray-400 mt-3 font-bold tracking-widest uppercase group-hover:text-gray-200 transition-colors">AniList Server</p>
                    </div>
                </a>

                <a href="movie/index.php" class="animate-float-delayed group relative flex-1 bg-white/[0.03] backdrop-blur-md border border-white/10 p-10 rounded-3xl hover:border-[#ff003c]/50 transition-all duration-500 overflow-hidden hover:shadow-[0_0_40px_rgba(255,0,60,0.15)]">
                    <div class="absolute inset-0 bg-gradient-to-bl from-[#ff003c]/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="relative z-10 flex flex-col items-center">
                        <div class="w-20 h-20 bg-[#ff003c]/10 rounded-full flex items-center justify-center mb-6 border border-[#ff003c]/20 group-hover:scale-110 transition-transform duration-500 group-hover:border-[#ff003c] group-hover:shadow-[0_0_20px_rgba(255,0,60,0.4)]">
                            <svg class="w-10 h-10 text-[#ff003c]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                            </svg>
                        </div>
                        <h2 class="text-3xl font-black text-white tracking-widest uppercase glow-text-red group-hover:text-[#ff003c] transition-colors">Movies & TV</h2>
                        <p class="text-xs text-gray-400 mt-3 font-bold tracking-widest uppercase group-hover:text-gray-200 transition-colors">TMDB Server</p>
                    </div>
                </a>

            </div>
        </main>

        <section class="relative z-10 bg-[#0a0a0a]/80 backdrop-blur-xl border-t border-white/5 py-24 px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h3 class="text-3xl font-black tracking-widest uppercase mb-4 text-white">How It <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#00f3ff] to-[#ff003c]">Works</span></h3>
                    <div class="w-24 h-1.5 bg-gradient-to-r from-[#00f3ff] to-[#ff003c] mx-auto rounded-full"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="group bg-[#111] p-8 rounded-2xl border border-white/5 hover:border-[#00f3ff]/30 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-black border border-gray-800 rounded-full flex items-center justify-center mb-6 text-xl font-black text-gray-300 group-hover:text-[#00f3ff] group-hover:border-[#00f3ff] transition-colors">1</div>
                        <h4 class="text-lg font-bold text-white uppercase tracking-wider mb-3">Choose Your Portal</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Zentrix is divided into two dedicated servers. Select the <strong class="text-[#00f3ff]">Anime</strong> portal for the latest releases, or the <strong class="text-[#ff003c]">Movie</strong> portal for global cinema and TV.
                        </p>
                    </div>

                    <div class="group bg-[#111] p-8 rounded-2xl border border-white/5 hover:border-white/20 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-black border border-gray-800 rounded-full flex items-center justify-center mb-6 text-xl font-black text-gray-300 group-hover:text-white group-hover:border-white transition-colors">2</div>
                        <h4 class="text-lg font-bold text-white uppercase tracking-wider mb-3">Seamless Streaming</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Powered by high-speed proxy servers and top-tier APIs, ensuring you get metadata, posters, and uninterrupted video playback directly to your device.
                        </p>
                    </div>

                    <div class="group bg-[#111] p-8 rounded-2xl border border-white/5 hover:border-[#ff003c]/30 transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-black border border-gray-800 rounded-full flex items-center justify-center mb-6 text-xl font-black text-gray-300 group-hover:text-[#ff003c] group-hover:border-[#ff003c] transition-colors">3</div>
                        <h4 class="text-lg font-bold text-white uppercase tracking-wider mb-3">Cloud Sync History</h4>
                        <p class="text-sm text-gray-400 leading-relaxed">
                            Create an account to automatically save your progress. Your "Continue Watching" history is stored in our secure database, syncing across all your devices.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        
        <footer class="text-center py-8 border-t border-white/5 bg-[#050505]">
            <p class="text-xs font-bold tracking-widest text-gray-600 uppercase">© <?= date('Y') ?> Zentrix Stream. All Rights Reserved.</p>
        </footer>
    </div>

    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('loader');
            const mainContent = document.getElementById('main-content');
            
            // Add a tiny artificial delay so the user sees the cool loader
            setTimeout(() => {
                loader.style.opacity = '0';
                loader.style.visibility = 'hidden';
                mainContent.classList.add('visible');
            }, 600);
        });
    </script>
</body>
</html>
