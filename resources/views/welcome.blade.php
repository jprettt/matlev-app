<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLN Maturity Level K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center p-6">
    <div class="max-w-xl w-full text-center bg-gray-800 p-8 rounded-2xl border border-gray-700 shadow-2xl">
        <h1 class="text-3xl font-extrabold text-cyan-400 mb-2">PLN MATURITY LEVEL</h1>
        <p class="text-sm text-gray-400 mb-8">Platform Evaluasi K3 & Keamanan Kerja</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('login') }}" class="py-3 px-6 bg-cyan-600 hover:bg-cyan-500 font-bold rounded-xl transition text-center shadow-lg">
                Masuk / Login
            </a>
            <a href="{{ route('register') }}" class="py-3 px-6 bg-gray-700 hover:bg-gray-600 border border-cyan-500/50 text-cyan-300 font-bold rounded-xl transition text-center shadow-lg">
                Daftar / Register
            </a>
        </div>
    </div>
</body>
</html>