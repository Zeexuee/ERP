<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-200 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ERP</title>
    
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

        .liquid-blob-1 {
            position: fixed;
            top: -15%;
            left: 10%;
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(148, 163, 184, 0.45) 0%, rgba(203, 213, 225, 0) 70%);
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }

        .liquid-blob-2 {
            position: fixed;
            bottom: -20%;
            right: 10%;
            width: 750px;
            height: 750px;
            background: radial-gradient(circle, rgba(203, 213, 225, 0.55) 0%, rgba(226, 232, 240, 0) 70%);
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
        }

        .apple-glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(35px) saturate(200%);
            -webkit-backdrop-filter: blur(35px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.9);
            box-shadow: 
                0 20px 40px -10px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(255, 255, 255, 0.95) inset;
        }

        .apple-input {
            background: rgba(255, 255, 255, 0.85);
            border: 1px solid rgba(15, 23, 42, 0.15);
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .apple-input:focus {
            outline: none;
            background: #ffffff;
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.12);
        }

        .btn-dark {
            background: #0f172a;
            color: #ffffff;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-dark:hover {
            background: #1e293b;
        }
    </style>
</head>
<body class="min-h-full flex items-center justify-center p-4 relative overflow-y-auto">

    <div class="liquid-blob-1"></div>
    <div class="liquid-blob-2"></div>

    <div class="w-full max-w-sm z-10 my-8">
        
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                ERP
            </h1>
        </div>

        <div class="apple-glass-panel rounded-3xl p-6 shadow-2xl relative">
            
            <div class="border-b border-slate-900/10 pb-3 mb-5">
                <h2 class="text-base font-bold text-slate-900">Login</h2>
            </div>

            @if ($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-3.5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        placeholder="email@erp.com" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-sm"
                    >
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        placeholder="••••••••" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-sm"
                    >
                </div>

                <div class="flex items-center text-xs text-slate-600 pt-0.5">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-slate-900 focus:ring-0 border-slate-300">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl btn-dark text-xs font-semibold mt-2">
                    Masuk
                </button>
            </form>

        </div>

    </div>
</body>
</html>
