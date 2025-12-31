<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GEMA - {{ $room->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-size: 18px; }
    </style>
</head>
<body class="bg-gradient-to-b from-green-800 to-green-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-6xl mb-4">🕌</div>
            <h1 class="text-3xl font-bold text-green-800">GEMA</h1>
            <p class="text-gray-600 mt-2">Audio Tour Guide</p>
        </div>

        <!-- Room Info -->
        <div class="bg-green-50 rounded-xl p-4 mb-6 text-center">
            <p class="text-sm text-green-700">Anda akan bergabung ke:</p>
            <p class="text-xl font-bold text-green-800">{{ $room->name }}</p>
        </div>

        <!-- Error Message -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                <p class="text-red-700 text-center">{{ $errors->first() }}</p>
            </div>
        @endif

        <!-- Form -->
        <form action="{{ route('room.process', $room->slug) }}" method="POST" id="loginForm">
            @csrf
            <div class="mb-6">
                <label for="pilgrim_id" class="block text-lg font-medium text-gray-700 mb-2">
                    Masukkan ID Jamaah
                </label>
                <input type="text"
                       id="pilgrim_id"
                       name="pilgrim_id"
                       placeholder="Nama atau Nomor ID Anda"
                       class="w-full px-4 py-4 text-xl border-2 border-gray-300 rounded-xl focus:border-green-500 focus:ring-green-500"
                       required
                       autofocus>
                <p class="text-sm text-gray-500 mt-2">Contoh: Ahmad atau J-001</p>
            </div>

            <button type="submit"
                    id="submitBtn"
                    class="w-full bg-green-600 text-white text-xl font-bold py-4 rounded-xl hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                <span id="btnText">Masuk Room →</span>
                <span id="btnLoading" class="hidden">⏳ Memproses...</span>
            </button>
        </form>

        <!-- Footer -->
        <p class="text-center text-gray-400 text-sm mt-8">
            Pastikan volume HP Anda sudah dinyalakan
        </p>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');

            // Disable button and show loading
            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
        });
    </script>
</body>
</html>
