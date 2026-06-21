<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../components/signup.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$room_code = $_GET['room'] ?? null;

if (!$room_code) die("<h1 style='color:red; text-align:center; margin-top:50px;'>Error: No Room Code provided.</h1>");

$room_data = $conn->query("SELECT * FROM watch_rooms WHERE room_code = '" . $conn->real_escape_string($room_code) . "'")->fetch_assoc();
if (!$room_data) die("<h1 style='color:red; text-align:center; margin-top:50px;'>Error: Room does not exist or has expired.</h1>");

$is_host = ($room_data['host_id'] == $user_id);
$tmdbId = $room_data['media_id'];
$mediaType = $room_data['media_type'];
$season = $room_data['season'];
$episode = $room_data['episode'];

$env = @parse_ini_file(__DIR__ . '/../.env') ?: [];
$tmdbKey = $env['TMDB_API_KEY'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Party - ZENTRIX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0b0b0b; color: #fff; font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; }
        .red-text { color: #ff0000; text-shadow: 0 0 10px rgba(255, 0, 0, 0.4); }
        .video-container { position: relative; width: 100%; height: 100%; background: #000; overflow: hidden; }
        iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; outline: none; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #ff0000; border-radius: 10px; }
        
        #curtain-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.96); backdrop-blur-md; z-index: 50; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: opacity 0.5s ease; }
        .server-active { background-color: #ff0000; color: #fff; font-weight: bold; box-shadow: 0 0 15px rgba(255,0,0,0.4); border: 1px solid #ff0000; }
        .server-inactive { background-color: #1a1a1a; color: #9ca3af; border: 1px solid #333; }
        
        .layout-wrapper { display: flex; flex-direction: column; height: 100vh; }
        @media (min-width: 1024px) { .layout-wrapper { flex-direction: row; } }
        .player-section { flex: 1; position: relative; border-right: 1px solid #222; }
        .sidebar-section { width: 100%; height: 45vh; display: flex; flex-direction: column; background: #111; z-index: 20; border-top: 1px solid #333; }
        @media (min-width: 1024px) { .sidebar-section { width: 360px; height: 100vh; border-top: none; } }
    </style>
</head>
<body class="layout-wrapper">

    <div class="player-section flex flex-col">
        <header class="h-16 bg-[#0f0f0f] border-b border-gray-800 flex justify-between items-center px-4 shrink-0">
            <div class="flex items-center gap-3">
                <a href="../index.php" class="text-gray-400 hover:text-red-500 font-bold text-sm transition">❮ EXIT</a>
                <span class="w-px h-6 bg-gray-700 block"></span>
                <h1 class="text-sm md:text-base font-black red-text uppercase tracking-widest" id="party-title">Loading Title...</h1>
            </div>
            <div class="flex gap-2">
                <button onclick="copyInvite()" class="bg-gray-800 hover:bg-gray-700 border border-gray-600 px-3 py-1.5 rounded text-xs font-bold transition">🔗 Copy Link</button>
            </div>
        </header>

        <div class="bg-gray-900 border-b border-gray-800 px-4 py-3 flex flex-wrap gap-4 justify-between items-center text-xs">
            <div class="flex items-center gap-2">
                <span class="text-gray-500 font-bold uppercase tracking-wider">Source Engine:</span>
                <div class="flex gap-1" id="server-controllers">
                    <button onclick="hostChangeConfig('peachify')" id="p-btn-peachify" class="px-3 py-1 rounded text-[11px] server-inactive">PEACHIFY</button>
                    <button onclick="hostChangeConfig('vidzen')" id="p-btn-vidzen" class="px-3 py-1 rounded text-[11px] server-inactive">VIDZEN</button>
                    <button onclick="hostChangeConfig('vidnest')" id="p-btn-vidnest" class="px-3 py-1 rounded text-[11px] server-inactive">VIDNEST</button>
                    <button onclick="hostChangeConfig('vixsrc')" id="p-btn-vixsrc" class="px-3 py-1 rounded text-[11px] server-inactive">VIXSRC</button>
                </div>
            </div>
            
            <?php if($mediaType === 'tv'): ?>
            <div class="flex items-center gap-2" id="host-tv-panel">
                <button onclick="hostStepEpisode(-1)" class="bg-gray-800 border border-gray-700 hover:border-red-500 px-3 py-1 rounded font-bold">❮ EP</button>
                <span class="font-bold bg-black px-3 py-1 rounded border border-gray-800 text-red-500" id="host-ep-display">EP <?= $episode ?></span>
                <button onclick="hostStepEpisode(1)" class="bg-gray-800 border border-gray-700 hover:border-red-500 px-3 py-1 rounded font-bold">EP ❯</button>
            </div>
            <?php endif; ?>
        </div>

        <div class="flex-1 relative video-container">
            <iframe id="video-frame" allowfullscreen allow="autoplay; fullscreen; encrypted-media; picture-in-picture"></iframe>
            
            <div id="curtain-overlay">
                <div class="animate-pulse mb-4">
                    <svg class="w-14 h-14 text-red-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                </div>
                <h2 class="text-xl font-black text-white uppercase tracking-widest mb-1">Watch Party Session</h2>
                <p class="text-gray-400 text-xs mb-6" id="curtain-status">Connecting to room...</p>
                
                <?php if($is_host): ?>
                    <button onclick="hostStartMovie()" class="bg-red-600 hover:bg-red-500 text-white px-8 py-3 rounded-lg font-black uppercase tracking-widest transition shadow-[0_0_15px_rgba(255,0,0,0.5)] border border-red-500 text-sm">
                        START MOVIE NOW
                    </button>
                    <p class="text-[10px] text-gray-500 mt-3 font-bold uppercase tracking-wider">Clicking this will instantly unhide the video for everyone.</p>
                <?php else: ?>
                    <button onclick="toggleReady()" id="ready-btn" class="bg-gray-800 hover:bg-gray-700 text-white px-8 py-3 rounded-lg font-black uppercase tracking-widest transition border border-gray-600 text-sm shadow-md">
                        I AM READY
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="p-3 border-b border-gray-800 bg-[#0f0f0f] shrink-0">
            <h3 class="text-[10px] uppercase font-bold text-gray-500 tracking-widest mb-2 flex justify-between items-center border-b border-gray-800 pb-2">
                <span>Users in Room (<span id="user-count">1</span>)</span>
                <span class="bg-red-900/30 text-red-500 border border-red-800/50 px-2 py-0.5 rounded font-mono text-[9px]"><?= $room_code ?></span>
            </h3>
            <div id="users-list" class="flex flex-col gap-1.5 max-h-32 overflow-y-auto pr-1"></div>
        </div>

        <div id="chat-box" class="flex-1 p-4 overflow-y-auto space-y-3 bg-[#111]">
            <div class="text-center text-[10px] text-red-500 bg-red-900/10 border border-red-900/30 rounded p-2 mb-2 font-medium">🛡️ Secure Channel: This feed is completely closed. Only participants with the room link can read messages.</div>
        </div>

        <form id="chat-form" class="p-3 border-t border-gray-800 bg-[#0f0f0f] flex gap-2 shrink-0">
            <input type="text" id="chat-input" class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-red-600" placeholder="Send secure message..." autocomplete="off">
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-700 transition shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
            </button>
        </form>
    </div>

    <script>
        const roomCode = "<?= $room_code ?>";
        const currentUserId = <?= $user_id ?>;
        let isHost = <?= $is_host ? 'true' : 'false' ?>;
        
        const tmdbId = "<?= $tmdbId ?>";
        const mediaType = "<?= $mediaType ?>";
        
        let localActiveServer = 'peachify';
        let localActiveSeason = parseInt("<?= $season ?>") || 1;
        let localActiveEpisode = parseInt("<?= $episode ?>") || 1;
        
        let currentLoadedUrl = "";
        let lastChatLength = 0;

        document.addEventListener('DOMContentLoaded', () => {
            fetchMediaDetails();
            startSyncLoop(); 
        });

        async function fetchMediaDetails() {
            try {
                const res = await fetch(`../api.php?route=${mediaType}/${tmdbId}`);
                const data = await res.json();
                let title = data.title || data.name;
                document.getElementById('party-title').innerText = isHost ? `👑 HOSTING: ${title}` : title;
            } catch (e) {}
        }

        function renderFrameEngine(server, s, e) {
            const iframe = document.getElementById('video-frame');
            let builtUrl = "";
            
            if (mediaType === 'movie') {
                switch(server) {
                    case 'peachify': builtUrl = `https://peachify.top/embed/movie/${tmdbId}`; break;
                    case 'vidzen': builtUrl = `https://vidzen.fun/movie/${tmdbId}`; break;
                    case 'vidnest': builtUrl = `https://vidnest.fun/movie/${tmdbId}`; break;
                    case 'vixsrc': builtUrl = `https://vixsrc.to/movie/${tmdbId}`; break;
                }
            } else {
                switch(server) {
                    case 'peachify': builtUrl = `https://peachify.top/embed/tv/${tmdbId}/${s}/${e}`; break;
                    case 'vidzen': builtUrl = `https://vidzen.fun/tv/${tmdbId}/${s}/${e}`; break;
                    case 'vidnest': builtUrl = `https://vidnest.fun/tv/${tmdbId}/${s}/${e}`; break;
                    case 'vixsrc': builtUrl = `https://vixsrc.to/tv/${tmdbId}/${s}/${e}`; break;
                }
            }

            if(currentLoadedUrl !== builtUrl) {
                currentLoadedUrl = builtUrl;
                iframe.src = builtUrl;
            }

            ['peachify', 'vidzen', 'vidnest', 'vixsrc'].forEach(srv => {
                const b = document.getElementById(`p-btn-${srv}`);
                if(b) b.className = srv === server ? 'px-3 py-1 rounded text-[11px] server-active' : 'px-3 py-1 rounded text-[11px] server-inactive';
            });
            
            const displayBadge = document.getElementById('host-ep-display');
            if(displayBadge) displayBadge.innerText = `EP ${e}`;
        }

        function startSyncLoop() {
            setInterval(syncRoomKernel, 2000); 
            syncRoomKernel(); 
        }

        async function syncRoomKernel() {
            try {
                const res = await fetch(`party_action.php?action=sync&room_code=${roomCode}&_t=${Date.now()}`);
                const text = await res.text();
                
                // ULTIMATE JSON EXTRACTOR: Ignores InfinityFree's tracking code
                const match = text.match(/---ZENTRIX-START---(.*?)---ZENTRIX-END---/s);
                
                if (!match) {
                    // IF DATABASE FAILS OR FILE MISSING, PRINT IT HERE
                    document.getElementById('curtain-status').innerHTML = `<span class="text-red-500 font-bold">ERROR:</span><br><textarea class="w-full h-24 text-[10px] text-black p-1 mt-2">${text.substring(0, 200)}</textarea>`;
                    return;
                }

                const data = JSON.parse(match[1]);

                if (data.php_errors) {
                    console.log("Backend Note:", data.php_errors);
                }

                if (data.status === 'success') {
                    if (data.users) updateUsersListUI(data.users, data.host_id);
                    if (data.chat) updateChatInterface(data.chat);
                    
                    if (data.play_status !== 'waiting') {
                        localActiveServer = data.play_status;
                        localActiveSeason = data.current_season;
                        localActiveEpisode = data.current_episode;
                        
                        renderFrameEngine(localActiveServer, localActiveSeason, localActiveEpisode);

                        // Drop the curtain for everyone!
                        document.getElementById('curtain-overlay').style.display = 'none';
                    } else {
                        // Keep curtain up
                        document.getElementById('curtain-overlay').style.display = 'flex';
                        if (isHost) {
                            document.getElementById('curtain-status').innerText = "You are the Host. Wait for friends to join, then start the movie!";
                        } else {
                            const me = data.users.find(u => parseInt(u.id) === currentUserId);
                            const isMeReady = me ? (me.is_ready == 1) : false;
                            document.getElementById('curtain-status').innerText = isMeReady ? "Waiting for Host to start the movie..." : "Ready up! The video is locked until the host starts it.";
                        }
                    }
                } else {
                    document.getElementById('curtain-status').innerText = "Database Error: " + data.message;
                }
            } catch(e) {
                // Ignore temporary network drops to prevent flashing text
            }
        }

        async function hostStartMovie() {
            if(!confirm("Start the movie? This will instantly unlock the video for everyone.")) return;
            await fetch(`party_action.php?action=update_video_config&room_code=${roomCode}&server=${localActiveServer}&season=${localActiveSeason}&episode=${localActiveEpisode}&_t=${Date.now()}`);
            syncRoomKernel();
        }

        async function hostChangeConfig(targetServer) {
            if(!isHost) {
                alert("Only the Host can change servers!");
                return;
            }
            localActiveServer = targetServer;
            await fetch(`party_action.php?action=update_video_config&room_code=${roomCode}&server=${localActiveServer}&season=${localActiveSeason}&episode=${localActiveEpisode}&_t=${Date.now()}`);
            syncRoomKernel();
        }

        async function hostStepEpisode(direction) {
            if(!isHost) {
                alert("Only the Host can change episodes!");
                return;
            }
            let nextEp = localActiveEpisode + direction;
            if (nextEp < 1) nextEp = 1;
            localActiveEpisode = nextEp;
            
            await fetch(`party_action.php?action=update_video_config&room_code=${roomCode}&server=${localActiveServer}&season=${localActiveSeason}&episode=${localActiveEpisode}&_t=${Date.now()}`);
            syncRoomKernel();
        }

        function updateUsersListUI(users, hostId) {
            const list = document.getElementById('users-list');
            if(!list) return;
            document.getElementById('user-count').innerText = users.length;
            
            list.innerHTML = users.map(u => {
                const isUserHost = parseInt(u.id) === parseInt(hostId);
                const isReady = parseInt(u.is_ready) === 1;
                
                let tag = '';
                if(isUserHost) tag = `<span class="text-[9px] bg-red-600/90 text-white px-1.5 py-0.5 rounded border border-red-700 font-bold uppercase tracking-wider">Host</span>`;
                else if(isReady) tag = `<span class="text-[9px] bg-red-600 text-white px-1.5 py-0.5 rounded font-bold uppercase tracking-wider shadow-md">Ready</span>`;
                else tag = `<span class="text-[9px] bg-gray-800 text-gray-400 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">Lobby</span>`;

                return `
                    <div class="flex items-center justify-between bg-black/40 border border-gray-800/80 px-2.5 py-1.5 rounded-md">
                        <span class="text-xs font-bold text-gray-300 truncate max-w-[180px]">👤 ${u.username}</span>
                        ${tag}
                    </div>
                `;
            }).join('');
        }

        function updateChatInterface(messages) {
            const chatBox = document.getElementById('chat-box');
            if(!chatBox) return;
            
            if (messages.length > lastChatLength) {
                let segment = '';
                for(let i = lastChatLength; i < messages.length; i++) {
                    const m = messages[i];
                    const isMe = parseInt(m.user_id) === currentUserId;
                    
                    if (isMe) {
                        segment += `
                            <div class="flex justify-end">
                                <div class="bg-red-600 text-white p-2 rounded-lg rounded-tr-none text-xs max-w-[85%] shadow-md">
                                    <p class="whitespace-pre-wrap">${m.message}</p>
                                </div>
                            </div>`;
                    } else {
                        segment += `
                            <div class="flex justify-start flex-col">
                                <span class="text-[8px] text-gray-500 font-bold ml-1 mb-0.5 uppercase">${m.username}</span>
                                <div class="bg-gray-800 border border-gray-700 text-white p-2 rounded-lg rounded-tl-none text-xs max-w-[85%] shadow-md">
                                    <p class="whitespace-pre-wrap">${m.message}</p>
                                </div>
                            </div>`;
                    }
                }
                chatBox.innerHTML += segment;
                chatBox.scrollTop = chatBox.scrollHeight; 
                lastChatLength = messages.length;
            }
        }

        document.getElementById('chat-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if(!msg) return;
            
            input.value = ''; 
            await fetch(`party_action.php?action=send_message&room_code=${roomCode}&message=${encodeURIComponent(msg)}&_t=${Date.now()}`);
            syncRoomKernel(); 
        });

        async function toggleReady() {
            const btn = document.getElementById('ready-btn');
            const state = btn.classList.contains('bg-red-600');
            
            if (state) {
                btn.className = 'bg-gray-800 hover:bg-gray-700 text-white px-8 py-3 rounded-lg font-black uppercase tracking-widest transition border border-gray-600 text-sm shadow-md';
                btn.innerHTML = 'I AM READY';
            } else {
                btn.className = 'bg-red-600 border-red-500 text-white px-8 py-3 rounded-lg font-black uppercase tracking-widest transition text-sm shadow-[0_0_10px_rgba(255,0,0,0.5)]';
                btn.innerHTML = '✓ READY';
            }
            await fetch(`party_action.php?action=toggle_ready&room_code=${roomCode}&_t=${Date.now()}`);
            syncRoomKernel();
        }

        function copyInvite() {
            navigator.clipboard.writeText(window.location.href);
            alert("Invite link copied! Send it to your friends.");
        }
    </script>
</body>
</html>