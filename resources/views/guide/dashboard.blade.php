<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard Guide') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Flash Messages -->
            @if(session('status'))
                <div id="successAlert" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex justify-between items-center">
                    <span>{{ session('status') }}</span>
                    <button onclick="document.getElementById('successAlert').remove()" class="text-green-700 hover:text-green-900">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div id="errorAlert" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex justify-between items-center">
                    <span>{{ session('error') }}</span>
                    <button onclick="document.getElementById('errorAlert').remove()" class="text-red-700 hover:text-red-900">✕</button>
                </div>
            @endif

            <!-- Create Room Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Buat Room Baru</h3>
                    <form action="{{ route('guide.room.create') }}" method="POST" class="flex gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="Nama Room (contoh: Umrah Januari 2025)"
                               class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               required>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Buat Room
                        </button>
                    </form>
                </div>
            </div>

            <!-- Room List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Daftar Room</h3>

                    @if($rooms->isEmpty())
                        <p class="text-gray-500">Belum ada room. Buat room baru untuk memulai.</p>
                    @else
                        <div class="space-y-4">
                            @foreach($rooms as $room)
                                <div class="border rounded-lg p-4 flex items-center justify-between" id="room-{{ $room->id }}">
                                    <div>
                                        <h4 class="font-medium">{{ $room->name }}</h4>
                                        <p class="text-sm text-gray-500">
                                            <span class="participant-count" data-room-id="{{ $room->id }}">-</span> jamaah online
                                            <span class="mx-2">•</span>
                                            <span class="{{ $room->is_active ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $room->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('guide.room', $room) }}"
                                           class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">
                                            Kelola
                                        </a>
                                        <button onclick="confirmDelete({{ $room->id }}, '{{ $room->name }}')"
                                                class="bg-red-100 text-red-700 px-4 py-2 rounded-md hover:bg-red-200">
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md mx-4">
            <h3 class="text-lg font-medium mb-2">Hapus Room?</h3>
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus room "<span id="deleteRoomName"></span>"? Semua data jamaah akan ikut terhapus.</p>
            <div class="flex gap-2 justify-end">
                <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200">
                    Batal
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Delete modal functions
        function confirmDelete(roomId, roomName) {
            document.getElementById('deleteRoomName').textContent = roomName;
            document.getElementById('deleteForm').action = `/guide/room/${roomId}`;
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        // Close modal on outside click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        // Real-time participant count
        async function updateParticipantCounts() {
            const counters = document.querySelectorAll('.participant-count');

            for (const counter of counters) {
                const roomId = counter.dataset.roomId;
                try {
                    const res = await fetch(`/guide/room/${roomId}/participants`);
                    const data = await res.json();
                    counter.textContent = data.count;
                } catch (e) {
                    console.error('Failed to fetch participant count:', e);
                }
            }
        }

        // Update counts on load and every 5 seconds
        updateParticipantCounts();
        setInterval(updateParticipantCounts, 5000);

        // Auto-dismiss flash messages after 5 seconds
        setTimeout(() => {
            const successAlert = document.getElementById('successAlert');
            const errorAlert = document.getElementById('errorAlert');
            if (successAlert) successAlert.remove();
            if (errorAlert) errorAlert.remove();
        }, 5000);
    </script>
    @endpush
</x-app-layout>
