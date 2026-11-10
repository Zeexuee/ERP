@extends('layouts.app', ['title' => 'Sales Orders'])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 apple-glass-card rounded-2xl p-6">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                <span>Modul Sales Orders</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Sales Orders</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Kanvas kosong siap untuk perancangan ulang tabel order, status pemrosesan, dan filter baru.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('sales-orders.create') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition">
                + Buat Sales Order
            </a>
        </div>
    </div>

    <!-- Blank Canvas Area -->
    <div class="apple-glass-panel rounded-3xl p-12 text-center border border-white/80 min-h-[400px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-4">
            <svg class="w-8 h-8 stroke-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Kanvas Sales Orders Siap Dirancang</h3>
        <p class="text-xs text-slate-500 max-w-md leading-relaxed">Tabel dan komponen lama telah dibersihkan. Anda dapat mulai membangun struktur data dan alur order penjualan yang baru.</p>
    </div>
</div>
@endsection
