@extends('layouts.app', ['title' => 'Produksi'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900">Produksi</h2>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Antrean Menunggu</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalPending }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Sedang Dikerjakan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalInProduction }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Produksi Selesai</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalFinished }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Bahan Gudang</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalMaterials }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Stok Menipis</span>
            <div class="text-2xl font-bold {{ $lowStockMaterialsCount > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                {{ $lowStockMaterialsCount }}
            </div>
        </div>
    </div>

    <!-- Antrean Kerja -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Antrean Produksi</h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                {{ $requests->total() }} Total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">No. Permintaan</th>
                        <th class="py-2.5 px-3">Ref. Order</th>
                        <th class="py-2.5 px-3">Pelanggan</th>
                        <th class="py-2.5 px-3">Barang & Jumlah</th>
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($requests as $item)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">
                                #{{ $item->request_number }}
                            </td>
                            <td class="py-3 px-3">
                                @if($item->salesOrder)
                                    <a href="{{ route('sales-orders.show', $item->salesOrder) }}" class="font-mono font-semibold text-slate-700 hover:underline">
                                        {{ $item->salesOrder->order_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                {{ $item->salesOrder->customer->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <ul class="space-y-0.5">
                                    @foreach($item->salesOrder->items ?? [] as $soItem)
                                        <li class="text-slate-700">
                                            {{ $soItem->product->name ?? 'Produk' }}
                                            <span class="font-semibold text-slate-900">({{ $soItem->quantity }} {{ $soItem->unit ?? 'unit' }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $item->requested_date ? \Illuminate\Support\Carbon::parse($item->requested_date)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($item->status->value === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-700">
                                        Menunggu
                                    </span>
                                @elseif($item->status->value === 'in_production')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white">
                                        Dikerjakan
                                    </span>
                                @elseif($item->status->value === 'finished')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border border-slate-900 text-slate-900">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">
                                Belum ada antrean.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-900/10">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Stok Produk -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Stok Produk</h3>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-700 hover:underline">
                Katalog
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($products as $prod)
                <div class="p-4 rounded-2xl bg-white/70 border border-slate-900/10 space-y-1.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono text-[10px] text-slate-400 uppercase">{{ $prod->sku }}</span>
                            <h4 class="font-bold text-sm text-slate-900">{{ $prod->name }}</h4>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-900 text-white">
                            {{ $prod->stock_quantity }} {{ $prod->unit ?? 'unit' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-900/5 text-[11px] text-slate-600">
                        @if($prod->branches->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($prod->branches as $b)
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 font-mono text-[9px] font-semibold border border-slate-200">
                                        {{ $b->branch_code }}: {{ $b->stock_quantity }} {{ $prod->unit ?? 'unit' }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Informasi Barang Gudang (Bahan Baku) -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-3">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-sm font-bold text-slate-900">Informasi Barang Gudang (Bahan Baku)</h3>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                        {{ $totalMaterials }} Jenis Bahan
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Monitoring stok bahan baku pabrik dan mutasi alur barang gudang terkini.</p>
            </div>
            <div class="flex items-center gap-2">
                @if($lowStockMaterialsCount > 0)
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                        {{ $lowStockMaterialsCount }} Bahan Menipis
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Stok Bahan Aman
                    </span>
                @endif
                <a href="{{ route('production.materials.index') }}" class="text-xs font-semibold text-slate-700 hover:underline">
                    Lihat Gudang →
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Tabel Bahan Baku Teratas & Kritis -->
            <div class="lg:col-span-2 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Status Stok Bahan Gudang</span>
                    <span class="text-[11px] text-slate-500">Prioritas bahan menipis ditampilkan di atas</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Kode & Bahan</th>
                                <th class="py-2.5 px-3">Kategori</th>
                                <th class="py-2.5 px-3 text-right">Stok Gudang</th>
                                <th class="py-2.5 px-3 text-right">Batas Min.</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/5">
                            @forelse($warehouseMaterials as $mat)
                                <tr class="hover:bg-white/40 transition">
                                    <td class="py-2.5 px-3">
                                        <span class="font-mono text-[10px] text-slate-500 block">{{ $mat->code }}</span>
                                        <span class="font-bold text-slate-900">{{ $mat->name }}</span>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ $mat->category }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($mat->stock_quantity, 1) }} <span class="font-sans text-[10px] text-slate-500 font-normal">{{ $mat->unit }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600 text-[11px]">
                                        {{ number_format($mat->minimum_stock, 1) }} {{ $mat->unit }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @if($mat->stock_quantity <= 0)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                                Habis
                                            </span>
                                        @elseif($mat->isLowStock())
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                Menipis
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                Aman
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-slate-400">
                                        Belum ada data barang gudang.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mutasi Terkini Barang Gudang -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Mutasi Terkini Gudang</span>
                    <a href="{{ route('production.materials.index') }}" class="text-[11px] font-semibold text-slate-600 hover:underline">Semua Log</a>
                </div>

                <div class="divide-y divide-slate-900/5 rounded-2xl bg-white/60 border border-slate-900/10 p-3">
                    @forelse($recentMaterialLogs as $log)
                        <div class="py-2.5 first:pt-1 last:pb-1">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        @if($log->type === 'in')
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">MASUK</span>
                                        @else
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-200 text-slate-800">KELUAR</span>
                                        @endif
                                        <span class="font-bold text-xs text-slate-900">{{ $log->material?->name ?? 'Bahan' }}</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-0.5">
                                        {{ $log->source_or_destination ?? ($log->reference_number ? 'Ref: ' . $log->reference_number : '-') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="font-mono text-xs font-bold {{ $log->type === 'in' ? 'text-emerald-700' : 'text-slate-900' }}">
                                        {{ $log->type === 'in' ? '+' : '-' }}{{ number_format($log->quantity, 1) }} {{ $log->unit }}
                                    </span>
                                    <span class="block text-[9px] text-slate-400 font-mono">
                                        {{ $log->movement_date ? $log->movement_date->format('d/m H:i') : $log->created_at->format('d/m H:i') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            Belum ada aktivitas mutasi barang gudang.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
