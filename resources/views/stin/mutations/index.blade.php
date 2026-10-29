@extends('layouts.app', ['title' => 'Riwayat Mutasi Barang'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-black tracking-tight text-slate-900">Riwayat Mutasi Barang</h2>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                    Periode: <strong class="text-slate-900 ml-1">{{ $selectedMonthLabel }}</strong>
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Daftar lengkap alur keluar-masuk barang hasil scan barcode dan mutasi inventaris gudang.
            </p>
        </div>

        <!-- Action Shortcuts -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('stin.mutations.export', array_merge(request()->query(), ['month' => $selectedMonth])) }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5" title="Unduh mutasi ke format Excel (CSV)">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </a>
            <a href="{{ route('stin.scanner.index') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
                Scan Barcode
            </a>
            <a href="{{ route('stin.items.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-bold shadow-xs transition">
                Data Gudang
            </a>
        </div>
    </div>

    <!-- Filter & Pilihan Bulan (Bisa Ganti Bulan dengan Cepat) -->
    <div class="apple-glass-card rounded-2xl p-4">
        <form action="{{ route('stin.mutations.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            <!-- Selector Bulan -->
            <div class="flex flex-wrap items-center gap-2">
                <label for="monthSelect" class="text-xs font-bold text-slate-700 whitespace-nowrap">
                    Pilih Bulan:
                </label>
                <div class="relative min-w-[200px]">
                    <select id="monthSelect" name="month" onchange="this.form.submit()" class="w-full text-xs font-bold rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 transition">
                        <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>Semua Bulan (Keseluruhan)</option>
                        @foreach($availableMonths as $m)
                            <option value="{{ $m->period }}" {{ $selectedMonth === $m->period ? 'selected' : '' }}>
                                {{ $m->label }} ({{ number_format($m->count) }} mutasi)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Filter Jenis Mutasi, Kategori & Pencarian -->
            <div class="flex flex-wrap items-center gap-2 flex-1 lg:justify-end">
                <!-- Filter Jenis -->
                <div class="min-w-[130px]">
                    <select name="type" onchange="this.form.submit()" class="w-full text-xs rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900 transition">
                        <option value="all" {{ $selectedType === 'all' || ! $selectedType ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="out" {{ $selectedType === 'out' ? 'selected' : '' }}>Barang Keluar</option>
                        <option value="in" {{ $selectedType === 'in' ? 'selected' : '' }}>Barang Masuk</option>
                        <option value="adjustment" {{ $selectedType === 'adjustment' ? 'selected' : '' }}>Penyesuaian</option>
                    </select>
                </div>

                <!-- Input Pencarian -->
                <div class="relative min-w-[220px]">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari SKU, Barang, PIC..." class="w-full text-xs rounded-xl border border-slate-300 bg-white px-3 py-2 pl-8 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900 transition">
                    <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    Cari
                </button>

                @if($search || ($selectedType && $selectedType !== 'all') || ($selectedCategory && $selectedCategory !== 'all'))
                    <a href="{{ route('stin.mutations.index', ['month' => $selectedMonth]) }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Reset filter">
                        Reset
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tabel Riwayat Mutasi Barang -->
    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-900/10 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                Riwayat Mutasi &mdash; <span class="text-indigo-600">{{ $selectedMonthLabel }}</span>
            </h3>
            <span class="text-xs text-slate-500 font-medium">
                Total {{ number_format($movements->total()) }} transaksi
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-[11px] uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">Barang & SKU</th>
                        <th class="px-5 py-3.5">Jenis Mutasi</th>
                        <th class="px-5 py-3.5 text-center">Jumlah</th>
                        <th class="px-5 py-3.5 text-center">Sisa Stok</th>
                        <th class="px-5 py-3.5">Petugas (PIC)</th>
                        <th class="px-5 py-3.5">Keterangan / Sumber</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($movements as $m)
                        @php
                            $item = $m->item;
                            $unit = $item ? ($item->unit ?? 'pcs') : 'pcs';
                            $isOut = $m->type === 'out';
                            $isIn = $m->type === 'in';
                            $movementData = [
                                'id' => $m->id,
                                'type' => $m->type,
                                'type_label' => $m->getMovementTypeLabel(),
                                'quantity' => $m->quantity,
                                'unit' => $unit,
                                'stock_before' => $m->stock_before,
                                'stock_after' => $m->stock_after,
                                'item_name' => $item ? $item->name : 'Item Terhapus',
                                'item_sku' => $item ? $item->sku : '-',
                                'item_category' => $item ? ($item->category ?? '-') : '-',
                                'pic_name' => $m->pic_name ?: ($m->user?->name ?? '-'),
                                'user_name' => $m->user?->name ?? '-',
                                'notes' => $m->notes ?: '-',
                                'reference_type' => $m->reference_type,
                                'reference_id' => $m->reference_id,
                                'date_formatted' => ($m->movement_date ?? $m->created_at)?->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i:s') . ' WIB',
                                'signature_url' => $m->signature_path ? asset('storage/' . $m->signature_path) : null,
                            ];
                        @endphp
                        <tr class="hover:bg-white/60 transition text-xs">
                            <!-- Waktu Mutasi -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">
                                    {{ ($m->movement_date ?? $m->created_at)?->timezone('Asia/Jakarta')->translatedFormat('d M Y') ?? '-' }}
                                </div>
                                <div class="text-[11px] font-mono text-slate-400">
                                    {{ ($m->movement_date ?? $m->created_at)?->timezone('Asia/Jakarta')->format('H:i:s') ?? '-' }} WIB
                                </div>
                            </td>

                            <!-- Informasi Barang -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900 truncate max-w-xs" title="{{ $item ? $item->name : 'Item Terhapus' }}">
                                    {{ $item ? $item->name : 'Item Terhapus' }}
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">
                                        {{ $item ? $item->sku : '-' }}
                                    </span>
                                    @if($item && $item->category)
                                        <span class="text-[10px] text-slate-400">&bull; {{ $item->category }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Jenis Mutasi -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($isOut)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        Barang Keluar
                                    </span>
                                @elseif($isIn)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        Barang Masuk
                                    </span>
                                @elseif($m->type === 'adjustment')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        Penyesuaian
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ ucfirst($m->type) }}
                                    </span>
                                @endif
                            </td>

                            <!-- Jumlah Perubahan Qty (Contoh: Keluar 2 unit) -->
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                @if($isOut)
                                    <span class="font-black text-sm text-rose-600">
                                        -{{ number_format($m->quantity) }} {{ $unit }}
                                    </span>
                                @elseif($isIn)
                                    <span class="font-black text-sm text-emerald-600">
                                        +{{ number_format($m->quantity) }} {{ $unit }}
                                    </span>
                                @else
                                    <span class="font-bold text-sm text-slate-800">
                                        {{ number_format($m->quantity) }} {{ $unit }}
                                    </span>
                                @endif
                            </td>

                            <!-- Sisa Stok Akhir -->
                            <td class="px-5 py-4 whitespace-nowrap text-center font-mono font-bold text-slate-900">
                                {{ number_format($m->stock_after) }} {{ $unit }}
                            </td>

                            <!-- Petugas (PIC) -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ $m->pic_name ?: ($m->user?->name ?? '-') }}</div>
                            </td>

                            <!-- Catatan / Sumber Barcode -->
                            <td class="px-5 py-4 max-w-xs">
                                <div class="text-slate-700 truncate" title="{{ $m->notes ?? '-' }}">
                                    {{ $m->notes ?: '-' }}
                                </div>
                            </td>

                            <!-- Tombol Lihat Detail -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <button type="button" 
                                        onclick='openDetailModal(@json($movementData))' 
                                        class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-slate-700">Tidak ada riwayat mutasi</div>
                                <div class="text-xs text-slate-500 mt-1">Belum ada pergerakan barang pada bulan <strong>{{ $selectedMonthLabel }}</strong>.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-slate-900/10">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Modal Rincian / Detail Mutasi Barang -->
