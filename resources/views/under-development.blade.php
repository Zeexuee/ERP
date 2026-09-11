@extends('layouts.app', ['title' => $division_label ?? 'Tahap Pengembangan'])

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    
    <div class="apple-glass-panel rounded-3xl p-8 sm:p-12 shadow-2xl relative overflow-hidden border border-white/80">
        
        <!-- Ambient subtle glow behind card -->
        <div class="absolute -top-24 -right-24 w-72 h-72 bg-slate-300/40 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-slate-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 text-center">
            
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 text-white text-[11px] font-bold uppercase tracking-wider mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                <span>Tahap Pengembangan</span>
            </div>

            <!-- Icon -->
            <div class="mx-auto w-20 h-20 rounded-3xl bg-slate-100 border border-slate-200/80 flex items-center justify-center text-slate-800 mb-6 shadow-inner">
                <svg class="w-10 h-10 stroke-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>

            <!-- Header & Title -->
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight mb-3">
                {{ $division_label ?? 'Modul Sistem' }}
            </h1>

            <p class="text-sm sm:text-base text-slate-600 max-w-lg mx-auto leading-relaxed mb-6 font-medium">
                {{ $description ?? 'Akses untuk modul ini sedang ditutup sementara karena masih dalam proses pengembangan dan standarisasi sistem.' }}
            </p>

            <!-- Corporate Notice Box -->
            <div class="p-4 rounded-2xl bg-white/70 border border-slate-200 text-left max-w-lg mx-auto mb-8 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="w-5 h-5 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center shrink-0 mt-0.5 text-xs font-bold">
                        i
                    </div>
                    <div class="text-xs text-slate-700 space-y-1">
                        <p class="font-bold text-slate-900">Pemberitahuan Sistem ERP</p>
                        <p class="leading-relaxed">
                            Akses ke seluruh fitur dan pengolahan data pada divisi ini dinonaktifkan sementara oleh Administrator. Silakan gunakan modul operasional yang telah aktif (<strong>Sales</strong> atau <strong>Produksi</strong>).
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navigation Actions -->
            <div class="flex flex-wrap items-center justify-center gap-3">
                @php
                    $u = auth()->user();
                @endphp

                @if($u && $u->canAccessModule('sales'))
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition">
                        Buka Modul Sales
                    </a>
                @endif

                @if($u && $u->canAccessModule('production'))
                    <a href="{{ route('production.dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition">
                        Buka Modul Produksi
                    </a>
                @endif

                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold transition shadow-sm">
                        Keluar (Logout)
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
