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
        html {
            zoom: 0.9;
        }

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
            transition: width 650ms cubic-bezier(0.16, 1, 0.3, 1);
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
            transition: opacity 350ms cubic-bezier(0.16, 1, 0.3, 1) 220ms, max-width 450ms cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
            pointer-events: auto;
        }

        .is-collapsed .brand-text-label {
            opacity: 0 !important;
            max-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            pointer-events: none !important;
            transition: opacity 60ms linear, max-width 300ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        .is-collapsed .nav-text-label {
            opacity: 0 !important;
            max-width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            pointer-events: none !important;
            transition: opacity 60ms linear, max-width 300ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        .nav-section-title {
            opacity: 1;
            max-height: 2.5rem;
            margin-top: 0.75rem;
            margin-bottom: 0.25rem;
            transition: opacity 350ms ease 150ms, max-height 450ms cubic-bezier(0.16, 1, 0.3, 1), margin 450ms cubic-bezier(0.16, 1, 0.3, 1);
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
            transition: opacity 60ms linear, max-height 250ms cubic-bezier(0.16, 1, 0.3, 1), margin 250ms cubic-bezier(0.16, 1, 0.3, 1);
        }

        .brand-header-container {
            transition: padding 600ms cubic-bezier(0.16, 1, 0.3, 1);
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
    <aside id="floatingSidebar" class="w-64 my-4 ml-4 apple-glass-panel rounded-3xl border border-white flex flex-col justify-between hidden md:flex relative z-50 shadow-xl overflow-visible">
        <div>
            <!-- Brand Header & Toggle Icon -->
            <div class="brand-header-container h-16 flex items-center justify-between px-3 border-b border-slate-900/10 w-full">
                <div class="brand-text-label">
                    <span class="font-bold text-xs tracking-wider text-slate-500 uppercase">Beta v.0.0.1</span>
                </div>
                <button id="toggleSidebarBtn" type="button" class="w-10 h-10 shrink-0 flex items-center justify-center rounded-xl text-slate-600 hover:text-slate-900 hover:bg-white/60 transition" title="Sidebar">
                    <svg id="toggleIcon" class="w-5 h-5 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Rail Items with Fixed Icons & Delayed Text -->
            <nav class="px-2 py-4 space-y-1">
                @php
                    $currentUser = auth()->user();
                    $userRole = $currentUser?->role?->value;
                    $currentDivision = match (true) {
                        request()->routeIs('production.*') => 'production',
                        request()->routeIs('stin.*') => 'stin',
                        request()->routeIs('crm.*') => 'crm',
                        request()->routeIs('ecommerce.*') => 'ecommerce',
                        request()->routeIs('dashboard') || request()->routeIs('customers.*') || request()->routeIs('sales-orders.*') || request()->routeIs('invoices.*') => 'sales',
                        default => match ($userRole) {
                            'production' => 'production',
                            'stin' => 'stin',
                            'admin_crm' => 'crm',
                            'admin_ecommerce' => 'ecommerce',
                            default => 'sales',
                        },
                    };
                    $currentDivisionLabel = match ($currentDivision) {
                        'production' => 'PRODUKSI',
                        'stin' => 'STIN',
                        'crm' => 'CRM',
                        'ecommerce' => 'E-COMMERCE',
                        default => 'SALES',
                    };
                @endphp

                @if($currentDivision === 'sales')
                    <!-- Sales Navigation -->
                    <a href="{{ route('dashboard') }}" title="Dashboard" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Dashboard</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Penjualan</div>

                    <a href="{{ route('customers.index') }}" title="Pelanggan" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('customers.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Pelanggan</span>
                    </a>

                    <a href="{{ route('sales-orders.index') }}" title="Sales Orders" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('sales-orders.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Sales Orders</span>
                    </a>

                    <a href="{{ route('invoices.index') }}" title="Faktur" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('invoices.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Faktur</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Katalog</div>

                    <a href="{{ route('products.index') }}" title="Produk" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Produk</span>
                    </a>

                    <a href="{{ route('production-requests.index') }}" title="Antrean" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production-requests.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.594 15.12a2 2 0 00-1.022.547l-.29.29a2 2 0 00.586 3.414l2.12.707a6 6 0 003.8.024l.36-.12a6 6 0 013.8.024l2.12.707a2 2 0 002.535-1.927l-.044-.41a2 2 0 00-.547-1.022l-.29-.29z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Antrean</span>
                    </a>

                @elseif($currentDivision === 'production')
                    <!-- Produksi Navigation -->
                    <a href="{{ route('production.dashboard') }}" title="Dashboard Produksi" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production.dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Dashboard</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Gudang Bahan</div>

                    <a href="{{ route('production.materials.index') }}" title="Barang Gudang" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production.materials.index') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Barang Gudang</span>
                    </a>

                    <a href="{{ route('production.materials.create-receipt') }}" title="Input Barang Masuk" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production.materials.create-receipt') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Barang Masuk</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Manufaktur</div>

                    <a href="{{ route('production.batches.index') }}" title="Proses Produksi" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production.batches.index') || request()->routeIs('production.batches.show') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.594 15.12a2 2 0 00-1.022.547l-.29.29a2 2 0 00.586 3.414l2.12.707a6 6 0 003.8.024l.36-.12a6 6 0 013.8.024l2.12.707a2 2 0 002.535-1.927l-.044-.41a2 2 0 00-.547-1.022l-.29-.29z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Proses Produksi</span>
                    </a>

                    <a href="{{ route('production.batches.create') }}" title="Buat Produksi" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production.batches.create') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Buat Produksi</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Katalog & Antrean</div>

                    <a href="{{ route('products.index') }}" title="Produk Jadi" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Produk Jadi</span>
                    </a>

                    <a href="{{ route('production-requests.index') }}" title="Antrean Sales" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('production-requests.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Antrean Sales</span>
                    </a>

                @elseif($currentDivision === 'stin')
                    <!-- STIN Navigation -->
                    <a href="{{ route('stin.dashboard') }}" title="STIN" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('stin.dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Dashboard</span>
                    </a>

                @elseif($currentDivision === 'crm')
                    <!-- CRM Navigation -->
                    <a href="{{ route('crm.dashboard') }}" title="CRM" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('crm.dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Dashboard</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Klien</div>

                    <a href="{{ route('customers.index') }}" title="Pelanggan" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('customers.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Pelanggan</span>
                    </a>

                @elseif($currentDivision === 'ecommerce')
                    <!-- E-Commerce Navigation -->
                    <a href="{{ route('ecommerce.dashboard') }}" title="E-Commerce" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('ecommerce.dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Dashboard</span>
                    </a>

                    <div class="nav-section-title px-3 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Katalog</div>

                    <a href="{{ route('products.index') }}" title="Produk" class="flex items-center gap-2 px-2 py-1 text-sm font-bold rounded-xl transition-all {{ request()->routeIs('products.*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-700 hover:text-slate-900 hover:bg-white/50' }}">
                        <div class="nav-icon-box">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="nav-text-label">Produk</span>
                    </a>
                @endif
            </nav>
        </div>

        <!-- Bottom Sidebar: Icon Ekosistem Modular (Simple Icon Switcher) -->
        <div class="p-2 border-t border-slate-900/10 relative z-50">
            <button 
                id="appSwitcherToggleBtn" 
                type="button" 
                onclick="toggleAppSwitcher(event)"
                class="w-full flex items-center gap-2 p-1.5 rounded-2xl bg-white/90 hover:bg-white text-slate-800 border border-slate-200/80 transition shadow-sm"
                title="EK.DIV {{ $currentDivisionLabel }}"
            >
                <div class="nav-icon-box">
                    <svg class="w-5 h-5 text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="5" cy="5" r="2"/>
                        <circle cx="12" cy="5" r="2"/>
                        <circle cx="19" cy="5" r="2"/>
                        <circle cx="5" cy="12" r="2"/>
                        <circle cx="12" cy="12" r="2"/>
                        <circle cx="19" cy="12" r="2"/>
                        <circle cx="5" cy="19" r="2"/>
                        <circle cx="12" cy="19" r="2"/>
                        <circle cx="19" cy="19" r="2"/>
                    </svg>
                </div>
                <div class="nav-text-label text-left">
                    <span class="block text-xs font-bold text-slate-900 truncate">EK.DIV {{ $currentDivisionLabel }}</span>
                </div>
            </button>

            <!-- Dropdown Popover Modular Ekosistem -->
            <div 
                id="appSwitcherDropdown" 
                class="hidden absolute left-full ml-3 bottom-0 w-64 bg-white/95 backdrop-blur-2xl border border-slate-200/90 shadow-2xl rounded-3xl p-3 text-slate-900 pointer-events-auto"
                style="z-index: 99999; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);"
            >
                <div class="grid grid-cols-3 gap-2">
                    @php
                        $u = auth()->user();
                        $modules = [
                            [
                                'key' => 'sales',
                                'label' => 'Sales',
                                'fullName' => 'Sales',
                                'url' => route('dashboard'),
                                'active' => request()->routeIs('dashboard') || request()->routeIs('customers.*') || request()->routeIs('sales-orders.*') || request()->routeIs('invoices.*'),
                                'hasAccess' => $u ? $u->canAccessModule('sales') : false,
                                'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z',
                            ],
                            [
                                'key' => 'production',
                                'label' => 'Produksi',
                                'fullName' => 'Produksi',
                                'url' => route('production.dashboard'),
                                'active' => request()->routeIs('production.*'),
                                'hasAccess' => $u ? $u->canAccessModule('production') : false,
                                'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                            ],
                            [
                                'key' => 'stin',
                                'label' => 'STIN',
                                'fullName' => 'STIN',
                                'url' => route('stin.dashboard'),
                                'active' => request()->routeIs('stin.*'),
                                'hasAccess' => $u ? $u->canAccessModule('stin') : false,
                                'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                            ],
                            [
                                'key' => 'crm',
                                'label' => 'CRM',
                                'fullName' => 'CRM',
                                'url' => route('crm.dashboard'),
                                'active' => request()->routeIs('crm.*'),
                                'hasAccess' => $u ? $u->canAccessModule('crm') : false,
                                'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                            ],
                            [
                                'key' => 'ecommerce',
                                'label' => 'E-Com',
                                'fullName' => 'E-Commerce',
                                'url' => route('ecommerce.dashboard'),
                                'active' => request()->routeIs('ecommerce.*'),
                                'hasAccess' => $u ? $u->canAccessModule('ecommerce') : false,
                                'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
                            ],
                        ];
                    @endphp

                    @foreach($modules as $m)
                        <button 
                            type="button" 
                            onclick="handleModuleSwitch('{{ $m['key'] }}', '{{ $m['fullName'] }}', '{{ $m['url'] }}', {{ $m['hasAccess'] ? 'true' : 'false' }})"
                            class="h-16 flex flex-col items-center justify-center p-2 rounded-2xl transition {{ $m['active'] ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-50 hover:bg-white text-slate-800 border border-slate-200/80' }}"
                            title="{{ $m['fullName'] }}"
                        >
                            <svg class="w-5 h-5 mb-1 {{ $m['active'] ? 'text-white' : 'text-slate-800' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $m['icon'] }}"/>
                            </svg>
                            <span class="text-[10px] font-bold">{{ $m['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Floating Header Bar -->
        <header class="h-14 mt-4 ml-4 mr-4 apple-glass-panel rounded-2xl border border-white flex items-center justify-between px-6 shadow-md relative z-10">
            <div class="flex items-center gap-3">
                <h1 class="text-base font-extrabold text-slate-900 tracking-tight">
                    {{ $title ?? 'ERP Sales' }}
                </h1>
            </div>

            <div class="flex items-center gap-2">
                <span id="connectionStatusBadge" class="px-3 py-1 rounded-full text-[11px] font-bold badge-dark">
                    Online
                </span>
                <span class="px-3 py-1 rounded-full text-[11px] font-mono font-bold badge-dark">
                    IP: {{ request()->ip() }}
                </span>
                @auth
                    <!-- Logout Button -->
                    <form action="{{ route('logout') }}" method="POST" class="inline ml-1">
                        @csrf
                        <button type="submit" class="h-8 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold flex items-center gap-1.5 transition shadow-sm" title="Logout">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                @endauth
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

    <!-- Modal Otorisasi Akun -->
    <div id="switchAuthModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm hidden transition-opacity duration-200">
        <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-white rounded-3xl p-6 max-w-sm w-full shadow-2xl relative">
            <div class="flex items-center justify-between pb-3 border-b border-slate-900/10 mb-4">
                <h3 id="switchModalTitle" class="text-base font-bold text-slate-900">
                    Otorisasi Akun
                </h3>
                <button type="button" onclick="closeSwitchModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs font-bold transition">
                    ✕
                </button>
            </div>

            <div id="switchModalError" class="p-2.5 rounded-xl bg-slate-100 border border-slate-300 text-slate-800 text-xs font-medium mb-3 hidden"></div>

            <form id="switchAuthForm" onsubmit="submitQuickSwitch(event)" class="space-y-3">
                <input type="hidden" id="switch_target_module" name="target_module" value="">

                <div>
                    <label for="switch_email" class="block text-xs font-semibold text-slate-700 mb-1">
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="switch_email" 
                        required 
                        placeholder="email@erp.com" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-sm"
                    >
                </div>

                <div>
                    <label for="switch_password" class="block text-xs font-semibold text-slate-700 mb-1">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="switch_password" 
                        required 
                        placeholder="••••••••" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-sm"
                    >
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-900/10">
                    <button type="button" onclick="closeSwitchModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <button type="submit" id="switchSubmitBtn" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold">
                        <span id="switchBtnText">Masuk</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @stack('modals')

    <script>
        // App Switcher Dropdown Toggle Logic
        function toggleAppSwitcher(e) {
            if (e) e.stopPropagation();
            const dropdown = document.getElementById('appSwitcherDropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', (e) => {
            const dropdown = document.getElementById('appSwitcherDropdown');
            const btn = document.getElementById('appSwitcherToggleBtn');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        // Fast Role / Module Switching Handler
        function handleModuleSwitch(moduleKey, moduleName, moduleUrl, hasAccess) {
            const dropdown = document.getElementById('appSwitcherDropdown');
            if (dropdown) dropdown.classList.add('hidden');

            if (hasAccess) {
                // User has access -> direct instantaneous redirection
                window.location.href = moduleUrl;
            } else {
                // User does NOT have access -> open instantaneous authorization pop-up modal
                openSwitchModal(moduleKey, moduleName);
            }
        }

        function openSwitchModal(moduleKey, moduleName) {
            document.getElementById('switch_target_module').value = moduleKey;
            document.getElementById('switchModalTitle').textContent = 'Otorisasi ' + moduleName;
            
            // Suggest default demo email for convenience
            const demoEmails = {
                'sales': 'sales@erp.com',
                'production': 'produksi@erp.com',
                'stin': 'stin@erp.com',
                'crm': 'crm@erp.com',
                'ecommerce': 'ecommerce@erp.com'
            };

            const emailInput = document.getElementById('switch_email');
            const passInput = document.getElementById('switch_password');
            if (demoEmails[moduleKey]) {
                emailInput.value = demoEmails[moduleKey];
                passInput.value = 'password';
            } else {
                emailInput.value = '';
                passInput.value = '';
            }

            const errorBox = document.getElementById('switchModalError');
            errorBox.classList.add('hidden');
            errorBox.textContent = '';

            const modal = document.getElementById('switchAuthModal');
            modal.classList.remove('hidden');
            passInput.focus();
        }

        function closeSwitchModal() {
            const modal = document.getElementById('switchAuthModal');
            if (modal) modal.classList.add('hidden');
        }

        async function submitQuickSwitch(e) {
            e.preventDefault();
            const btn = document.getElementById('switchSubmitBtn');
            const btnText = document.getElementById('switchBtnText');
            const errorBox = document.getElementById('switchModalError');

            const email = document.getElementById('switch_email').value;
            const password = document.getElementById('switch_password').value;
            const targetModule = document.getElementById('switch_target_module').value;

            btn.disabled = true;
            btnText.textContent = 'Memverifikasi...';
            errorBox.classList.add('hidden');

            try {
                const response = await fetch('{{ route('quick-switch') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password,
                        target_module: targetModule
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    btnText.textContent = 'Berhasil...';
                    window.location.href = data.redirect_url;
                } else {
                    errorBox.textContent = data.message || 'Gagal otorisasi akun.';
                    errorBox.classList.remove('hidden');
                    btn.disabled = false;
                    btnText.textContent = 'Masuk';
                }
            } catch (err) {
                errorBox.textContent = 'Terjadi gangguan server.';
                errorBox.classList.remove('hidden');
                btn.disabled = false;
                btnText.textContent = 'Masuk';
            }
        }

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
