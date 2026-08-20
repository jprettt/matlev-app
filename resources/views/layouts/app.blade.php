<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLN Maturity Level K3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex flex-col font-sans">
    <nav class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <span class="text-cyan-400 font-bold text-xl tracking-wider">PLN MATURITY LEVEL</span>
            <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">K3 & Keamanan</span>
        </div>
        @auth
        <div class="flex items-center space-x-4 text-xs">
            <span class="text-gray-300">{{ Auth::user()->name }} ({{ strtoupper(Auth::user()->role) }})</span>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-600/80 hover:bg-red-600 text-white px-3 py-1.5 rounded transition">Logout</button>
            </form>
        </div>
        @endauth
    </nav>

    <main class="flex-grow p-6">
        @if(session('success'))
            <div class="bg-emerald-900/50 border border-emerald-500 text-emerald-200 px-4 py-3 rounded-lg mb-6 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>