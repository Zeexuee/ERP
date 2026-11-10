@extends('layouts.app', ['title' => 'Detail Faktur: ' . ($invoice->invoice_number ?? '')])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 apple-glass-card rounded-2xl p-6">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                <span>Modul Faktur & Keuangan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $invoice->invoice_number ?? 'Detail Faktur' }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Kanvas rincian faktur, pencatatan pembayaran, dan riwayat pelunasan siap dirancang ulang.</p>
        </div>
        <div>
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 rounded-xl bg-white text-slate-700 hover:text-slate-900 border border-slate-200 text-xs font-bold transition">
                ← Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Blank View Canvas Area -->
    <div class="apple-glass-panel rounded-3xl p-12 text-center border border-white/80 min-h-[400px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-4">
            <svg class="w-8 h-8 stroke-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Kanvas Detail Faktur Tagihan</h3>
        <p class="text-xs text-slate-500 max-w-md leading-relaxed">Rincian faktur dan pencatatan pembayaran lama telah dibersihkan. Siap untuk penataan antarmuka penagihan dan bukti transfer baru.</p>
    </div>
</div>
@endsection
