@extends('layouts.app', ['title' => 'Edit Pelanggan'])

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 apple-glass-card rounded-2xl p-6">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider mb-2">
                <span>Modul Pelanggan</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Edit Pelanggan: {{ $customer->name ?? '' }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">Kanvas formulir edit pelanggan siap dirancang ulang.</p>
        </div>
        <div>
            <a href="{{ route('customers.index') }}" class="px-4 py-2 rounded-xl bg-white text-slate-700 hover:text-slate-900 border border-slate-200 text-xs font-bold transition">
                ← Kembali ke Daftar
            </a>
        </div>
    </div>

    <!-- Blank Form Canvas Area -->
    <div class="apple-glass-panel rounded-3xl p-12 text-center border border-white/80 min-h-[400px] flex flex-col items-center justify-center">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 mb-4">
            <svg class="w-8 h-8 stroke-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold text-slate-800 mb-1">Kanvas Form Edit Pelanggan</h3>
        <p class="text-xs text-slate-500 max-w-md leading-relaxed">Formulir lama telah dibersihkan. Siap untuk penataan elemen pembaruan data pelanggan baru.</p>
    </div>
</div>
@endsection
