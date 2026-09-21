@extends('layouts.app', ['title' => 'Warehouse'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Barang Gudang
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Inventaris bahan baku, sortir kayu, dan penerimaan barang masuk pabrik Gaharu Sana'i.</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="openNewMaterialModal()" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
                + Tambah Jenis Bahan
            </button>
            <a href="{{ route('production.materials.create-receipt') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                <span>+ Input Barang Masuk</span>
            </a>
        </div>
    </div>



    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Jenis Bahan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalMaterials }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Tersedia di gudang Sentul</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimasi Nilai Bahan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Total valuasi inventaris bahan</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Stok Menipis</span>
            <div class="text-2xl font-bold {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }} mt-1">
                {{ $lowStockCount }}
            </div>
            <span class="text-[11px] text-slate-400 mt-1 block">Bahan di bawah batas minimum</span>
        </div>
    </div>

    <!-- Main Table: Daftar Bahan Baku Gudang -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Daftar Stok Bahan Baku</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    {{ $materials->total() }} Bahan
                </span>
            </div>

            <!-- Search & Category Filters -->
            <form action="{{ route('production.materials.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="category" onchange="this.form.submit()" class="text-xs px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-800 focus:outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari kode/nama/kategori..." 
                        class="text-xs px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-800 placeholder-slate-400 w-44 md:w-56 focus:outline-none"
                    >
                </div>

                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                    Cari
                </button>
                @if(request('search') || request('category'))
                    <a href="{{ route('production.materials.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Kode</th>
                        <th class="py-2.5 px-3">Nama Bahan Baku</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3 text-right">Stok Gudang</th>
                        <th class="py-2.5 px-3">Timbang Terakhir</th>
                        <th class="py-2.5 px-3 text-right">Biaya / Unit</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($materials as $mat)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">
                                {{ $mat->code }}
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">{{ $mat->name }}</span>
                                <span class="text-[10px] text-slate-500">Min. Alert: {{ number_format($mat->minimum_stock, 1) }} {{ $mat->unit }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $mat->category }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($mat->stock_quantity, 1) }} <span class="font-sans text-[10px] text-slate-500 font-normal">{{ $mat->unit }}</span>
                            </td>
                            <td class="py-3 px-3 text-slate-700 text-[11px] font-mono">
                                @if($mat->last_weighed_at)
                                    <span class="font-semibold text-slate-900 block">{{ $mat->last_weighed_at->format('d/m/Y H:i') }} WIB</span>
                                    @if($mat->last_weighed_by)
                                        <span class="text-[10px] text-slate-500 font-sans block">Oleh: {{ $mat->last_weighed_by }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700">
                                Rp {{ number_format($mat->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-center">
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
                                        Cukup
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openSortModal({{ $mat->id }}, '{{ addslashes($mat->code) }}', '{{ addslashes($mat->name) }}', {{ (float) $mat->stock_quantity }}, '{{ addslashes($mat->unit) }}')" 
                                        title="Split Bahan Baku"
                                        class="px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-white text-slate-800 border border-slate-300 hover:bg-slate-100 shadow-sm transition"
                                    >
                                        Split
                                    </button>
                                    <button 
                                        type="button" 
                                        onclick="openRecountModal({{ $mat->id }}, '{{ addslashes($mat->code) }}', '{{ addslashes($mat->name) }}', {{ (float) $mat->stock_quantity }}, '{{ addslashes($mat->unit) }}', '{{ addslashes($mat->last_weighed_by ?? '') }}')" 
                                        title="Timbang Ulang Stok Opname"
                                        class="px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition"
                                    >
                                        Timbang Ulang
                                    </button>
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ $mat->id }}, '{{ addslashes($mat->code) }}', '{{ addslashes($mat->name) }}', '{{ addslashes($mat->category) }}', {{ (float) $mat->minimum_stock }})" 
                                        title="Edit Data Bahan Baku"
                                        class="px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-white text-slate-800 border border-slate-300 hover:bg-slate-100 shadow-sm transition"
                                    >
                                        Edit
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">
                                Belum ada data bahan baku gudang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materials->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-900/10">
                {{ $materials->links() }}
            </div>
        @endif
    </div>

    <!-- Riwayat Alur Barang Gudang (Maximum 60 Data Terakhir) -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Alur Barang Gudang</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Menampilkan 60 riwayat alur barang gudang terbaru (terakhir masuk di posisi atas).</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('production.materials.export-logs') }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Export to Excel</span>
                </a>
                <a href="{{ route('production.materials.create-receipt') }}" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-semibold shadow-sm transition">
                    + Input Barang Masuk
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Jenis Alur</th>
                        <th class="py-2.5 px-3">No. Referensi</th>
                        <th class="py-2.5 px-3">Bahan Baku</th>
                        <th class="py-2.5 px-3 text-right">Kuantitas</th>
                        <th class="py-2.5 px-3">Asal / Tujuan / Keterangan</th>
                        <th class="py-2.5 px-3">Tanggal & Waktu</th>
                        <th class="py-2.5 px-3">Petugas</th>
                        <th class="py-2.5 px-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($recentLogs as $log)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-2.5 px-3">
                                @if($log->type === 'in')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200 whitespace-nowrap inline-block">
                                        Barang Masuk
                                    </span>
                                @elseif($log->type === 'out')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200 whitespace-nowrap inline-block">
                                        Pemakaian Produksi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white shadow-sm whitespace-nowrap inline-block">
                                        Timbang Ulang
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-900">
                                {{ $log->reference_number ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">
                                {{ $log->material->name ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold">
                                @if($log->type === 'in')
                                    <span class="text-emerald-700">+{{ number_format($log->quantity, 1) }} {{ $log->unit }}</span>
                                @elseif($log->type === 'out')
                                    <span class="text-amber-800">-{{ number_format($log->quantity, 1) }} {{ $log->unit }}</span>
                                @else
                                    <span class="text-slate-900">{{ number_format($log->quantity, 1) }} {{ $log->unit }} <span class="font-sans text-[10px] font-normal text-slate-500">(Opname)</span></span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ $log->source_or_destination ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 font-mono text-[11px]">
                                {{ $log->movement_date ? $log->movement_date->format('d/m/Y H:i') : '-' }} WIB
                            </td>
                            <td class="py-2.5 px-3 text-slate-700 font-medium">
                                {{ $log->actor_by ?? '-' }}
                                @if($log->signature_path)
                                    <div class="mt-1">
                                        <img src="{{ asset('storage/' . $log->signature_path) }}" alt="Tanda Tangan" class="h-6 w-auto object-contain border border-slate-200 rounded p-0.5 bg-white">
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px] max-w-xs truncate">
                                {{ $log->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">
                                Belum ada riwayat alur barang gudang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tambah Jenis Bahan Baku Baru -->
<div id="newMaterialModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <h3 class="text-sm font-bold text-slate-900">Tambah Jenis Bahan Baku Baru</h3>
            <button type="button" onclick="closeNewMaterialModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form action="{{ route('production.materials.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kode Bahan</label>
                    <input type="text" name="code" required placeholder="MAT-GHR-008" class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono">
                </div>
                <div class="relative overflow-visible">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                    <div class="relative">
                        <input type="text" name="category" id="new_material_category" required autocomplete="off" placeholder="Pilih / ketik kategori..." class="w-full px-3 py-2 pr-7 rounded-xl text-xs apple-input cursor-pointer">
                        <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>
                    <div id="newCategoryDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"></div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Bahan Baku</label>
                <input type="text" name="name" required placeholder="Nama lengkap bahan baku..." class="w-full px-3 py-2 rounded-xl text-xs apple-input">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Satuan</label>
                    <input type="text" name="unit" required placeholder="kg / tola / pcs" class="w-full px-3 py-2 rounded-xl text-xs apple-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Stok Awal</label>
                    <input type="number" step="0.01" name="stock_quantity" value="" placeholder="0.00" required class="w-full px-3 py-2 rounded-xl text-xs apple-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Min. Alert</label>
                    <input type="number" step="0.01" name="minimum_stock" value="" placeholder="0.00" class="w-full px-3 py-2 rounded-xl text-xs apple-input">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Estimasi Biaya / Unit (Rp)</label>
                <input type="number" step="100" name="unit_cost" value="" placeholder="0" class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button type="button" onclick="closeNewMaterialModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Simpan Bahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hitung Ulang Stok Opname -->
<div id="recountMaterialModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Timbang Ulang Stok (Stock Opname)</h3>
                <p id="recountSubTitle" class="text-[11px] text-slate-600 font-mono mt-0.5"></p>
            </div>
            <button type="button" onclick="closeRecountModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form id="recountForm" method="POST" class="mt-4 space-y-4">
            @csrf
            
            <div class="p-3 rounded-2xl bg-slate-100 border border-slate-200 text-xs">
                <span class="text-slate-600 block">Stok saat ini tercatat di sistem:</span>
                <span id="currentStockDisplay" class="font-mono font-bold text-slate-900 text-sm"></span>
            </div>

            <div>
                <label for="actual_stock" class="block text-xs font-semibold text-slate-800 mb-1">
                    Hasil Timbang Ulang / Stok Fisik Aktif <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0" 
                        id="actual_stock" 
                        name="actual_stock" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300 pr-16"
                    >
                    <span id="recountUnitBadge" class="absolute right-3 top-2 text-xs font-bold text-slate-800">
                        kg
                    </span>
                </div>
            </div>

            <div>
                <label for="recount_notes" class="block text-xs font-semibold text-slate-800 mb-1">
                    Catatan Penimbangan / Stock Opname
                </label>
                <textarea 
                    id="recount_notes" 
                    name="notes" 
                    rows="2" 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                ></textarea>
            </div>

            <div>
                <label for="recount_pic" class="block text-xs font-semibold text-slate-800 mb-1">
                    PIC (Penanggung Jawab)
                </label>
                <input 
                    type="text" 
                    id="recount_pic" 
                    name="weighed_by" 
                    placeholder="Nama penanggung jawab..."
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                >
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button type="button" onclick="closeRecountModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Simpan Hasil Timbang
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Data Bahan Baku -->
<div id="editMaterialModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <h3 class="text-sm font-bold text-slate-900">Edit Data Bahan Baku</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form id="editMaterialForm" method="POST" class="mt-4 space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_code" class="block text-xs font-semibold text-slate-800 mb-1">Kode Bahan Baku <span class="text-red-500">*</span></label>
                <input type="text" id="edit_code" name="code" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono font-bold uppercase">
            </div>

            <div>
                <label for="edit_name" class="block text-xs font-semibold text-slate-800 mb-1">Nama Bahan Baku <span class="text-red-500">*</span></label>
                <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-medium">
            </div>

            <div class="relative overflow-visible">
                <label for="edit_category" class="block text-xs font-semibold text-slate-800 mb-1">Kategori <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" id="edit_category" name="category" required autocomplete="off" placeholder="Pilih / ketik kategori..." class="w-full px-3 py-2 pr-7 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-medium cursor-pointer">
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="editCategoryDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"></div>
            </div>

            <div>
                <label for="edit_minimum_stock" class="block text-xs font-semibold text-slate-800 mb-1">Minimal Alert Stok <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" min="0" id="edit_minimum_stock" name="minimum_stock" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button type="button" onclick="closeEditModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sortir Bahan Baku -->
<div id="sortMaterialModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/40 backdrop-blur-sm overflow-hidden">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl max-w-lg w-full max-h-[88vh] flex flex-col shadow-2xl relative overflow-hidden">
        <div class="shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-900/10 bg-white/60">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Split Bahan</h3>
                <p id="sortSubTitle" class="text-[11px] text-slate-600 font-mono mt-0.5"></p>
            </div>
            <button type="button" onclick="closeSortModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none p-1 transition">
                ✕
            </button>
        </div>

        <form id="sortMaterialForm" method="POST" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf

            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-3.5">
                <div class="p-2.5 rounded-xl bg-slate-100 border border-slate-200 text-xs flex items-center justify-between">
                    <span class="text-slate-600 font-medium">Stok awal:</span>
                    <span id="sortCurrentStockDisplay" class="font-mono font-bold text-slate-900 text-xs"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="sorted_quantity" class="block text-xs font-semibold text-slate-800 mb-1">
                            Kuantitas Split <span class="text-slate-900 font-bold">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                step="0.01" 
                                min="0.01" 
                                id="sorted_quantity" 
                                name="sorted_quantity" 
                                required 
                                class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300 pr-14"
                            >
                            <span id="sortUnitBadge" class="absolute right-3 top-2 text-xs font-mono font-bold text-slate-600">
                                kg
                            </span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-800 mb-1.5">
                        Tujuan Hasil Split <span class="text-slate-900 font-bold">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2.5">
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-300 cursor-pointer bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-slate-800">
                            <input type="radio" name="destination_type" value="new" checked onchange="toggleDestinationType('new')" class="text-slate-900 focus:ring-slate-900">
                            <span>Jadikan Bahan Baru</span>
                        </label>
                        <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-300 cursor-pointer bg-slate-50 hover:bg-slate-100 transition text-xs font-medium text-slate-800">
                            <input type="radio" name="destination_type" value="existing" onchange="toggleDestinationType('existing')" class="text-slate-900 focus:ring-slate-900">
                            <span>Gabung Bahan Ada</span>
                        </label>
                    </div>
                </div>

                <!-- Options: Bahan Baku Baru -->
                <div id="newMaterialSection" class="space-y-2.5 p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div>
                        <label for="sort_new_name" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nama Bahan Baku Baru <span class="text-slate-900 font-bold">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="sort_new_name" 
                            name="new_name" 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label for="sort_new_code" class="block text-[11px] font-semibold text-slate-700 mb-1">Kode (Opsional)</label>
                            <input type="text" id="sort_new_code" name="new_code" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input font-mono">
                        </div>
                        <div class="relative overflow-visible">
                            <label for="sort_new_category" class="block text-[11px] font-semibold text-slate-700 mb-1">Kategori (Opsional)</label>
                            <div class="relative">
                                <input type="text" id="sort_new_category" name="new_category" autocomplete="off" placeholder="Pilih / ketik kategori..." class="w-full px-3 py-1.5 pr-7 rounded-xl text-xs apple-input cursor-pointer">
                                <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="sortCategoryDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-44 overflow-y-auto"></div>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-500">
                        Satuan bahan baru otomatis mengikuti bahan asal: <strong id="sortNewMaterialUnitNotice" class="font-mono text-slate-800">kg</strong>.
                    </p>
                </div>

                <!-- Options: Gabung ke Bahan Existing -->
                <div id="existingMaterialSection" class="hidden space-y-2.5 p-3 rounded-2xl bg-slate-50 border border-slate-200">
                    <div>
                        <label for="existing_material_id" class="block text-xs font-semibold text-slate-800 mb-1">
                            Pilih Bahan Baku Tujuan <span class="text-slate-900 font-bold">*</span>
                        </label>
                        <select 
                            id="existing_material_id" 
                            name="existing_material_id" 
                            onchange="validateUnitMatch()"
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                        >
                            <option value="" data-unit="">Pilih Bahan Baku Tujuan</option>
                            @foreach($allMaterials as $m)
                                <option value="{{ $m->id }}" data-unit="{{ strtolower($m->unit) }}">
                                    [{{ $m->code }}] {{ $m->name }} (Stok: {{ number_format($m->stock_quantity, 1) }} {{ $m->unit }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Unit mismatch alert: COLORLESS & ICON-LESS! -->
                    <div id="unitMismatchAlert" class="hidden p-2.5 rounded-xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-semibold">
                        <p class="font-bold">Satuan tidak dapat digabungkan</p>
                        <p id="unitMismatchText" class="mt-0.5 text-[11px] font-normal text-slate-700"></p>
                    </div>
                </div>

                <div>
                    <label for="sort_notes" class="block text-xs font-semibold text-slate-800 mb-1">
                        Catatan Split
                    </label>
                    <textarea 
                        id="sort_notes" 
                        name="notes" 
                        rows="2" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                    ></textarea>
                </div>
            </div>

            <div class="shrink-0 px-6 py-3 border-t border-slate-900/10 bg-slate-50/90 flex items-center justify-end gap-2">
                <button type="button" onclick="closeSortModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" id="submitSortBtn" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Proses Split
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentSortSourceMaterialId = null;
    let currentSortSourceUnit = '';

    function openNewMaterialModal() {
        document.getElementById('newMaterialModal').classList.remove('hidden');
    }
    function closeNewMaterialModal() {
        document.getElementById('newMaterialModal').classList.add('hidden');
    }

    function openSortModal(id, code, name, stock, unit) {
        currentSortSourceMaterialId = id;
        currentSortSourceUnit = (unit || '').toLowerCase().trim();

        const modal = document.getElementById('sortMaterialModal');
        const form = document.getElementById('sortMaterialForm');
        const title = document.getElementById('sortSubTitle');
        const stockDisplay = document.getElementById('sortCurrentStockDisplay');
        const qtyInput = document.getElementById('sorted_quantity');
        const unitBadge = document.getElementById('sortUnitBadge');
        const unitNotice = document.getElementById('sortNewMaterialUnitNotice');

        form.action = `/production/materials/${id}/split`;
        title.innerText = `[${code}] ${name}`;
        stockDisplay.innerText = `${stock} ${unit}`;
        qtyInput.max = stock;
        qtyInput.value = '';
        unitBadge.innerText = unit;
        unitNotice.innerText = unit;

        const select = document.getElementById('existing_material_id');
        Array.from(select.options).forEach(opt => {
            if (opt.value == id) {
                opt.disabled = true;
                opt.style.display = 'none';
            } else {
                opt.disabled = false;
                opt.style.display = '';
            }
        });
        select.value = '';

        document.querySelector('input[name="destination_type"][value="new"]').checked = true;
        toggleDestinationType('new');

        document.getElementById('sort_new_name').value = '';
        document.getElementById('sort_new_code').value = '';
        document.getElementById('sort_new_category').value = '';
        document.getElementById('sort_notes').value = '';

        clearSortSignature();
        modal.classList.remove('hidden');
        setTimeout(initSortSignatureCanvas, 100);
    }

    let sortCanvas, sortCtx;
    let isDrawingSortSig = false;
    let sortCanvasInitialized = false;

    function initSortSignatureCanvas() {
        sortCanvas = document.getElementById('sortSignatureCanvas');
        if (!sortCanvas) return;
        sortCtx = sortCanvas.getContext('2d');

        sortCtx.strokeStyle = '#0f172a';
        sortCtx.lineWidth = 2;
        sortCtx.lineCap = 'round';
        sortCtx.lineJoin = 'round';

        function getPos(e) {
            const rect = sortCanvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (sortCanvas.width / (rect.width || 1)),
                y: (clientY - rect.top) * (sortCanvas.height / (rect.height || 1))
            };
        }

        function startDraw(e) {
            isDrawingSortSig = true;
            const pos = getPos(e);
            sortCtx.beginPath();
            sortCtx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!isDrawingSortSig) return;
            e.preventDefault();
            const pos = getPos(e);
            sortCtx.lineTo(pos.x, pos.y);
            sortCtx.stroke();
            const input = document.getElementById('sort_signature_data');
            if (input) input.value = sortCanvas.toDataURL('image/png');
        }

        function stopDraw() {
            if (isDrawingSortSig) {
                isDrawingSortSig = false;
                const input = document.getElementById('sort_signature_data');
                if (input) input.value = sortCanvas.toDataURL('image/png');
            }
        }

        if (!sortCanvasInitialized) {
            sortCanvas.addEventListener('mousedown', startDraw);
            sortCanvas.addEventListener('mousemove', draw);
            sortCanvas.addEventListener('mouseup', stopDraw);
            sortCanvas.addEventListener('mouseleave', stopDraw);

            sortCanvas.addEventListener('touchstart', startDraw, { passive: false });
            sortCanvas.addEventListener('touchmove', draw, { passive: false });
            sortCanvas.addEventListener('touchend', stopDraw);

            sortCanvasInitialized = true;
        }
    }

    function clearSortSignature() {
        if (sortCanvas && sortCtx) {
            sortCtx.clearRect(0, 0, sortCanvas.width, sortCanvas.height);
        }
        const input = document.getElementById('sort_signature_data');
        if (input) input.value = '';
    }

    function closeSortModal() {
        document.getElementById('sortMaterialModal').classList.add('hidden');
    }

    function toggleDestinationType(type) {
        const newSec = document.getElementById('newMaterialSection');
        const existSec = document.getElementById('existingMaterialSection');
        const newNameInput = document.getElementById('sort_new_name');
        const existSelect = document.getElementById('existing_material_id');

        if (type === 'new') {
            newSec.classList.remove('hidden');
            existSec.classList.add('hidden');
            newNameInput.required = true;
            existSelect.required = false;
            validateUnitMatch();
        } else {
            newSec.classList.add('hidden');
            existSec.classList.remove('hidden');
            newNameInput.required = false;
            existSelect.required = true;
            validateUnitMatch();
        }
    }

    function validateUnitMatch() {
        const destType = document.querySelector('input[name="destination_type"]:checked')?.value;
        const alertDiv = document.getElementById('unitMismatchAlert');
        const alertText = document.getElementById('unitMismatchText');
        const submitBtn = document.getElementById('submitSortBtn');

        if (destType !== 'existing') {
            alertDiv.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return;
        }

        const select = document.getElementById('existing_material_id');
        const selectedOpt = select.options[select.selectedIndex];

        if (!selectedOpt || !selectedOpt.value) {
            alertDiv.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            return;
        }

        const targetUnit = (selectedOpt.dataset.unit || '').toLowerCase().trim();

        if (targetUnit && targetUnit !== currentSortSourceUnit) {
            alertText.innerText = `Satuan bahan baku asal (${currentSortSourceUnit}) tidak sama dengan bahan baku tujuan (${targetUnit}). Penggabungan tidak dapat dilakukan.`;
            alertDiv.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            alertDiv.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function openRecountModal(id, code, name, stock, unit, weighedBy = '') {
        const modal = document.getElementById('recountMaterialModal');
        const form = document.getElementById('recountForm');
        const title = document.getElementById('recountSubTitle');
        const currentStockDisplay = document.getElementById('currentStockDisplay');
        const actualInput = document.getElementById('actual_stock');
        const unitBadge = document.getElementById('recountUnitBadge');
        const weighedByInput = document.getElementById('weighed_by');

        form.action = `/production/materials/${id}/recount`;
        title.innerText = `[${code}] ${name}`;
        currentStockDisplay.innerText = `${stock} ${unit}`;
        actualInput.value = stock;
        unitBadge.innerText = unit;
        if (weighedByInput) {
            weighedByInput.value = weighedBy;
        }

        modal.classList.remove('hidden');
    }

    function closeRecountModal() {
        document.getElementById('recountMaterialModal').classList.add('hidden');
    }

    function openEditModal(id, code, name, category, minStock) {
        const modal = document.getElementById('editMaterialModal');
        const form = document.getElementById('editMaterialForm');
        
        form.action = `/production/materials/${id}`;
        document.getElementById('edit_code').value = code;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_category').value = category;
        document.getElementById('edit_minimum_stock').value = minStock;

        modal.classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editMaterialModal').classList.add('hidden');
    }

    // Searchable Combobox with Register On-the-Fly for Categories (Rule 7.1)
    function setupCategoryCombobox(inputId, dropdownId, initialCategories) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        if (!input || !dropdown) return;

        let categories = Array.isArray(initialCategories) ? [...initialCategories] : [];

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text || '';
            return div.innerHTML;
        }

        function render(query = '') {
            const q = (query || '').trim();
            const qLower = q.toLowerCase();

            dropdown.innerHTML = '';

            // 1. Opsi Registrasi On-the-Fly di URUTAN PERTAMA / PALING ATAS (Rule 7.1)
            const topItem = document.createElement('div');
            topItem.className = 'px-3.5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 cursor-pointer flex items-center justify-between border-b border-slate-800 transition select-none';
            if (q.length > 0) {
                topItem.innerHTML = `
                    <span class="text-xs font-bold">+ Tambahkan "<strong>${escapeHtml(q)}</strong>"</span>
                    <span class="text-[10px] font-semibold opacity-80 uppercase tracking-wider">Register On-the-Fly</span>
                `;
                topItem.onmousedown = (e) => {
                    e.preventDefault();
                    selectCat(q);
                };
            } else {
                topItem.innerHTML = `
                    <span class="text-xs font-bold">+ Tambah Kategori Baru...</span>
                    <span class="text-[10px] font-semibold opacity-80 uppercase tracking-wider">Ketik Nama</span>
                `;
                topItem.onmousedown = (e) => {
                    e.preventDefault();
                    input.focus();
                };
            }
            dropdown.appendChild(topItem);

            // 2. Daftar Pilihan Kategori
            const matches = categories.filter(c => !qLower || c.toLowerCase().includes(qLower));

            if (matches.length > 0) {
                matches.forEach(c => {
                    const item = document.createElement('div');
                    const isSelected = input.value && input.value.trim().toLowerCase() === c.toLowerCase();
                    item.className = `px-3.5 py-2 hover:bg-slate-100 cursor-pointer flex items-center justify-between border-b border-slate-100 last:border-0 transition select-none ${isSelected ? 'bg-slate-50 font-bold text-slate-900' : 'text-slate-700 text-xs'}`;
                    item.innerHTML = `
                        <span class="text-xs">${escapeHtml(c)}</span>
                        ${isSelected ? '<span class="text-[10px] font-semibold text-slate-500">Terpilih</span>' : ''}
                    `;
                    item.onmousedown = (e) => {
                        e.preventDefault();
                        selectCat(c);
                    };
                    dropdown.appendChild(item);
                });
            } else if (q.length > 0) {
                const noMatch = document.createElement('div');
                noMatch.className = 'px-3.5 py-2 text-[11px] text-slate-400 italic';
                noMatch.innerText = 'Kategori belum terdaftar. Klik opsi di atas untuk mendaftarkannya.';
                dropdown.appendChild(noMatch);
            }

            dropdown.classList.remove('hidden');
        }

        function selectCat(catName) {
            input.value = catName;
            if (!categories.some(c => c.toLowerCase() === catName.toLowerCase())) {
                categories.push(catName);
            }
            dropdown.classList.add('hidden');
        }

        input.addEventListener('focus', () => render(input.value));
        input.addEventListener('input', () => render(input.value));
        input.addEventListener('click', () => render(input.value));
        input.addEventListener('blur', () => {
            setTimeout(() => dropdown.classList.add('hidden'), 200);
        });
    }

    const availableCategories = @json($categories);
    setupCategoryCombobox('new_material_category', 'newCategoryDropdown', availableCategories);
    setupCategoryCombobox('edit_category', 'editCategoryDropdown', availableCategories);
    setupCategoryCombobox('sort_new_category', 'sortCategoryDropdown', availableCategories);
</script>
@endsection
