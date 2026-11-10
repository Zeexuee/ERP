@extends('layouts.app', ['title' => 'Faktur Tagihan'])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 apple-glass-card rounded-2xl p-6">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                <span>Modul Faktur & Keuangan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Faktur Tagihan</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Kanvas kosong siap untuk perancangan ulang tabel faktur tagihan dan status pelunasan baru.</p>
        </div>
    </div>

    <!-- Blank Canvas Area -->
    <div class="apple-glass-panel rounded-3xl p-12 text-center border border-white/80 min-h-[400px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-4">
            <svg class="w-8 h-8 stroke-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Kanvas Faktur Tagihan Siap Dirancang</h3>
        <p class="text-xs text-slate-500 max-w-md leading-relaxed">Tabel dan komponen lama telah dibersihkan. Anda dapat mulai membangun struktur data penagihan dan rekonsiliasi kas yang baru.</p>
    </div>
</div>
@endsection
