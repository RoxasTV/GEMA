<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>GEMA - Room Ditutup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-gray-700 to-gray-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md text-center">
        <!-- Icon -->
        <div class="text-6xl mb-4">🔒</div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Room Ditutup</h1>

        <!-- Message -->
        <p class="text-gray-600 mb-6">
            Sesi telah berakhir. Terima kasih telah bergabung dalam perjalanan ibadah ini.
        </p>

        <!-- Room Name -->
        <div class="bg-gray-100 rounded-xl p-4 mb-6">
            <p class="text-sm text-gray-500">Room:</p>
            <p class="text-lg font-medium text-gray-800">{{ $room->name }}</p>
        </div>

        <!-- Doa -->
        <div class="bg-green-50 rounded-xl p-4 mb-6">
            <p class="text-green-800 text-lg">🤲</p>
            <p class="text-green-700 text-sm mt-2">Semoga ibadah Anda diterima Allah SWT</p>
        </div>

        <!-- Footer -->
        <p class="text-gray-400 text-sm">
            Anda dapat menutup halaman ini
        </p>
    </div>
</body>
</html>
