<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maturity Level K3 UP2D Suluttenggo</title>
    
    <!-- Google Fonts Import -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:wght@700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Definisi Custom Font Class */
        .font-formal {
            font-family: 'Merriweather', serif;
        }
        .font-sans-elegan {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Pergeseran Panel Batik (Rasio 55% Batik : 45% Space Form) */
        .auth-wrapper.sign-up-mode .panel-batik {
            transform: translateX(81.818%);
            border-top-left-radius: 1.5rem;
            border-bottom-left-radius: 1.5rem;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .auth-wrapper.sign-up-mode .form-login {
            opacity: 0;
            pointer-events: none;
        }

        .auth-wrapper.sign-up-mode .form-register {
            opacity: 1;
            pointer-events: auto;
            z-index: 10;
        }
    </style>
</head>
<body class="bg-white min-h-screen overflow-x-hidden font-sans-elegan">

    <div id="authWrapper" class="auth-wrapper relative w-full min-h-screen flex flex-col md:flex-row overflow-hidden">

        <!-- ============================================================== -->
        <!-- PANEL BATIK (55% LEBAR LAYAR)                                  -->
        <!-- ============================================================== -->
        <div class="panel-batik hidden md:block absolute top-0 bottom-0 left-0 w-[55%] z-20 transition-all duration-700 ease-in-out bg-blue-900 shadow-2xl rounded-r-3xl">
            <img src="{{ asset('images/batik biruu.png') }}" alt="Batik PLN" class="w-full h-full object-cover">
        </div>

        <!-- ============================================================== -->
        <!-- FORM LOGIN (45% LEBAR LAYAR - POSISI KANAN)                    -->
        <!-- ============================================================== -->
        <div class="form-login w-full md:w-[45%] min-h-screen p-8 md:p-12 flex flex-col justify-between bg-white z-10 transition-all duration-700 ease-in-out md:ml-auto">
            
            <!-- Logo PLN -->
            <div class="flex justify-end -mt-10 pt-2">
                <img src="{{ asset('images/pln transparan.png') }}" alt="Logo PLN" class="h-20 w-auto object-contain">
            </div>

            <!-- Wrapper Form -->
            <div class="mt-4 mb-auto max-w-md w-full mx-auto">
                <!-- Judul utama -->
                <h1 class="font-formal text-5xl font-bold text-gray-900 tracking-tight mt-4">Selamat Datang</h1>
                
                <!-- Seluruh Konten di bawahnya -->
                <div class="mt-16">
                    <!-- Sub-Judul -->
                    <div class="border-l-2 border-blue-600 pl-4 py-0.5 mb-12">
                        <h2 class="text-xl font-bold text-gray-800 tracking-wider uppercase">Maturity Level K3</h2>
                        <p class="text-xs font-bold text-gray-700 tracking-widest uppercase mt-1">UP2D PLN Suluttenggo</p>
                    </div>

                    @if($errors->any() && !($errors->has('name') || $errors->has('password_confirmation')))
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-xs mb-8">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Form Login -->
                    <form action="{{ route('login.post') }}" method="POST" class="space-y-7">
                        @csrf
                        
                        <!-- Input Email dengan Ikon Surat -->
                        <div>
                            <label for="login-email" class="block text-xs font-semibold text-gray-600 mb-1.5 tracking-wide">EMAIL</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="email" id="login-email" name="email" value="{{ old('email') }}" required placeholder="Masukkan email anda" 
                                    class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800 transition shadow-sm placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Input Password dengan Ikon Gembok & Mata -->
                        <div>
                            <label for="login-password" class="block text-xs font-semibold text-gray-600 mb-1.5 tracking-wide">PASSWORD</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input type="password" id="login-password" name="password" required placeholder="Masukkan password" 
                                    class="w-full pl-11 pr-10 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800 transition shadow-sm placeholder-gray-400">
                                
                                <button type="button" onclick="togglePassword('login-password', 'eye-icon-login')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg id="eye-icon-login" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full py-3 bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-xl transition duration-200 text-sm mt-4 shadow-md hover:shadow-lg tracking-wide">
                            Login
                        </button>
                    </form>

                    <div class="mt-12 text-center text-xs text-gray-500">
                        Belum punya akun? 
                        <button type="button" onclick="switchToRegister()" class="text-blue-700 font-bold hover:underline ml-1 focus:outline-none">
                            Sign Up disini
                        </button>
                    </div>
                </div>
            </div>

            <div></div>
        </div>

        <!-- ============================================================== -->
        <!-- FORM SIGN UP (45% LEBAR LAYAR - POSISI KIRI)                   -->
        <!-- ============================================================== -->
        <div class="form-register w-full md:w-[45%] min-h-screen p-8 md:p-12 flex flex-col justify-between bg-white opacity-0 pointer-events-none transition-all duration-700 ease-in-out absolute top-0 left-0">
            
            <!-- Logo PLN -->
            <div class="flex justify-start -mt-10 pt-2">
                <img src="{{ asset('images/pln transparan.png') }}" alt="Logo PLN" class="h-20 w-auto object-contain">
            </div>

            <!-- Wrapper Form -->
            <div class="mt-4 mb-auto max-w-md w-full mx-auto">
                <!-- Judul utama -->
                <h1 class="font-formal text-4xl font-bold text-gray-900 tracking-tight mt-4">Buat Akun</h1>
                
                <!-- Seluruh Konten di bawahnya -->
                <div class="mt-14">
                    <!-- Sub-Judul -->
                    <div class="border-l-2 border-blue-600 pl-4 py-0.5 mb-10">
                        <h2 class="text-xl font-bold text-gray-800 tracking-wider uppercase">Maturity Level K3</h2>
                        <p class="text-xs font-bold text-gray-700 tracking-widest uppercase mt-1">UP2D PLN Suluttenggo</p>
                    </div>

                    @if($errors->any() && ($errors->has('name') || $errors->has('password_confirmation')))
                        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded text-xs mb-6">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <!-- Form Register -->
                    <form action="{{ route('register.post') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <!-- Input Nama Lengkap dengan Ikon User/Orang -->
                        <div>
                            <label for="reg-name" class="block text-xs font-semibold text-gray-600 mb-1 tracking-wide">NAMA LENGKAP</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" id="reg-name" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap" 
                                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800 shadow-sm placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Email dengan Ikon Surat -->
                        <div>
                            <label for="reg-email" class="block text-xs font-semibold text-gray-600 mb-1 tracking-wide">EMAIL / GMAIL</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="email" id="reg-email" name="email" value="{{ old('email') }}" required placeholder="nama@gmail.com" 
                                    class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800 shadow-sm placeholder-gray-400">
                            </div>
                        </div>

                        <!-- Password dengan Ikon Gembok & Mata -->
                        <div>
                            <label for="reg-password" class="block text-xs font-semibold text-gray-600 mb-1 tracking-wide">PASSWORD</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input type="password" id="reg-password" name="password" required placeholder="••••••••" 
                                    class="w-full pl-10 pr-10 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800 shadow-sm placeholder-gray-400">
                                <button type="button" onclick="togglePassword('reg-password', 'eye-icon-reg')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg id="eye-icon-reg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="reg-confirm" class="block text-xs font-semibold text-gray-600 mb-1 tracking-wide">KONFIRMASI PASSWORD</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input type="password" id="reg-confirm" name="password_confirmation" required placeholder="••••••••" 
                                    class="w-full pl-10 pr-10 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent text-sm text-gray-800 shadow-sm placeholder-gray-400">
                                <button type="button" onclick="togglePassword('reg-confirm', 'eye-icon-confirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg id="eye-icon-confirm" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" 
                            class="w-full py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold rounded-xl transition duration-200 text-sm mt-2 shadow-md hover:shadow-lg tracking-wide">
                            Daftar
                        </button>
                    </form>

                    <div class="mt-6 text-center text-xs text-gray-500">
                        Sudah punya akun? 
                        <button type="button" onclick="switchToLogin()" class="text-blue-700 font-bold hover:underline ml-1 focus:outline-none">
                            Login disini
                        </button>
                    </div>
                </div>
            </div>

            <div></div>
        </div>

    </div>

    <script>
        const authWrapper = document.getElementById('authWrapper');

        function switchToRegister() {
            authWrapper.classList.add('sign-up-mode');
        }

        function switchToLogin() {
            authWrapper.classList.remove('sign-up-mode');
        }

        // Toggle Hide / Show Password
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a8.959 8.959 0 013.682-.782c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21f-3-3m-3.95-3.95l-3.95-3.95M9 9l3 3" />
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        }
    </script>
</body>
</html>