<div id="detailModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-5 animate-in fade-in duration-150">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span id="modalTypeBadge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                    Mutasi
                </span>
                <h4 class="text-base font-bold text-slate-900">Rincian Detail Mutasi</h4>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-sm font-bold transition">
                &times;
            </button>
        </div>

        <!-- Ringkasan Perubahan Qty -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-3 gap-3 text-center">
            <div>
                <span class="block text-[11px] text-slate-500 font-medium">Stok Sebelum</span>
                <span id="modalStockBefore" class="font-mono font-bold text-sm text-slate-700">0</span>
            </div>
            <div class="border-x border-slate-200">
                <span class="block text-[11px] text-slate-500 font-medium">Jumlah Mutasi</span>
                <span id="modalQuantityChange" class="font-black text-base text-slate-900">-</span>
            </div>
            <div>
                <span class="block text-[11px] text-slate-500 font-medium">Stok Sesudah</span>
                <span id="modalStockAfter" class="font-mono font-extrabold text-sm text-slate-900">0</span>
            </div>
        </div>

        <!-- Data Barang -->
        <div class="space-y-3 text-xs">
            <div class="flex items-start justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500 font-medium">Nama Barang</span>
                <span id="modalItemName" class="font-bold text-slate-900 text-right max-w-xs">-</span>
            </div>
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500 font-medium">Kode SKU / Barcode</span>
                <span id="modalItemSku" class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded">-</span>
            </div>
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500 font-medium">Kategori Barang</span>
                <span id="modalItemCategory" class="font-semibold text-slate-800">-</span>
            </div>
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500 font-medium">Waktu Transaksi</span>
                <span id="modalDateFormatted" class="font-medium text-slate-800">-</span>
            </div>
            <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                <span class="text-slate-500 font-medium">Petugas Lapangan (PIC)</span>
                <span id="modalPicName" class="font-bold text-slate-900">-</span>
            </div>
            <div class="py-1.5 border-b border-slate-100">
                <span class="block text-slate-500 font-medium mb-1">Catatan / Keterangan Sumber</span>
                <div id="modalNotes" class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-medium">
                    -
                </div>
            </div>

            <!-- Tanda Tangan Digital PIC jika ada -->
            <div id="modalSignatureContainer" class="hidden pt-2">
                <span class="block text-slate-500 font-medium mb-1.5">Tanda Tangan Digital PIC:</span>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center">
                    <img id="modalSignatureImg" src="" alt="Tanda Tangan PIC" class="max-h-32 max-w-full object-contain">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Modal -->
        <div class="pt-2 flex items-center justify-end gap-2">
            <button type="button" onclick="closeDetailModal()" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                Tutup Rincian
            </button>
        </div>
    </div>
