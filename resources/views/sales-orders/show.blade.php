@extends('layouts.app', ['title' => 'Rincian Sales Order: ' . ($salesOrder->order_number ?? '')])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 apple-glass-card rounded-2xl p-6">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                <span>Modul Sales Orders</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $salesOrder->order_number ?? 'Rincian Sales Order' }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Kanvas detail rincian order, status produksi, dan tagihan siap dirancang ulang.</p>
        </div>
        <div>
            <a href="{{ route('sales-orders.index') }}" class="px-4 py-2 rounded-xl bg-white text-slate-700 hover:text-slate-900 border border-slate-200 text-xs font-bold transition">
                ← Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Blank View Canvas Area -->
    <div class="apple-glass-panel rounded-3xl p-12 text-center border border-white/80 min-h-[400px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-4">
            <svg class="w-8 h-8 stroke-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Kanvas Rincian Sales Order</h3>
        <p class="text-xs text-slate-500 max-w-md leading-relaxed">Rincian lama telah dibersihkan. Siap untuk penataan antarmuka detail pesanan, barang, dokumen terkait, dan status alur baru.</p>
    </div>
</div>
@endsection
