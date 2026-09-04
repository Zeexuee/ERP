@extends('layouts.app', ['title' => 'Barang Gudang'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Barang Gudang</h2>
            <p class="text-xs text-slate-500 mt-0.5">Inventaris bahan baku dan penerimaan barang masuk pabrik Gaharu Sana'i.</p>
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
                        placeholder="Cari kode/nama/rak..." 
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
                        <th class="py-2.5 px-3 text-right">Biaya / Unit</th>
                        <th class="py-2.5 px-3">Lokasi Simpan</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
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
                                <span class="text-[10px] text-slate-400">Min. Alert: {{ number_format($mat->minimum_stock, 1) }} {{ $mat->unit }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $mat->category }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($mat->stock_quantity, 1) }} <span class="font-sans text-[10px] text-slate-500 font-normal">{{ $mat->unit }}</span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-700">
                                Rp {{ number_format($mat->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $mat->storage_location ?? '-' }}
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
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

    <!-- Riwayat Penerimaan Barang Masuk Terakhir -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Penerimaan Barang Masuk Terakhir</h3>
            <a href="{{ route('production.materials.create-receipt') }}" class="text-xs font-semibold text-slate-700 hover:underline">
                + Input Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">No. Bukti Masuk</th>
                        <th class="py-2.5 px-3">Bahan Baku</th>
                        <th class="py-2.5 px-3 text-right">Jumlah</th>
                        <th class="py-2.5 px-3">Pemasok / Asal</th>
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3">Penerima</th>
                        <th class="py-2.5 px-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($recentReceipts as $rcv)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-2.5 px-3 font-mono font-semibold text-slate-900">
                                {{ $rcv->receipt_number }}
                            </td>
                            <td class="py-2.5 px-3 font-semibold text-slate-800">
                                {{ $rcv->material->name ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-700">
                                +{{ number_format($rcv->quantity, 1) }} {{ $rcv->unit }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ $rcv->source_or_supplier }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-600">
                                {{ $rcv->received_date->format('d/m/Y') }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-700">
                                {{ $rcv->received_by }}
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px] max-w-xs truncate">
                                {{ $rcv->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                Belum ada riwayat barang masuk.
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
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori</label>
                    <input type="text" name="category" required placeholder="Kayu Dasar / Minyak..." class="w-full px-3 py-2 rounded-xl text-xs apple-input">
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
                    <input type="number" step="0.01" name="stock_quantity" value="0" required class="w-full px-3 py-2 rounded-xl text-xs apple-input">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Min. Alert</label>
                    <input type="number" step="0.01" name="minimum_stock" value="10" class="w-full px-3 py-2 rounded-xl text-xs apple-input">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estimasi Biaya / Unit (Rp)</label>
                    <input type="number" step="100" name="unit_cost" value="0" class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Lokasi Rak Simpan</label>
                    <input type="text" name="storage_location" placeholder="Rak A-01" class="w-full px-3 py-2 rounded-xl text-xs apple-input">
                </div>
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

<script>
    function openNewMaterialModal() {
        document.getElementById('newMaterialModal').classList.remove('hidden');
    }
    function closeNewMaterialModal() {
        document.getElementById('newMaterialModal').classList.add('hidden');
    }
</script>
@endsection
