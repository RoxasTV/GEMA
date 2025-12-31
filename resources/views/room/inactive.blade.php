<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Tidak Aktif - GEMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md text-center">
        <div class="text-6xl mb-4">⏸️</div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Room Belum Aktif</h1>
        <p class="text-gray-600 mb-6">
            Room <strong>{{ $room->name }}</strong> belum diaktifkan oleh Guide.
        </p>
        <p class="text-gray-500 text-sm">
            Silakan tunggu atau hubungi Guide Anda.
        </p>
        <button onclick="location.reload()" class="mt-6 bg-gray-200 text-gray-700 px-6 py-3 rounded-xl hover:bg-gray-300">
            🔄 Coba Lagi
        </button>
    </div>
</body>
</html>
