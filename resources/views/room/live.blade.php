<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GEMA - {{ $room->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-size: 20px; }
        .mic-btn { width: 120px; height: 120px; }
    </style>
</head>
<body class="bg-gradient-to-b from-green-800 to-green-900 min-h-screen">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-green-900/50 text-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold">🕌 GEMA</h1>
                    <p class="text-sm text-green-200">{{ $room->name }}</p>
                </div>
                <div class="text-right flex items-center gap-3">
                    <div>
                        <p class="text-sm text-green-200">ID: {{ $pilgrimId }}</p>
                        <p id="connectionStatus" class="text-xs text-yellow-300">Menghubungkan...</p>
                    </div>
                    <button id="leaveBtn" class="bg-red-500/80 hover:bg-red-600 text-white px-3 py-2 rounded-lg text-sm">
                        Keluar
                    </button>
                </div>
            </div>
        </header>

        <!-- Prayer Display -->
        <main class="flex-1 p-4 overflow-y-auto">
            <div id="prayerContainer" class="bg-white/95 rounded-2xl p-6 min-h-[200px]">
                <div id="prayerContent">
                    @if($room->currentPrayer)
                        <h2 class="text-2xl font-bold text-green-800 mb-4 text-center">
                            {{ $room->currentPrayer->title }}
                        </h2>
                        <div class="text-xl leading-relaxed text-gray-800 whitespace-pre-line">
                            {{ $room->currentPrayer->content }}
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-8">
                            <div class="text-5xl mb-4">📖</div>
                            <p class="text-xl">Menunggu Guide memilih doa...</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>

        <!-- Mic Control Footer -->
        <footer class="bg-green-900/80 p-6">
            <!-- Hard Mute Indicator -->
            <div id="hardMuteIndicator" class="hidden bg-red-500 text-white text-center py-2 rounded-lg mb-4">
                🔇 Anda di-mute oleh Guide
            </div>

            <div class="flex flex-col items-center">
                <!-- Connection Status (above mic button) -->
                <p id="connectionStatusFooter" class="text-yellow-300 mb-4 text-xl font-medium">⏳ Menghubungkan...</p>

                <!-- Mic Button -->
                <button id="micBtn" class="mic-btn rounded-full bg-gray-400 text-white text-4xl flex items-center justify-center shadow-lg transition-all">
                    🎤
                </button>
                <p id="micStatus" class="text-white mt-3 text-lg">Tahan untuk bicara</p>
            </div>

            <!-- Active Speaker -->
            <div id="activeSpeaker" class="mt-4 text-center text-green-200 text-sm hidden">
                🔊 <span id="speakerName"></span> sedang berbicara
            </div>
        </footer>
    </div>

    <script type="module">
        import { Room, RoomEvent, Track, ConnectionState } from 'https://cdn.jsdelivr.net/npm/livekit-client@2/dist/livekit-client.esm.mjs';

        const wsUrl = '{{ $livekitHost }}';
        const token = '{{ $token }}';
        const roomSlug = '{{ $room->slug }}';
        const myIdentity = '{{ $pilgrimId }}';

        let room = new Room();
        let isMicOn = false;
        let isHardMuted = false;
        let reconnectAttempts = 0;
        const maxReconnectAttempts = 5;

        // Wake Lock
        let wakeLock = null;
        async function requestWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                }
            } catch (err) {
                console.log('Wake Lock not supported');
            }
        }

        // Update connection status UI
        function updateConnectionUI(status, message) {
            const headerStatus = document.getElementById('connectionStatus');
            const footerStatus = document.getElementById('connectionStatusFooter');

            headerStatus.classList.remove('text-yellow-300', 'text-green-300', 'text-red-300');
            footerStatus.classList.remove('text-yellow-300', 'text-green-300', 'text-red-300');

            if (status === 'connected') {
                headerStatus.textContent = 'Terhubung ✓';
                headerStatus.classList.add('text-green-300');
                footerStatus.textContent = '✅ Terhubung';
                footerStatus.classList.add('text-green-300');
            } else if (status === 'connecting') {
                headerStatus.textContent = message || 'Menghubungkan...';
                headerStatus.classList.add('text-yellow-300');
                footerStatus.textContent = '⏳ ' + (message || 'Menghubungkan...');
                footerStatus.classList.add('text-yellow-300');
            } else if (status === 'disconnected') {
                headerStatus.textContent = message || 'Terputus';
                headerStatus.classList.add('text-red-300');
                footerStatus.textContent = '❌ ' + (message || 'Terputus');
                footerStatus.classList.add('text-red-300');
            }
        }

        // Connect with reconnect logic
        async function connect() {
            try {
                updateConnectionUI('connecting', 'Menghubungkan...');
                await room.connect(wsUrl, token);
                reconnectAttempts = 0;
                updateConnectionUI('connected');
                requestWakeLock();
            } catch (error) {
                console.error('Connection failed:', error);
                updateConnectionUI('disconnected', 'Gagal terhubung');
                attemptReconnect();
            }
        }

        // Reconnect logic
        async function attemptReconnect() {
            if (reconnectAttempts >= maxReconnectAttempts) {
                updateConnectionUI('disconnected', 'Koneksi gagal. Refresh halaman.');
                return;
            }

            reconnectAttempts++;
            const delay = Math.min(1000 * Math.pow(2, reconnectAttempts), 10000); // Exponential backoff, max 10s

            updateConnectionUI('connecting', `Reconnect (${reconnectAttempts}/${maxReconnectAttempts})...`);

            setTimeout(async () => {
                try {
                    await room.connect(wsUrl, token);
                    reconnectAttempts = 0;
                    updateConnectionUI('connected');
                    requestWakeLock();
                } catch (error) {
                    console.error('Reconnect failed:', error);
                    attemptReconnect();
                }
            }, delay);
        }

        // Handle disconnection events
        room.on(RoomEvent.Disconnected, (reason) => {
            console.log('Disconnected:', reason);
            // Don't reconnect if intentionally disconnected (room closed)
            if (reason !== 'CLIENT_INITIATED') {
                updateConnectionUI('disconnected', 'Koneksi terputus');
                attemptReconnect();
            }
        });

        room.on(RoomEvent.Reconnecting, () => {
            updateConnectionUI('connecting', 'Reconnecting...');
        });

        room.on(RoomEvent.Reconnected, () => {
            reconnectAttempts = 0;
            updateConnectionUI('connected');
        });

        // Mic - Push to Talk (Hold to Talk)
        const micBtn = document.getElementById('micBtn');
        const micStatus = document.getElementById('micStatus');

        // Mouse events (desktop)
        micBtn.addEventListener('mousedown', startTalking);
        micBtn.addEventListener('mouseup', stopTalking);
        micBtn.addEventListener('mouseleave', stopTalking);

        // Touch events (mobile)
        micBtn.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startTalking();
        });
        micBtn.addEventListener('touchend', (e) => {
            e.preventDefault();
            stopTalking();
        });
        micBtn.addEventListener('touchcancel', stopTalking);

        async function startTalking() {
            if (isHardMuted) return;

            try {
                isMicOn = true;
                await room.localParticipant.setMicrophoneEnabled(true);
                updateMicUI();
            } catch (error) {
                console.error('Mic error:', error);
                isMicOn = false;

                // Show mic permission error
                micStatus.textContent = '⚠️ Izin mic ditolak';
                micBtn.classList.remove('bg-green-500', 'bg-gray-400');
                micBtn.classList.add('bg-orange-500');

                // Show detailed instruction based on device
                const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
                const isAndroid = /Android/.test(navigator.userAgent);

                let instruction = 'Tidak dapat mengakses mikrofon.\n\n';
                if (isIOS) {
                    instruction += 'Cara mengaktifkan:\n1. Buka Pengaturan Safari\n2. Izinkan akses Mikrofon\n3. Refresh halaman ini';
                } else if (isAndroid) {
                    instruction += 'Cara mengaktifkan:\n1. Tap ikon gembok/info di address bar\n2. Izinkan Mikrofon\n3. Refresh halaman ini';
                } else {
                    instruction += 'Cara mengaktifkan:\n1. Klik ikon gembok di address bar\n2. Izinkan Mikrofon\n3. Refresh halaman ini';
                }

                alert(instruction);
            }
        }

        async function stopTalking() {
            if (isHardMuted) return;

            isMicOn = false;
            await room.localParticipant.setMicrophoneEnabled(false);
            updateMicUI();
        }

        function updateMicUI() {
            if (isHardMuted) {
                micBtn.classList.remove('bg-green-500', 'bg-gray-400');
                micBtn.classList.add('bg-red-500');
                micStatus.textContent = 'Di-mute oleh Guide';
                return;
            }

            if (isMicOn) {
                micBtn.classList.remove('bg-gray-400', 'bg-red-500');
                micBtn.classList.add('bg-green-500');
                micStatus.textContent = '🔊 Sedang bicara...';
            } else {
                micBtn.classList.remove('bg-green-500', 'bg-red-500');
                micBtn.classList.add('bg-gray-400');
                micStatus.textContent = 'Tahan untuk bicara';
            }
        }

        // Handle hard mute from guide via Data Message
        const decoder = new TextDecoder();

        room.on(RoomEvent.DataReceived, (payload, participant) => {
            try {
                const message = JSON.parse(decoder.decode(payload));

                if (message.type === 'mute_all') {
                    isHardMuted = message.muted;

                    if (isHardMuted) {
                        // Force mute local mic
                        isMicOn = false;
                        room.localParticipant.setMicrophoneEnabled(false);
                        document.getElementById('hardMuteIndicator').classList.remove('hidden');
                    } else {
                        document.getElementById('hardMuteIndicator').classList.add('hidden');
                    }

                    updateMicUI();
                }

                // Handle room closed by guide
                if (message.type === 'room_closed') {
                    room.disconnect();
                    window.location.href = `/room/${roomSlug}?closed=1`;
                }
            } catch (e) {
                console.error('Failed to parse data message:', e);
            }
        });

        // Subscribe to audio tracks
        room.on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
            if (track.kind === Track.Kind.Audio) {
                const el = track.attach();
                document.body.appendChild(el);
            }
        });

        // Active speaker
        room.on(RoomEvent.ActiveSpeakersChanged, (speakers) => {
            const indicator = document.getElementById('activeSpeaker');
            const nameEl = document.getElementById('speakerName');

            if (speakers.length > 0 && speakers[0].identity !== myIdentity) {
                nameEl.textContent = speakers[0].identity;
                indicator.classList.remove('hidden');
            } else {
                indicator.classList.add('hidden');
            }
        });

        // Poll for prayer updates
        async function pollPrayer() {
            try {
                const res = await fetch(`/api/room/${roomSlug}/prayer`);
                const data = await res.json();
                const container = document.getElementById('prayerContent');

                if (data.prayer) {
                    container.innerHTML = `
                        <h2 class="text-2xl font-bold text-green-800 mb-4 text-center">${data.prayer.title}</h2>
                        <div class="text-xl leading-relaxed text-gray-800 whitespace-pre-line">${data.prayer.content}</div>
                    `;
                } else {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 py-8">
                            <div class="text-5xl mb-4">📖</div>
                            <p class="text-xl">Menunggu Guide memilih doa...</p>
                        </div>
                    `;
                }
            } catch (e) {
                console.error('Failed to poll prayer:', e);
            }
        }

        // Poll every 3 seconds
        setInterval(pollPrayer, 3000);

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (wakeLock) wakeLock.release();
            room.disconnect();
        });

        // Leave room button
        document.getElementById('leaveBtn').addEventListener('click', () => {
            if (confirm('Yakin ingin keluar dari room?')) {
                if (wakeLock) wakeLock.release();
                room.disconnect();
                window.location.href = `/room/${roomSlug}`;
            }
        });

        connect();
    </script>
</body>
</html>

