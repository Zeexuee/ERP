<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-200 text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Sales - {{ $title ?? 'Dashboard' }}</title>
    <!-- SF Pro / Inter Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #e2e8f0;
        }

        /* Ambient Fluid Blobs in Background for backdrop-blur liquid effect */
        .liquid-blob-1 {
            position: fixed;
            top: -15%;
            left: 15%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(148, 163, 184, 0.5) 0%, rgba(203, 213, 225, 0) 70%);
            filter: blur(70px);
            pointer-events: none;
            z-index: 0;
        }
        .liquid-blob-2 {
            position: fixed;
            bottom: -15%;
            right: 10%;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(203, 213, 225, 0.6) 0%, rgba(226, 232, 240, 0) 70%);
            filter: blur(90px);
            pointer-events: none;
            z-index: 0;
        }

        /* Liquid Glass Specular Panel */
        .apple-glass-panel {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(30px) saturate(200%);
            -webkit-backdrop-filter: blur(30px) saturate(200%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 
                0 15px 35px -5px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(255, 255, 255, 0.9) inset;
        }

        /* Liquid Glass Specular Card */
        .apple-glass-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.75) 0%, rgba(255, 255, 255, 0.4) 100%);
            backdrop-filter: blur(25px) saturate(190%);
            -webkit-backdrop-filter: blur(25px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 
                0 10px 30px 0 rgba(15, 23, 42, 0.04),
                0 0 0 1px rgba(255, 255, 255, 0.9) inset;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .apple-glass-card:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.65) 100%);
            border-color: rgba(255, 255, 255, 1);
            box-shadow: 
                0 14px 40px 0 rgba(15, 23, 42, 0.08),
                0 0 0 1px rgba(255, 255, 255, 1) inset;
        }

        /* Inputs */
        .apple-input {
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(15, 23, 42, 0.15);
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .apple-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.95);
            border-color: #0f172a;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.12);
        }

        /* Dark Neutral Buttons (No bright colors) */
        .btn-dark {
            background: #0f172a;
            color: #ffffff;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px 0 rgba(15, 23, 42, 0.2);
        }

        .btn-dark:hover {
            background: #1e293b;
            box-shadow: 0 6px 20px 0 rgba(15, 23, 42, 0.3);
        }

        .btn-subtle {
            background: rgba(255, 255, 255, 0.8);
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.15);
            font-weight: 600;
            box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.03);
            transition: all 0.2s ease;
        }

        .btn-subtle:hover {
            background: rgba(255, 255, 255, 0.98);
            border-color: rgba(15, 23, 42, 0.35);
        }

        /* Badges: Neutral Dark/Slate Tones */
        .badge-dark {
            background: rgba(15, 23, 42, 0.08);
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.15);
        }

        /* Badges: Neutral Dark/Slate Tones */
        .badge-dark {
            background: rgba(15, 23, 42, 0.08);
            color: #0f172a;
            border: 1px solid rgba(15, 23, 42, 0.15);
        }

        /* Sidebar Rail Fixed Geometry */
        #floatingSidebar {
            transition: width 500ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        #floatingSidebar.is-collapsed {
            width: 4.5rem !important; /* 72px slim rail */
        }

        /* Nav Item Icon Anchor (Fixed 40px box - ZERO jitter) */
        .nav-icon-box {
            width: 2.5rem; /* 40px */
            height: 2.5rem; /* 40px */
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Text Label Transitions */
        .nav-text-label, .brand-text-label {
            white-space: nowrap;
            opacity: 1;
            max-width: 200px;
            transition: opacity 600ms cubic-bezier(0.16, 1, 0.3, 1) 200ms, max-width 500ms ease;
            overflow: hidden;
        }

        .is-collapsed .nav-text-label,
        .is-collapsed .brand-text-label {
            opacity: 0 !important;
            max-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            transition: opacity 120ms ease, max-width 300ms ease;
        }

        .nav-section-title {
            opacity: 1;
            max-height: 2.5rem;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
            transition: opacity 250ms ease, max-height 500ms cubic-bezier(0.16, 1, 0.3, 1), margin 500ms cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            white-space: nowrap;
        }
        .is-collapsed .nav-section-title {
            opacity: 0 !important;
            max-height: 0 !important;
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            pointer-events: none;
        }

        .is-collapsed .brand-header-container {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    </style>
</head>
<body class="h-full flex overflow-hidden relative text-slate-900">

    <!-- Ambient Fluid Background Blobs -->
    <div class="liquid-blob-1"></div>
    <div class="liquid-blob-2"></div>

    <!-- Floating Sidebar (Elongated Glass Rail with Fixed Icon Anchors) -->
    <aside id="floatingSidebar" class="w-64 my-4 ml-4 apple-glass-panel rounded-3xl border border-white flex flex-col justify-between hidden md:flex z-20 relative shadow-xl overflow-hidden">
        <div>
            <!-- Brand Header & Toggle Icon -->
            <div class="brand-header-container h-16 flex items-center justify-between px-2 border-b border-slate-900/10 w-full">
                <div class="brand-text-label pl-2">
                    <span class="font-extrabold text-lg tracking-tight text-slate-900">ERP Sales</span>
                    <span class="block text-[9px] font-bold text-slate-500 tracking-widest uppercase">System</span>
                </div>
                <button id="toggleSidebarBtn" type="button" class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-white/60 transition" title="Buka / Tutup Sidebar">
                    <svg id="toggleIcon" class="w-5 h-5 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Rail Items with Fixed Icons & Delayed Text -->
            <nav class="px-2 py-6 space-y-1">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" title="Dashboard" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                    <div class="nav-icon-box">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                    <span class="nav-text-label">Dashboard</span>
                </a>

                <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Penjualan</div>

                <!-- Customers -->
                <a href="{{ route('customers.index') }}" title="Pelanggan" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('customers.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                    <div class="nav-icon-box">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <span class="nav-text-label">Pelanggan</span>
                </a>


                <!-- Sales Orders -->
                <a href="{{ route('sales-orders.index') }}" title="Sales Orders" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('sales-orders.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                    <div class="nav-icon-box">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <span class="nav-text-label">Sales Orders</span>
                </a>

                <!-- Invoices -->
                <a href="{{ route('invoices.index') }}" title="Faktur" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                    <div class="nav-icon-box">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="nav-text-label">Faktur (Invoices)</span>
                </a>

                <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Katalog & Produksi</div>

                <!-- Products -->
                <a href="{{ route('products.index') }}" title="Produk & Stok" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                    <div class="nav-icon-box">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="nav-text-label">Produk & Stok</span>
                </a>

                <!-- Production Requests -->
                <a href="{{ route('production-requests.index') }}" title="Permintaan Produksi" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production-requests.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                    <div class="nav-icon-box">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.594 15.12a2 2 0 00-1.022.547l-.29.29a2 2 0 00.586 3.414l2.12.707a6 6 0 003.8.024l.36-.12a6 6 0 013.8.024l2.12.707a2 2 0 002.535-1.927l-.044-.41a2 2 0 00-.547-1.022l-.29-.29z"/>
                        </svg>
                    </div>
                    <span class="nav-text-label">Permintaan Produksi</span>
                </a>
            </nav>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col overflow-hidden relative z-10">
        
        <!-- Floating Header Bar (Clean, Independent Header) -->
        <header class="h-14 mt-4 ml-4 mr-4 apple-glass-panel rounded-2xl border border-white flex items-center justify-between px-6 z-10 shadow-md">
            <h1 class="text-base font-extrabold text-slate-900 tracking-tight">
                {{ $title ?? 'ERP Sales' }}
            </h1>
            <div class="flex items-center gap-2">
                <span id="connectionStatusBadge" class="px-3 py-1 rounded-full text-[11px] font-bold badge-dark">
                    Online
                </span>
                <span class="px-3 py-1 rounded-full text-[11px] font-mono font-bold badge-dark">
                    IP: {{ request()->ip() }}
                </span>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6 space-y-6">

            <!-- Flash Notifications -->
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-white/80 border border-slate-900/20 text-slate-900 text-sm font-bold shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-2xl bg-white/80 border border-slate-900/20 text-slate-900 text-sm font-bold shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Elongated Vertical Rail Collapse / Expand Logic
        const sidebar = document.getElementById('floatingSidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebarBtn');
        const toggleIcon = document.getElementById('toggleIcon');

        function setSidebarRailState(collapsed) {
            if (collapsed) {
                sidebar.classList.add('is-collapsed');
                if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)';
                localStorage.setItem('sidebar_rail_collapsed', 'true');
            } else {
                sidebar.classList.remove('is-collapsed');
                if (toggleIcon) toggleIcon.style.transform = 'rotate(0deg)';
                localStorage.setItem('sidebar_rail_collapsed', 'false');
            }
        }

        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', () => {
                const isCurrentlyCollapsed = sidebar.classList.contains('is-collapsed');
                setSidebarRailState(!isCurrentlyCollapsed);
            });
        }

        // Restore sidebar preference
        if (localStorage.getItem('sidebar_rail_collapsed') === 'true') {
            setSidebarRailState(true);
        }

        // Online / Offline Monitor
        function checkNetworkStatus() {
            const badge = document.getElementById('connectionStatusBadge');
            if (badge) {
                if (navigator.onLine) {
                    badge.textContent = 'Online';
                    badge.className = 'px-3 py-1 rounded-full text-[11px] font-bold badge-dark';
                } else {
                    badge.textContent = 'Offline';
                    badge.className = 'px-3 py-1 rounded-full text-[11px] font-bold badge-dark opacity-60';
                }
            }
        }

        window.addEventListener('online', checkNetworkStatus);
        window.addEventListener('offline', checkNetworkStatus);
        document.addEventListener('DOMContentLoaded', checkNetworkStatus);
    </script>
</body>
</html>
