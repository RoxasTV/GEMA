<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $room->name }}
            </h2>
            @if($room->is_active)
                <button id="deactivateBtn" class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white">
                    Nonaktifkan Room
                </button>
            @else
                <form action="{{ route('guide.room.toggle', $room) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-md bg-green-600 hover:bg-green-700 text-white">
                        Aktifkan Room
                    </button>
                </form>
            @endif
        </div>
    </x-slot>

    <!-- Hidden form for deactivation -->
    <form id="deactivateForm" action="{{ route('guide.room.toggle', $room) }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- QR Code & Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
   <h3 class="text-lg font-medium mb-4">QR Code Room</h3>
                    <div class="flex justify-center mb-4">
                        {!! $qrCode !!}
                    </div>
                    <p class="text-sm text-gray-500 text-center break-all">
                        {{ route('room.entry', $room->slug) }}
                    </p>
                    <div class="mt-4 p-3 bg-gray-100 rounded-lg">
                        <p class="text-sm"><strong>Status:</strong>
                            <span class="{{ $room->is_active ? 'text-green-600' : 'text-red-600' }}">
                                {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </p>
                    </div>
                </div>

       <!-- Audio Controls -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Kontrol Audio</h3>

                    <!-- Mic Toggle -->
                    <div class="mb-6">
                        <button id="micToggle" class="w-full py-4 rounded-lg bg-gray-200 text-gray-700 font-medium text-lg">
                            🎤 Mic OFF
                        </button>
                    </div>

                    <!-- Mute All -->
                    <div class="space-y-2">
                        <button id="muteAllBtn" class="w-full py-3 rounded-lg bg-red-600 text-white font-medium hover:bg-red-700">
                            🔇 Mute Semua Jamaah
                        </button>
                        <button id="unmuteAllBtn" class="w-full py-3 rounded-lg bg-green-600 text-white font-medium hover:bg-green-700">
                            🔊 Unmute Semua Jamaah
                        </button>
                    </div>

                    <!-- Connection Status -->
                    <div class="mt-4 p-3 bg-gray-100 rounded-lg">
                        <p class="text-sm"><strong>Status:</strong> <span id="connectionStatus">Menghubungkan...</span></p>
                    </div>

                    <!-- Participants -->
                    <div class="mt-4">
                        <h4 class="font-medium mb-2">Jamaah Online (<span id="participantCount">0</span>)</h4>

                        <!-- Active Speaker Indicator -->
                        <div id="activeSpeakerBox" class="hidden mb-3 p-3 bg-green-100 border border-green-300 rounded-lg">
                            <p class="text-green-800 font-medium flex items-center gap-2">
                                <span class="animate-pulse">🔊</span>
                                <span id="activeSpeakerName">-</span> sedang berbicara
                            </p>
                        </div>

                        <div id="participantList" class="max-h-40 overflow-y-auto space-y-1">
                            <!-- Participants will be listed here -->
                        </div>
                    </div>
                </div>


                <!-- Prayer Selector -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Pilih Doa</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <button onclick="selectPrayer(null)" class="w-full text-left p-3 rounded-lg border hover:bg-gray-50 {{ !$room->current_prayer_id ? 'border-indigo-500 bg-indigo-50' : '' }}">
                            <span class="text-gray-500">-- Tidak ada doa --</span>
                        </button>
                        @foreach($prayers as $prayer)
                            <button onclick="selectPrayer({{ $prayer->id }})" class="w-full text-left p-3 rounded-lg border hover:bg-gray-50 {{ $room->current_prayer_id == $prayer->id ? 'border-indigo-500 bg-indigo-50' : '' }}">
                                <strong>{{ $prayer->title }}</strong>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script type="module">
        import { Room, RoomEvent, Track } from 'https://cdn.jsdelivr.net/npm/livekit-client@2/dist/livekit-client.esm.mjs';

        const wsUrl = '{{ $livekitHost }}';
        const token = '{{ $token }}';
        const roomId = {{ $room->id }};

        let room = new Room();
        let localAudioTrack = null;
        let isMicOn = false;

        // Connect to LiveKit
        async function connect() {
            try {
                await room.connect(wsUrl, token);
                document.getElementById('connectionStatus').textContent = 'Terhubung ✓';
                document.getElementById('connectionStatus').classList.add('text-green-600');
                updateParticipantList();
            } catch (error) {
                console.error('Connection failed:', error);
                document.getElementById('connectionStatus').textContent = 'Gagal terhubung';
                document.getElementById('connectionStatus').classList.add('text-red-600');
            }
        }

        // Mic toggle
        document.getElementById('micToggle').addEventListener('click', async () => {
            isMicOn = !isMicOn;
            await room.localParticipant.setMicrophoneEnabled(isMicOn);

            const btn = document.getElementById('micToggle');
            if (isMicOn) {
                btn.textContent = '🎤 Mic ON';
                btn.classList.remove('bg-gray-200', 'text-gray-700');
                btn.classList.add('bg-green-600', 'text-white');
            } else {
                btn.textContent = '🎤 Mic OFF';
                btn.classList.remove('bg-green-600', 'text-white');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            }
        });

        // Mute/Unmute all via Data Message
        const encoder = new TextEncoder();

        document.getElementById('muteAllBtn').addEventListener('click', async () => {
            const data = encoder.encode(JSON.stringify({ type: 'mute_all', muted: true }));
            await room.localParticipant.publishData(data, { reliable: true });

            // Update UI
            document.getElementById('muteAllBtn').classList.add('ring-2', 'ring-offset-2', 'ring-red-500');
            document.getElementById('unmuteAllBtn').classList.remove('ring-2', 'ring-offset-2', 'ring-green-500');
        });

        document.getElementById('unmuteAllBtn').addEventListener('click', async () => {
            const data = encoder.encode(JSON.stringify({ type: 'mute_all', muted: false }));
            await room.localParticipant.publishData(data, { reliable: true });

            // Update UI
            document.getElementById('unmuteAllBtn').classList.add('ring-2', 'ring-offset-2', 'ring-green-500');
            document.getElementById('muteAllBtn').classList.remove('ring-2', 'ring-offset-2', 'ring-red-500');
        });

        // Deactivate room - send message to all pilgrims first
        const deactivateBtn = document.getElementById('deactivateBtn');
        if (deactivateBtn) {
            deactivateBtn.addEventListener('click', async () => {
                // Confirm before deactivating
                const participantCount = document.getElementById('participantCount').textContent;
                const confirmMsg = participantCount > 0
                    ? `Ada ${participantCount} jamaah yang akan dikeluarkan. Yakin ingin menonaktifkan room?`
                    : 'Yakin ingin menonaktifkan room?';

                if (!confirm(confirmMsg)) return;

                // Send room_closed message to all pilgrims
                const data = encoder.encode(JSON.stringify({ type: 'room_closed' }));
                await room.localParticipant.publishData(data, { reliable: true });

                // Wait a moment for message to be delivered
                setTimeout(() => {
                    document.getElementById('deactivateForm').submit();
                }, 500);
            });
        }

        // Prayer selection
        window.selectPrayer = async function(prayerId) {
            await fetch(`/guide/room/${roomId}/prayer`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ prayer_id: prayerId })
            });
            location.reload();
        };

        // Update participant list
        function updateParticipantList() {
            const list = document.getElementById('participantList');
            const count = document.getElementById('participantCount');
            const participants = Array.from(room.remoteParticipants.values());

            count.textContent = participants.length;
            list.innerHTML = participants.map(p => `
                <div class="p-2 rounded text-sm flex items-center gap-2 ${p.isSpeaking ? 'bg-green-100 border border-green-300' : 'bg-gray-100'}">
                    <span class="w-2 h-2 rounded-full ${p.isSpeaking ? 'bg-green-500 animate-pulse' : 'bg-gray-400'}"></span>
                    <span class="${p.isSpeaking ? 'font-medium text-green-800' : ''}">${p.identity}</span>
                    ${p.isSpeaking ? '<span class="ml-auto text-green-600">🔊</span>' : ''}
                </div>
            `).join('');
        }

        // Handle active speakers
        function updateActiveSpeaker(speakers) {
            const box = document.getElementById('activeSpeakerBox');
            const name = document.getElementById('activeSpeakerName');

            // Filter out self (guide)
            const remoteSpeakers = speakers.filter(s => s.identity !== '{{ auth()->user()->email }}');

            if (remoteSpeakers.length > 0) {
                name.textContent = remoteSpeakers[0].identity;
                box.classList.remove('hidden');
            } else {
                box.classList.add('hidden');
            }

            // Also update participant list
            updateParticipantList();
        }

        // Event listeners
        room.on(RoomEvent.ParticipantConnected, updateParticipantList);
        room.on(RoomEvent.ParticipantDisconnected, updateParticipantList);
        room.on(RoomEvent.ActiveSpeakersChanged, updateActiveSpeaker);
        room.on(RoomEvent.TrackSubscribed, (track) => {
            if (track.kind === Track.Kind.Audio) {
                const el = track.attach();
                document.body.appendChild(el);
            }
        });

        connect();
    </script>
    @endpush
</x-app-layout>
