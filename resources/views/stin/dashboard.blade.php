@extends('layouts.app', ['title' => 'STIN Dashboard'])

@section('content')
<div class="space-y-6">

    <!-- Header Section (Iconless) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black tracking-tight text-slate-900">STIN</h2>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Logistik Aktif
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Dashboard monitoring inventaris gudang, pemantauan mutasi stok real-time, dan status operasional.
            </p>
        </div>

        <!-- Quick Action Shortcuts (Iconless) -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('stin.mutations.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                Riwayat Mutasi
            </a>
            <a href="{{ route('stin.scanner.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold shadow-xs transition">
                Scan Barcode
            </a>
            <a href="{{ route('stin.loans.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold shadow-xs transition">
                Barang Dipinjam
            </a>
            <a href="{{ route('stin.items.create') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold shadow-xs transition">
                Input Data Item
            </a>
            <a href="{{ route('stin.items.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold shadow-xs transition">
                Data Gudang
            </a>
        </div>
    </div>

    <!-- Top KPI Metrics (Iconless) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- Card 1: Pelanggan -->
        <div class="p-5 rounded-2xl apple-glass-card hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pelanggan</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Mitra</span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">
                {{ number_format($stats['total_customers'] ?? 0) }}
            </div>
            <span class="text-[11px] font-medium text-slate-500 block mt-0.5">Klien & Mitra Aktif</span>
        </div>

        <!-- Card 2: Pesanan -->
        <div class="p-5 rounded-2xl apple-glass-card hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Pesanan</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Total</span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">
                {{ number_format($stats['total_sales_orders'] ?? 0) }}
            </div>
            <span class="text-[11px] font-medium text-slate-500 block mt-0.5">Total Pesanan Terdata</span>
        </div>

        <!-- Card 3: Total SKU Terdaftar -->
        <div class="p-5 rounded-2xl apple-glass-card hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Item Terdaftar</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">SKU</span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">
                {{ number_format($stats['total_items'] ?? 0) }}
            </div>
            <span class="text-[11px] font-medium text-slate-500 block mt-0.5">Varian SKU Gudang</span>
        </div>

        <!-- Card 4: Total Stok Fisik -->
        <div class="p-5 rounded-2xl apple-glass-card hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Stok Fisik</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-600">Unit</span>
            </div>
            <div class="text-2xl font-black text-slate-900 mt-2">
                {{ number_format($stats['total_stock_units'] ?? 0) }}
            </div>
            <span class="text-[11px] font-medium text-slate-500 block mt-0.5">Akumulasi Unit Fisik</span>
        </div>

        <!-- Card 5: Nilai Estimasi Inventaris -->
        <div class="p-5 rounded-2xl apple-glass-card hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Nilai Aset Stok</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800">HPP</span>
            </div>
            <div class="text-xl font-black text-slate-900 mt-2 truncate" title="Rp {{ number_format($stats['total_inventory_value'] ?? 0, 0, ',', '.') }}">
                Rp {{ number_format($stats['total_inventory_value'] ?? 0, 0, ',', '.') }}
            </div>
            <span class="text-[11px] font-medium text-slate-500 block mt-0.5">Valuasi HPP Keseluruhan</span>
        </div>
    </div>

    <!-- Alert / Mini Stat Bar (Iconless) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Status Stok Menipis -->
        <div class="p-4 rounded-2xl apple-glass-card flex items-center justify-between">
            <div>
                <h4 class="text-xs font-bold text-slate-900">Peringatan Batas Minimum</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ ($stats['low_stock_count'] ?? 0) > 0 ? ($stats['low_stock_count'] . ' item di bawah batas min') : 'Semua item dalam batas aman' }}
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ ($stats['low_stock_count'] ?? 0) > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $stats['low_stock_count'] ?? 0 }} Item
            </span>
        </div>

        <!-- Status Barang Dipinjam -->
        <a href="{{ route('stin.loans.index') }}" class="p-4 rounded-2xl apple-glass-card flex items-center justify-between hover:shadow-xs transition group">
            <div>
                <h4 class="text-xs font-bold text-slate-900 group-hover:underline">Barang Dipinjam</h4>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    {{ ($stats['active_loans_count'] ?? 0) > 0 ? ($stats['active_loans_count'] . ' transaksi aktif di luar') : 'Tidak ada barang di luar' }}
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold {{ ($stats['loaned_items_count'] ?? 0) > 0 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800' }}">
                {{ $stats['loaned_items_count'] ?? 0 }} Unit
            </span>
        </a>

        <!-- Aktivitas Mutasi Hari Ini -->
        <a href="{{ route('stin.mutations.index') }}" class="p-4 rounded-2xl apple-glass-card hover:shadow-md transition flex items-center justify-between group">
            <div>
                <div class="flex items-center gap-1.5">
                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition">Mutasi Hari Ini</h4>
                    <span class="text-[10px] text-slate-400">&rarr;</span>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-slate-600 mt-0.5">
                    <span class="text-emerald-700 font-semibold">+{{ number_format($stats['mutations_in_today'] ?? 0) }} Masuk</span>
                    <span>•</span>
                    <span class="text-rose-700 font-semibold">-{{ number_format($stats['mutations_out_today'] ?? 0) }} Keluar</span>
                </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-sky-100 text-sky-800">
                {{ $stats['mutations_today'] ?? 0 }} Transaksi
            </span>
        </a>

        <!-- Mutasi Bulan Ini -->
        <a href="{{ route('stin.mutations.index', ['month' => now()->format('Y-m')]) }}" class="p-4 rounded-2xl apple-glass-card hover:shadow-md transition flex items-center justify-between group">
            <div>
                <div class="flex items-center gap-1.5">
                    <h4 class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition">Total Mutasi Bulan Ini</h4>
                    <span class="text-[10px] text-slate-400">&rarr;</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Akumulasi transaksi keluar & masuk
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-extrabold bg-slate-900 text-white">
                {{ $stats['mutations_this_month'] ?? 0 }} Aktivitas
            </span>
        </a>
    </div>

    <!-- Analytics Row: Tren Mutasi 7 Hari & Distribusi Kategori (Iconless) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 7-Day Movements Activity Chart (2 cols) -->
        <div class="lg:col-span-2 apple-glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Aktivitas Aliran Stok 7 Hari Terakhir</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Volume barang masuk (Stock In) vs barang keluar (Stock Out)</p>
                </div>
                <div class="flex items-center gap-3 text-xs font-semibold">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-slate-600">Masuk</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-slate-600">Keluar</span>
                    </div>
                </div>
            </div>

            @php
                $maxVal = 1;
                foreach ($weeklyTrend as $day) {
                    if ($day['in_qty'] > $maxVal) $maxVal = $day['in_qty'];
                    if ($day['out_qty'] > $maxVal) $maxVal = $day['out_qty'];
                }
            @endphp

            <div class="h-56 flex items-end justify-between gap-3 pt-6 pb-2 px-2">
                @foreach ($weeklyTrend as $day)
                    @php
                        $inHeightPct = $maxVal > 0 ? max(6, round(($day['in_qty'] / $maxVal) * 100)) : 6;
                        $outHeightPct = $maxVal > 0 ? max(6, round(($day['out_qty'] / $maxVal) * 100)) : 6;
                        $hasIn = $day['in_qty'] > 0;
                        $hasOut = $day['out_qty'] > 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center h-full justify-end group">
                        <div class="w-full flex items-end justify-center gap-1.5 h-44 mb-2">
                            <!-- Bar In -->
                            <div class="relative w-1/2 max-w-[28px] flex flex-col items-center justify-end h-full">
                                <div class="text-[10px] font-bold text-slate-600 mb-1 opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                    +{{ $day['in_qty'] }}
                                </div>
                                <div style="height: {{ $hasIn ? $inHeightPct : 4 }}%;" class="w-full rounded-t-lg {{ $hasIn ? 'bg-emerald-500' : 'bg-slate-200/60' }} transition-all duration-300"></div>
                            </div>
                            <!-- Bar Out -->
                            <div class="relative w-1/2 max-w-[28px] flex flex-col items-center justify-end h-full">
                                <div class="text-[10px] font-bold text-slate-600 mb-1 opacity-0 group-hover:opacity-100 transition whitespace-nowrap">
                                    -{{ $day['out_qty'] }}
                                </div>
                                <div style="height: {{ $hasOut ? $outHeightPct : 4 }}%;" class="w-full rounded-t-lg {{ $hasOut ? 'bg-rose-500' : 'bg-slate-200/60' }} transition-all duration-300"></div>
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-slate-700">{{ $day['day_name'] }}</span>
                        <span class="text-[10px] text-slate-400 font-medium">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Category Distribution (1 col) -->
        <div class="apple-glass-card rounded-2xl p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
                    <h3 class="text-base font-bold text-slate-900">Distribusi Kategori</h3>
                    <a href="{{ route('stin.items.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">Kelola →</a>
                </div>

                <div class="space-y-4">
                    @forelse($categoriesDistribution as $cat)
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold mb-1.5">
                                <span class="text-slate-900 truncate max-w-[140px]">{{ $cat['name'] }}</span>
                                <span class="text-slate-600 font-mono text-[11px]">
                                    {{ number_format($cat['stock_count']) }} unit ({{ $cat['percentage'] }}%)
                                </span>
                            </div>
                            <div class="w-full bg-slate-200/80 rounded-full h-2 overflow-hidden">
                                <div class="bg-slate-900 h-2 rounded-full transition-all duration-500" style="width: {{ $cat['percentage'] }}%"></div>
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium mt-1">
                                {{ $cat['item_count'] }} SKU terdaftar
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-xs text-slate-400 font-medium">
                            Belum ada kategori item terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-900/10 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Total Akumulasi Fisik</span>
                <span class="font-extrabold text-slate-900">{{ number_format($stats['total_stock_units'] ?? 0) }} Unit</span>
            </div>
        </div>
    </div>

    <!-- Riwayat Mutasi Stok Terkini (Iconless) -->
    <div class="apple-glass-card rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-4 border-b border-slate-900/10 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Riwayat Mutasi Stok Terkini</h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Catatan transaksi keluar-masuk barang yang tercatat di sistem STIN.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('stin.mutations.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    Lihat Seluruh Mutasi →
                </a>
                <a href="{{ route('stin.mutations.export') }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs font-bold shadow-xs transition" title="Unduh seluruh riwayat mutasi gudang ke Excel">
                    Export Excel
                </a>
                <a href="{{ route('stin.scanner.index') }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 border border-slate-300 text-slate-800 text-xs font-bold shadow-xs transition">
                    Scan Barcode
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Waktu</th>
                        <th class="px-4 py-3">Item & SKU</th>
                        <th class="px-4 py-3 text-center">Jenis Mutasi</th>
                        <th class="px-4 py-3 text-right">Perubahan Qty</th>
                        <th class="px-4 py-3 text-right">Stok Akhir</th>
                        <th class="px-4 py-3">Petugas (PIC)</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($recentMovements as $mov)
                        @php
                            $isEntry = $mov->type === 'in';
                            $isOut = $mov->type === 'out';
                            $badgeClass = match ($mov->type) {
                                'in' => 'bg-emerald-100 text-emerald-800',
                                'out' => 'bg-rose-100 text-rose-800',
                                'adjustment' => 'bg-amber-100 text-amber-800',
                                default => 'bg-slate-100 text-slate-800'
                            };
                            $typeLabel = match ($mov->type) {
                                'in' => 'Stok Masuk',
                                'out' => 'Stok Keluar',
                                'adjustment' => 'Penyesuaian',
                                'transfer' => 'Transfer',
                                default => ucfirst($mov->type)
                            };
                        @endphp
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                {{ ($mov->movement_date ?? $mov->created_at)?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($mov->item)
                                    <div class="font-bold text-slate-900">{{ $mov->item->name }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">{{ $mov->item->sku }}</div>
                                @else
                                    <span class="text-slate-400 italic">Item tidak tersedia</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badgeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono font-extrabold text-right {{ $isEntry ? 'text-emerald-700' : ($isOut ? 'text-rose-700' : 'text-slate-900') }}">
                                {{ $isEntry ? '+' : ($isOut ? '-' : '') }}{{ number_format($mov->quantity) }}
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 text-right">
                                {{ number_format($mov->stock_after) }}
                            </td>
                            <td class="px-4 py-3 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                {{ $mov->pic_name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 max-w-xs truncate">
                                {{ $mov->notes ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('stin.mutations.index', ['search' => $mov->item?->sku ?: $mov->notes]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400 font-medium">
                                Belum ada riwayat mutasi stok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Grid Row: Khusus Manajemen Inventaris Gudang STIN (Iconless) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Card 1: Pantauan Stok Minimum & Restock Item -->
        <div class="apple-glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Pantauan Stok Minimum</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Item prioritas yang perlu penambahan stok fisik.</p>
                </div>
                <a href="{{ route('stin.items.create') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    Tambah Stok
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Nama Item</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-center">Sisa Stok</th>
                            <th class="px-3 py-2.5 text-center">Batas Min</th>
                            <th class="px-3 py-2.5 rounded-r-lg text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10">
                        @php
                            $displayItems = $criticalStockItems->isNotEmpty() ? $criticalStockItems : $lowestStockItems;
                        @endphp
                        @forelse($displayItems as $item)
                            <tr class="hover:bg-white/60 transition">
                                <td class="px-3 py-3">
                                    <div class="font-bold text-slate-900">{{ $item->name }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">{{ $item->sku }}</div>
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-700">
                                    {{ $item->category ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-md font-mono text-xs font-extrabold {{ $item->current_stock <= $item->min_stock ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-800' }}">
                                        {{ number_format($item->current_stock) }} {{ $item->unit ?? 'pcs' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-center font-mono text-xs text-slate-600">
                                    {{ number_format($item->min_stock) }}
                                </td>
                                <td class="px-3 py-3 text-right">
                                    <a href="{{ route('stin.items.mutations', $item) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold transition">
                                        Mutasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-slate-400 font-medium">
                                    Semua item memiliki stok mencukupi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card 2: Item Stok Terbanyak di Gudang -->
        <div class="apple-glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Item Stok Terbanyak</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Daftar item dengan kuantitas unit fisik terbesar di gudang.</p>
                </div>
                <a href="{{ route('stin.items.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                    Katalog Lengkap →
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                        <tr>
                            <th class="px-3 py-2.5 rounded-l-lg">Nama Item</th>
                            <th class="px-3 py-2.5">Kategori</th>
                            <th class="px-3 py-2.5 text-right">Jumlah Stok</th>
                            <th class="px-3 py-2.5 rounded-r-lg text-right">Nilai Aset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10">
                        @forelse($topStockItems as $topItem)
                            <tr class="hover:bg-white/60 transition">
                                <td class="px-3 py-3">
                                    <a href="{{ route('stin.items.mutations', $topItem) }}" class="font-bold text-slate-900 hover:underline">
                                        {{ $topItem->name }}
                                    </a>
                                    <div class="font-mono text-[11px] text-slate-500">{{ $topItem->sku }}</div>
                                </td>
                                <td class="px-3 py-3 text-xs text-slate-700">
                                    {{ $topItem->category ?? '-' }}
                                </td>
                                <td class="px-3 py-3 font-mono font-extrabold text-xs text-slate-900 text-right">
                                    {{ number_format($topItem->current_stock) }} {{ $topItem->unit ?? 'pcs' }}
                                </td>
                                <td class="px-3 py-3 font-mono font-bold text-xs text-emerald-700 text-right">
                                    Rp {{ number_format($topItem->current_stock * $topItem->unit_price, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-slate-400 font-medium">
                                    Belum ada data barang di gudang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pantauan Barang Dipinjam Terkini -->
    <div class="apple-glass-card rounded-2xl p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 mb-4 border-b border-slate-900/10 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Pantauan Barang Dipinjam Terkini</h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Sirkulasi peminjaman barang keluar sementara yang sedang aktif.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('stin.scanner.index', ['mode' => 'loan']) }}" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    + Pinjam Baru
                </a>
                <a href="{{ route('stin.loans.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">
                    Katalog Pinjaman Lengkap →
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">No. Pinjaman</th>
                        <th class="px-4 py-3">Peminjam / PIC</th>
                        <th class="px-4 py-3">Keperluan / Lokasi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Dipinjam</th>
                        <th class="px-4 py-3 text-right">Sisa di Luar</th>
                        <th class="px-4 py-3">Batas Waktu</th>
                        <th class="px-4 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($recentLoans as $rLoan)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 text-xs">
                                <a href="{{ route('stin.loans.show', $rLoan) }}" class="hover:underline">
                                    {{ $rLoan->loan_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 font-semibold text-slate-900 text-xs">
                                {{ $rLoan->borrower_name }}
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-700">
                                {{ $rLoan->purpose }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $rLoan->status->badgeClass() }}">
                                    {{ $rLoan->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono font-bold text-slate-900 text-right text-xs">
                                {{ $rLoan->totalQuantityLoaned() }} unit
                            </td>
                            <td class="px-4 py-3 font-mono font-extrabold text-right text-xs {{ $rLoan->totalQuantityRemaining() > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ $rLoan->totalQuantityRemaining() }} unit
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">
                                {{ $rLoan->expected_return_date?->translatedFormat('d M Y') ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right text-xs whitespace-nowrap">
                                @if($rLoan->status !== \App\Enums\WarehouseLoanStatus::RETURNED)
                                    <a href="{{ route('stin.loans.return', $rLoan) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold transition mr-1">
                                        Kembalikan
                                    </a>
                                @endif
                                <a href="{{ route('stin.loans.show', $rLoan) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 font-bold transition">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-400 font-medium">
                                Belum ada catatan peminjaman barang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