</div>

<script>
function openDetailModal(data) {
    const modal = document.getElementById('detailModal');

    // Type Badge
    const badge = document.getElementById('modalTypeBadge');
    if (data.type === 'out') {
        badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800';
        badge.textContent = 'Barang Keluar';
    } else if (data.type === 'in') {
        badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800';
        badge.textContent = 'Barang Masuk';
    } else {
        badge.className = 'px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800';
        badge.textContent = data.type_label || 'Penyesuaian';
    }

    // Perubahan Qty
    document.getElementById('modalStockBefore').textContent = `${data.stock_before} ${data.unit}`;
    document.getElementById('modalStockAfter').textContent = `${data.stock_after} ${data.unit}`;
    
    const qtyChange = document.getElementById('modalQuantityChange');
    if (data.type === 'out') {
        qtyChange.className = 'font-black text-base text-rose-600';
        qtyChange.textContent = `-${data.quantity} ${data.unit}`;
    } else if (data.type === 'in') {
        qtyChange.className = 'font-black text-base text-emerald-600';
        qtyChange.textContent = `+${data.quantity} ${data.unit}`;
    } else {
        qtyChange.className = 'font-black text-base text-slate-800';
        qtyChange.textContent = `${data.quantity} ${data.unit}`;
    }

    // Informasi Barang
    document.getElementById('modalItemName').textContent = data.item_name;
    document.getElementById('modalItemSku').textContent = data.item_sku;
    document.getElementById('modalItemCategory').textContent = data.item_category;
    document.getElementById('modalDateFormatted').textContent = data.date_formatted;
    document.getElementById('modalPicName').textContent = data.pic_name;
    document.getElementById('modalNotes').textContent = data.notes || 'Tidak ada catatan tambahan.';

    // Signature
    const sigContainer = document.getElementById('modalSignatureContainer');
    const sigImg = document.getElementById('modalSignatureImg');
    if (data.signature_url) {
        sigImg.src = data.signature_url;
        sigContainer.classList.remove('hidden');
    } else {
        sigContainer.classList.add('hidden');
        sigImg.src = '';
    }

    modal.classList.remove('hidden');
}

function closeDetailModal() {
    const modal = document.getElementById('detailModal');
    modal.classList.add('hidden');
}

// Close when clicking backdrop
document.getElementById('detailModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailModal();
    }
});
</script>
@endsection
