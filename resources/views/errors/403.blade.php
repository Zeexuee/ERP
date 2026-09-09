<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-200 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html {
            zoom: 0.9;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #e2e8f0;
        }
        .apple-glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.1), 0 0 0 1px rgba(255, 255, 255, 0.9) inset;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="w-full max-w-sm apple-glass-panel rounded-3xl p-6 shadow-2xl text-center">
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-slate-900 text-white mb-4">
            403
        </span>

        <h1 class="text-lg font-bold text-slate-900 mb-1">
            Akses Ditolak
        </h1>

        <p class="text-xs text-slate-600 mb-6">
            {{ $exception->getMessage() ?: 'Anda tidak memiliki hak akses ke halaman ini.' }}
        </p>

        @auth
            @php
                $u = auth()->user();
                $homeRoute = $u->role?->defaultRouteName() ?? 'dashboard';
            @endphp

            <div class="flex items-center gap-2 justify-center">
                <a href="{{ route($homeRoute) }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Kembali
                </a>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold transition">
                        Logout
                    </button>
                </form>
            </div>
        @else
            <div class="flex justify-center">
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition">
                    Login
                </a>
            </div>
        @endauth
    </div>
</body>
</html>
