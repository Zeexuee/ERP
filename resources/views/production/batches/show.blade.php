@extends('layouts.app', ['title' => 'Detail Produksi #' . $batch->batch_number])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Batch #{{ $batch->batch_number }}</h2>
                @if($batch->status === 'in_progress')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white shadow-sm">
                        Berjalan
                    </span>
                @elseif($batch->status === 'completed')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Selesai & Lolos QC
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-700">
                        {{ ucfirst($batch->status) }}
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Target: <span class="font-bold text-slate-800">{{ $batch->product->name ?? '-' }}</span> ({{ number_format($batch->target_quantity, 1) }} {{ $batch->unit }})
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.batches.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
                ← Kembali ke Daftar
            </a>

            @if($batch->status === 'in_progress')
                <button type="button" onclick="openCompleteModal()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                    ✓ Selesaikan Produksi
                </button>
            @endif
        </div>
    </div>

    <!-- Status & Progress Overview Card -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tahap Saat Ini</span>
            <div class="text-sm font-bold text-slate-900 mt-1">{{ $batch->stage }}</div>
            <span class="text-[10px] text-slate-400 mt-1 block">PIC: {{ $batch->pic_name }}</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Hasil Jadi</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($batch->target_quantity, 1) }} <span class="text-xs font-sans font-normal text-slate-500">{{ $batch->unit }}</span>
            </div>
            @if($batch->status === 'completed' && $batch->actual_quantity !== null)
                <span class="text-[10px] text-emerald-700 font-bold mt-1 block">Realisasi: {{ number_format($batch->actual_quantity, 1) }} {{ $batch->unit }}</span>
            @else
                <span class="text-[10px] text-slate-400 mt-1 block">Menunggu hasil penimbangan akhir</span>
            @endif
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Jadwal Pengerjaan</span>
            <div class="text-xs font-bold text-slate-800 mt-1">Mulai: {{ $batch->start_date->format('d/m/Y') }}</div>
            <span class="text-[10px] text-slate-500 mt-0.5 block">
                Target: {{ $batch->target_completion_date ? $batch->target_completion_date->format('d/m/Y') : '-' }}
            </span>
            @if($batch->completed_date)
                <span class="text-[10px] text-emerald-600 font-semibold block">Selesai: {{ $batch->completed_date->format('d/m/Y') }}</span>
            @endif
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Referensi Permintaan</span>
            @if($batch->productionRequest)
                <div class="text-xs font-bold text-slate-900 mt-1 font-mono">
                    #{{ $batch->productionRequest->request_number }}
                </div>
                <span class="text-[10px] text-slate-500 block">
                    Order: {{ $batch->productionRequest->salesOrder->order_number ?? '-' }}
                </span>
                <span class="text-[10px] text-slate-400 block truncate">
                    {{ $batch->productionRequest->salesOrder->customer->name ?? '-' }}
                </span>
            @else
                <div class="text-xs font-semibold text-slate-600 mt-1">Produksi Stok Pabrik</div>
                <span class="text-[10px] text-slate-400 block">Bukan dari pesanan khusus Sales</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Alokasi Bahan Baku & Catatan Formula -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Komposisi Bahan Baku (BOM) -->
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-3">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                    Komposisi Bahan Baku (BOM)
                </h3>

                <div class="divide-y divide-slate-900/5">
                    @forelse($batch->batchMaterials as $bm)
                        <div class="py-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $bm->material->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $bm->material->code ?? '' }} • {{ $bm->material->category ?? '' }}</span>
                            </div>
                            <div class="text-right">
                                <span class="font-mono font-bold text-slate-900 block">
                                    {{ number_format($bm->quantity_used, 1) }} {{ $bm->unit }}
                                </span>
                                <span class="text-[10px] text-slate-400 font-mono">
                                    @ Rp {{ number_format($bm->material->unit_cost ?? 0, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-3 text-center text-xs text-slate-400">
                            Tidak ada data bahan tercatat.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Catatan Formula -->
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-2">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                    Instruksi & Catatan Formula
                </h3>
                <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $batch->notes ?: 'Tidak ada catatan khusus untuk batch produksi ini.' }}
                </p>
            </div>
        </div>

        <!-- Kolom Kanan: Laporan Proses Harian & Riwayat Log -->
        <div class="space-y-6 lg:col-span-2">
            
            <!-- Form Input Laporan Proses Harian (Jika Masih Berjalan) -->
            @if($batch->status === 'in_progress')
                <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                        + Tambah Laporan Proses Harian
                    </h3>

                    <form action="{{ route('production.batches.store-log', $batch) }}" method="POST" class="space-y-3">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Laporan</label>
                                <input type="date" name="log_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tahap Pengerjaan</label>
                                <select name="stage" required class="w-full px-3 py-1.5 rounded-xl text-xs apple-input bg-white">
                                    <option value="1. Persiapan Bahan & Sortir Kayu" {{ $batch->stage === '1. Persiapan Bahan & Sortir Kayu' ? 'selected' : '' }}>1. Persiapan Bahan & Sortir Kayu</option>
                                    <option value="2. Infusi / Vacuum Pressure Resin & Minyak" {{ $batch->stage === '2. Infusi / Vacuum Pressure Resin & Minyak' ? 'selected' : '' }}>2. Infusi / Vacuum Pressure Resin & Minyak</option>
                                    <option value="3. Pengeringan & Curing Aroma" {{ $batch->stage === '3. Pengeringan & Curing Aroma' ? 'selected' : '' }}>3. Pengeringan & Curing Aroma</option>
                                    <option value="4. Quality Control & Pengujian Bakar" {{ $batch->stage === '4. Quality Control & Pengujian Bakar' ? 'selected' : '' }}>4. Quality Control & Pengujian Bakar</option>
                                    <option value="5. Selesai & Masuk Stok Barang Jadi">5. Selesai & Masuk Stok Barang Jadi</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Progres (%)</label>
                                <input type="number" min="0" max="100" name="progress_percentage" value="50" required class="w-full px-3 py-1.5 rounded-xl text-xs apple-input font-mono">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Petugas Pelapor (PIC)</label>
                                <input type="text" name="pic_name" value="{{ auth()->user()?->name ?? 'Teknisi Pabrik' }}" required class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Proses Harian & Observasi</label>
                                <input type="text" name="notes" required placeholder="Contoh: Tekanan vacuum dijaga 4 bar, aroma balsamic mulai merata..." class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                                Simpan Laporan Harian
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Riwayat Laporan Harian (Timeline) -->
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
                <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                    <h3 class="text-sm font-bold text-slate-900">
                        Riwayat Laporan Proses Harian
                    </h3>
                    <span class="text-xs text-slate-500">{{ $batch->dailyLogs->count() }} Laporan</span>
                </div>

                <div class="space-y-3">
                    @forelse($batch->dailyLogs as $log)
                        <div class="p-3.5 rounded-2xl bg-white/70 border border-slate-200/80 space-y-1.5">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">
                                        {{ $log->log_date->format('d M Y') }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                        {{ $log->stage }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[11px] font-mono font-bold text-slate-700">
                                        Progres: {{ $log->progress_percentage }}%
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        Oleh: {{ $log->pic_name }}
                                    </span>
                                </div>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">
                                {{ $log->notes }}
                            </p>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs text-slate-400">
                            Belum ada laporan harian yang dicatat.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Modal Selesaikan Produksi -->
@if($batch->status === 'in_progress')
<div id="completeModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <h3 class="text-sm font-bold text-slate-900">Selesaikan Batch #{{ $batch->batch_number }}</h3>
            <button type="button" onclick="closeCompleteModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form action="{{ route('production.batches.complete', $batch) }}" method="POST" class="mt-4 space-y-4">
            @csrf

            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 space-y-1">
                <span class="font-bold block">✓ Konversi Otomatis ke Stok Produk Jadi</span>
                <p>Setelah diselesaikan, kuantitas hasil produksi aktual akan langsung ditambahkan ke katalog produk <strong>{{ $batch->product->name }}</strong>.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Kuantitas Riil / Aktual <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            name="actual_quantity" 
                            value="{{ $batch->target_quantity }}" 
                            required 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono pr-12"
                        >
                        <span class="absolute right-3 top-2 text-xs font-semibold text-slate-400">
                            {{ $batch->unit }}
                        </span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="completed_date" 
                        value="{{ date('Y-m-d') }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Gudang Simpan Barang Jadi
                </label>
                <select name="branch_code" class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white">
                    <option value="PLANT-STL">Pabrik Pengolahan Sentul (PLANT-STL)</option>
                    <option value="WH-JKT">Gudang Utama Jakarta (WH-JKT)</option>
                    <option value="WH-SBY">Depot Distribusi Surabaya (WH-SBY)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Catatan Uji Kualitas / QC
                </label>
                <textarea 
                    name="notes" 
                    rows="2" 
                    placeholder="Hasil uji bakar sampel aroma pekat merata, lolos standar QC grade A..." 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button type="button" onclick="closeCompleteModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-sm transition">
                    Konfirmasi Selesai
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCompleteModal() {
        document.getElementById('completeModal').classList.remove('hidden');
    }
    function closeCompleteModal() {
        document.getElementById('completeModal').classList.add('hidden');
    }
</script>
@endif
@endsection
