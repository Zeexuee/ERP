@extends('layouts.app', ['title' => 'STIN'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900">STIN</h2>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelanggan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ number_format($stats['total_customers'] ?? 0) }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pesanan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ number_format($stats['total_sales_orders'] ?? 0) }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Permintaan Produksi</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ number_format($stats['total_production_requests'] ?? 0) }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Produk</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ number_format($stats['total_products'] ?? 0) }}
            </div>
        </div>
    </div>

</div>
@endsection
