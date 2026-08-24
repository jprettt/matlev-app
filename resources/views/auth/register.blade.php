<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Maturity Level K3 UP2D Suluttenggo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        h1 {
            font-family: 'Merriweather', serif;
        }
    </style>
</head>
<body class="bg-white min-h-screen flex flex-col md:flex-row m-0 p-0 overflow-x-hidden">

    <!-- Sisi Kiri: Batik Full Screen Height -->
    <div class="md:w-1/2 w-full h-64 md:h-screen relative bg-blue-900 flex-shrink-0">
        <img src="{{ asset('images/batik biru.jpg') }}" alt="Batik PLN" class="w-full h-full object-cover">
    </div>

    <!-- Sisi Kanan: Form Sign Up Langsung di Halaman -->
    <div class="md:w-1/2 w-full min-h-screen p-8 md:p-16 flex flex-col justify-between bg-white">
        
        <!-- Logo PLN Transparan -->
        <div class="flex justify-end">
            <img src="{{ asset('images/pln transparan.png') }}" alt="Logo PLN" class="h-12 w-auto object-contain">
        </div>

        <div class="my-auto max-w-md w-full mx-auto">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Buat Akun</h1>
                <h2 class="text-lg font-semibold text-gray-700 mt-1">Maturity Level K3</h2>
                <p class="text-xs font-medium text-gray-500">UP2D PLN Suluttenggo</p>
            </div>

            <!-- Alert Error -->
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-xs mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- Form Sign Up -->
            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-xs font-medium text-gray-600 mb-1">Username / Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap" 
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800">
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-gray-600 mb-1">Email / Gmail</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="nama@gmail.com" 
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800">
                </div>

                <div>
                    <label for="password" class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" 
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-gray-600 mb-1">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="••••••••" 
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800">
                </div>

                <button type="submit" 
                    class="w-full py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-lg transition duration-200 text-sm mt-2">
                    Daftar
                </button>
            </form>

            <!-- Footer Link ke Login -->
            <div class="mt-6 text-center text-xs text-gray-600">
                Sudah punya akun? 
                <a href="{{ route('login') }}" class="text-blue-600 font-bold hover:underline ml-1">Login disini</a>
            </div>
        </div>

        <div></div>

    </div>

</body>
</html>