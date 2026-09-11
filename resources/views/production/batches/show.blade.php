@extends('layouts.app', ['title' => 'Detail Produksi #' . $batch->batch_number])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Batch #{{ $batch->batch_number }}</h2>
                @if($batch->status === 'in_progress')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white shadow-xs">
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
            <a href="{{ route('production.batches.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Kembali ke Daftar
            </a>
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
                <span class="text-[10px] text-emerald-700 font-bold mt-1 block">Realisasi: {{ number_format($batch->actual_quantity, 1) }} {{ $batch->product->unit ?? $batch->unit }}</span>
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

    <!-- 5 Proses Manufaktur Pabrik (Fleksibel & Terpisah) -->
    @php
        $pastStages = $batch->dailyLogs->pluck('stage')->toArray();
        $pastStages[] = $batch->stage;

        $hasTembak = collect($pastStages)->contains(fn($s) => str_contains($s, 'Tembak'));
        $hasCelupCuci = collect($pastStages)->contains(fn($s) => str_contains($s, 'Celup') || str_contains($s, 'Cuci'));
        $hasWarna = collect($pastStages)->contains(fn($s) => str_contains($s, 'Warna'));
        $hasFinishing = collect($pastStages)->contains(fn($s) => str_contains($s, 'Finishing') || str_contains($s, 'Molen') || str_contains($s, 'Kerok') || str_contains($s, 'Bor'));
        $hasSelesai = collect($pastStages)->contains(fn($s) => str_contains($s, 'Selesai'));

        $isTembakActive = str_contains($batch->stage, 'Tembak');
        $isCelupCuciActive = str_contains($batch->stage, 'Celup') || str_contains($batch->stage, 'Cuci');
        $isWarnaActive = str_contains($batch->stage, 'Warna');
        $isFinishingActive = str_contains($batch->stage, 'Finishing') || str_contains($batch->stage, 'Molen') || str_contains($batch->stage, 'Kerok') || str_contains($batch->stage, 'Bor');
        $isSelesaiActive = str_contains($batch->stage, 'Selesai');
    @endphp

    <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-900/10 pb-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-900">5 Proses Manufaktur Pabrik</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                        Fleksibel & Independen
                    </span>
                </div>
                <span class="text-[11px] text-slate-500 block mt-0.5">
                    Setiap proses dapat dipilih dan dijalankan secara bebas sesuai kebutuhan produk di lapangan.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-mono font-semibold text-slate-600">
                    Proses Aktif: <strong class="text-slate-900">{{ $batch->stage }}</strong>
                </span>
            </div>
        </div>

        <!-- 5 Cards Proses Manufaktur (Fleksibel Bebas Pilih) -->
        <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
            <!-- Tembak -->
            <div onclick="selectProcessStage('Tembak')" class="p-3 rounded-2xl border cursor-pointer hover:scale-[1.01] transition {{ $isTembakActive ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : ($hasTembak ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-white/60 text-slate-600 border-slate-200') }}">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider mb-1">
                    <span>Proses Tembak</span>
                    @if($isTembakActive)
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-white text-slate-900">Aktif</span>
                    @elseif($hasTembak)
                        <span class="text-emerald-700 font-bold">✓ Tercatat</span>
                    @endif
                </div>
                <div class="text-xs font-bold leading-tight">
                    Injeksi & Jemur
                </div>
                <div class="text-[9px] mt-1 opacity-70">
                    {{ $batch->wet_result_weight ? number_format($batch->wet_result_weight, 1) . ' kg basah' : 'Kayu + Getah' }}
                </div>
            </div>

            <!-- Celup / Cuci -->
            <div onclick="selectProcessStage('Celup')" class="p-3 rounded-2xl border cursor-pointer hover:scale-[1.01] transition {{ $isCelupCuciActive ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : ($hasCelupCuci ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-white/60 text-slate-600 border-slate-200') }}">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider mb-1">
                    <span>Celup / Cuci</span>
                    @if($isCelupCuciActive)
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-white text-slate-900">Aktif</span>
                    @elseif($hasCelupCuci)
                        <span class="text-emerald-700 font-bold">✓ Tercatat</span>
                    @endif
                </div>
                <div class="text-xs font-bold leading-tight">
                    Perendaman / Cuci
                </div>
                <div class="text-[9px] mt-1 opacity-70">
                    Bisa Celup, Cuci, atau keduanya
                </div>
            </div>

            <!-- Warna -->
            <div onclick="selectProcessStage('Warna')" class="p-3 rounded-2xl border cursor-pointer hover:scale-[1.01] transition {{ $isWarnaActive ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : ($hasWarna ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-white/60 text-slate-600 border-slate-200') }}">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider mb-1">
                    <span>Pewarnaan</span>
                    @if($isWarnaActive)
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-white text-slate-900">Aktif</span>
                    @elseif($hasWarna)
                        <span class="text-emerald-700 font-bold">✓ Tercatat</span>
                    @endif
                </div>
                <div class="text-xs font-bold leading-tight">
                    Pewarnaan Kayu
                </div>
                <div class="text-[9px] mt-1 opacity-70">
                    Formulasi pigmen / lewati
                </div>
            </div>

            <!-- Finishing -->
            <div onclick="selectProcessStage('Finishing (Molen)')" class="p-3 rounded-2xl border cursor-pointer hover:scale-[1.01] transition {{ $isFinishingActive ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : ($hasFinishing ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-white/60 text-slate-600 border-slate-200') }}">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider mb-1">
                    <span>Finishing</span>
                    @if($isFinishingActive)
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-white text-slate-900">Aktif</span>
                    @elseif($hasFinishing)
                        <span class="text-emerald-700 font-bold">✓ Tercatat</span>
                    @endif
                </div>
                <div class="text-xs font-bold leading-tight">
                    Molen / Kerok / Bor
                </div>
                <div class="text-[9px] mt-1 opacity-70">
                    Pabrik / Vendor Pak Kholil
                </div>
            </div>

            <!-- Selesai -->
            <div onclick="selectProcessStage('Selesai')" class="p-3 rounded-2xl border cursor-pointer hover:scale-[1.01] transition {{ $isSelesaiActive ? 'bg-slate-900 text-white border-slate-900 shadow-sm' : ($hasSelesai ? 'bg-emerald-50 text-emerald-900 border-emerald-200' : 'bg-white/60 text-slate-600 border-slate-200') }}">
                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider mb-1">
                    <span>Selesai & QC</span>
                    @if($isSelesaiActive || $batch->status === 'completed')
                        <span class="px-1.5 py-0.2 rounded text-[8px] font-extrabold bg-white text-slate-900">Selesai</span>
                    @endif
                </div>
                <div class="text-xs font-bold leading-tight">
                    QC & Gudang Jadi
                </div>
                <div class="text-[9px] mt-1 opacity-70">
                    Realisasi stok barang jadi
                </div>
            </div>
        </div>

        <!-- Metrics Tembak & Vendor Overview Bar -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 border-t border-slate-100 text-xs">
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Hasil Tembak Basah</span>
                <span class="font-mono font-bold text-slate-800 text-sm">
                    {{ $batch->wet_result_weight ? number_format($batch->wet_result_weight, 1) . ' kg' : '-' }}
                </span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Getah Sisa Tembak</span>
                <span class="font-mono font-bold text-slate-800 text-sm">
                    {{ $batch->residual_resin_weight ? number_format($batch->residual_resin_weight, 1) . ' kg' : '-' }}
                </span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Hasil Setelah Jemur</span>
                <span class="font-mono font-bold text-slate-800 text-sm">
                    {{ $batch->dried_result_weight ? number_format($batch->dried_result_weight, 1) . ' kg' : '-' }}
                </span>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Vendor Bor Pak Kholil</span>
                <span class="font-bold text-slate-800 truncate block">
                    {{ $batch->vendor_name ? $batch->vendor_name : '-' }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom Kiri: Alokasi Bahan Baku & Catatan Formula -->
        <div class="space-y-6 lg:col-span-1">
            <!-- Komposisi Bahan Baku (BOM) -->
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-3">
                <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                    <h3 class="text-sm font-bold text-slate-900">
                        Komposisi Bahan Baku (BOM)
                    </h3>
                    <button type="button" onclick="openAddMaterialModal()" class="px-3 py-1 rounded-xl btn-dark text-xs font-semibold shadow-xs">
                        + Tambah Bahan
                    </button>
                </div>

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
                        Pencatatan Proses Produksi (Batch #{{ $batch->batch_number }})
                    </h3>

                    @error('materials')
                        <div class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-700 font-medium">
                            {{ $message }}
                        </div>
                    @enderror

                    <form action="{{ route('production.batches.store-log', $batch) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Laporan <span class="text-red-500">*</span></label>
                                <input type="date" name="log_date" value="{{ old('log_date', date('Y-m-d')) }}" required class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Pilih Proses Pengerjaan <span class="text-red-500">*</span></label>
                                <select name="stage" id="stageSelect" required class="w-full px-3 py-1.5 rounded-xl text-xs apple-input bg-white font-semibold">
                                    <option value="Tembak" {{ old('stage', $batch->stage) === 'Tembak' ? 'selected' : '' }}>Proses Tembak & Injeksi Getah</option>
                                    <option value="Celup" {{ old('stage', $batch->stage) === 'Celup' ? 'selected' : '' }}>Proses Celup (Getah)</option>
                                    <option value="Cuci" {{ old('stage', $batch->stage) === 'Cuci' ? 'selected' : '' }}>Proses Cuci Kayu</option>
                                    <option value="Warna" {{ old('stage', $batch->stage) === 'Warna' ? 'selected' : '' }}>Proses Pewarnaan</option>
                                    <option value="Finishing (Molen)" {{ old('stage', $batch->stage) === 'Finishing (Molen)' ? 'selected' : '' }}>Proses Finishing — Mesin Molen (Halus & Kilap)</option>
                                    <option value="Finishing (Kerok)" {{ old('stage', $batch->stage) === 'Finishing (Kerok)' ? 'selected' : '' }}>Proses Finishing — Kerok Manual (Serat Alami)</option>
                                    <option value="Finishing (Bor / Vendor Pak Kholil)" {{ old('stage', $batch->stage) === 'Finishing (Bor / Vendor Pak Kholil)' ? 'selected' : '' }}>Proses Finishing — Bor (Vendor Pak Kholil)</option>
                                    <option value="Selesai" {{ old('stage') === 'Selesai' ? 'selected' : '' }}>Selesai & Lolos QC (Simpan ke Gudang Jadi)</option>
                                </select>
                            </div>

                            <div id="workStatusContainer">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pengerjaan <span class="text-red-500">*</span></label>
                                <select name="work_status" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input bg-white">
                                    <option value="selesai" {{ old('work_status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    <option value="ulang" {{ old('work_status') === 'ulang' ? 'selected' : '' }}>Ulang</option>
                                    <option value="tertunda" {{ old('work_status') === 'tertunda' ? 'selected' : '' }}>Tertunda</option>
                                </select>
                            </div>
                        </div>

                        <!-- Metric Box Tahap Tembak & Penjemuran -->
                        <div id="tembakMetricsBox" class="p-4 rounded-2xl bg-white/80 border border-slate-200/80 space-y-3">
                            <h4 class="text-xs font-bold text-slate-900 border-b border-slate-100 pb-1.5">
                                Catatan Hasil Tembak & Penjemuran
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Hasil Tembak Basah (kg)</label>
                                    <input type="number" step="0.01" name="wet_result_weight" value="{{ old('wet_result_weight', $batch->wet_result_weight) }}" placeholder="0.00" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input font-mono">
                                    <span class="text-[9px] text-slate-400 mt-0.5 block">Timbangan basah setelah injeksi</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Getah Sisa Tembak (kg)</label>
                                    <input type="number" step="0.01" name="residual_resin_weight" value="{{ old('residual_resin_weight', $batch->residual_resin_weight) }}" placeholder="0.00" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input font-mono">
                                    <span class="text-[9px] text-slate-400 mt-0.5 block">Sisa getah dalam bejana</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Kembalikan Getah ke Gudang</label>
                                    <select name="residual_resin_material_id" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input bg-white">
                                        <option value="">-- Jangan Kembalikan --</option>
                                        @foreach($materials->where('category', 'Minyak & Resin') as $rMat)
                                            <option value="{{ $rMat->id }}">{{ $rMat->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-[9px] text-slate-400 mt-0.5 block">Menambah stok getah gudang</span>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Hasil Setelah Jemur (kg)</label>
                                    <input type="number" step="0.01" name="weighed_result_weight" value="{{ old('weighed_result_weight', $batch->dried_result_weight) }}" placeholder="0.00" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input font-mono font-bold">
                                    <span class="text-[9px] text-slate-400 mt-0.5 block">Timbangan kering setelah jemur</span>
                                </div>
                            </div>
                        </div>

                        <!-- Metric Box Vendor Bor Pak Kholil (Khusus Produk KLM) -->
                        <div id="vendorMetricsBox" class="hidden p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80 space-y-3">
                            <div class="flex items-center justify-between border-b border-amber-200/70 pb-1.5">
                                <h4 class="text-xs font-bold text-amber-950">
                                    Pengiriman ke Vendor Finishing Bor (Pak Kholil)
                                </h4>
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-200 text-amber-900 rounded-lg">Pihak Ketiga</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Nama Vendor Pengerjaan</label>
                                    <input type="text" name="vendor_name" value="{{ old('vendor_name', $batch->vendor_name ?? 'Pak Kholil') }}" placeholder="Pak Kholil" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input font-semibold">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Dikirim ke Vendor</label>
                                    <input type="date" name="vendor_sent_date" value="{{ old('vendor_sent_date', $batch->vendor_sent_date?->format('Y-m-d') ?? date('Y-m-d')) }}" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Tanggal Diterima Kembali</label>
                                    <input type="date" name="vendor_received_date" value="{{ old('vendor_received_date', $batch->vendor_received_date?->format('Y-m-d')) }}" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic BOM Material Allocation Container (Shown when stage === Tembak atau Celup) -->
                        <div id="bomAllocationContainer" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900">Alokasi Bahan Baku Tambahan (Jika Ada)</h4>
                                    <p class="text-[10px] text-slate-500 mt-0.5">Misal pemakaian getah tambahan pada proses celup atau pewarna. Stok gudang akan otomatis dipotong.</p>
                                </div>
                                <button type="button" onclick="addMaterialRowLog()" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                                    + Tambah Baris Bahan
                                </button>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                            <th class="py-2 px-2">Bahan Baku</th>
                                            <th class="py-2 px-2">Stok Gudang Tersedia</th>
                                            <th class="py-2 px-2">Kuantitas Digunakan</th>
                                            <th class="py-2 px-2 text-center w-12">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="materialRowsContainerLog" class="divide-y divide-slate-200">
                                        <!-- Populated dynamically via JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Dynamic Actual Qty Field when stage === Selesai -->
                        <div id="actualQtyContainer" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                            <label class="block text-xs font-bold text-slate-900">Hasil Barang Jadi Aktual <span class="text-red-500">*</span></label>
                            <div class="relative max-w-xs">
                                <input type="number" step="0.01" min="0.01" name="actual_quantity" value="{{ old('actual_quantity', $batch->target_quantity) }}" class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono pr-16 text-slate-900 font-bold bg-white">
                                <span class="absolute right-3 top-2 text-xs font-semibold text-slate-400">{{ $batch->product->unit ?? $batch->unit }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Petugas Pelapor (PIC) <span class="text-red-500">*</span></label>
                                <input type="text" name="pic_name" value="{{ old('pic_name', auth()->user()->name ?? '') }}" required placeholder="Masukkan nama PIC..." class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Proses Harian & Observasi <span class="text-red-500">*</span></label>
                                <input type="text" name="notes" value="{{ old('notes') }}" required placeholder="Contoh: Proses tembak infusi minyak kayu selesai, aroma pekat merata..." class="w-full px-3 py-1.5 rounded-xl text-xs apple-input">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-900/5">
                            <!-- Lampiran Dokumen / Foto -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Lampiran Foto / Dokumen (Bisa Tambah Lebih Dari 1)</label>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="triggerAddNewAttachment()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold cursor-pointer transition shadow-xs">
                                            + Pilih Berkas / Foto
                                        </button>
                                        <span class="text-[10px] text-slate-400">Bisa memilih sekaligus atau bertahap</span>
                                    </div>
                                    <div id="dynamicFileInputsContainer"></div>
                                    <div id="attachmentsPreviewList" class="space-y-1.5"></div>
                                </div>
                            </div>

                            <!-- Tanda Tangan PIC Canvas -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Tanda Tangan PIC</label>
                                <div class="border border-slate-300 rounded-xl bg-white p-2 relative space-y-1">
                                    <canvas id="signatureCanvas" width="300" height="100" class="border border-slate-200 rounded-lg w-full cursor-crosshair touch-none bg-white"></canvas>
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] text-slate-400">Gunakan mouse atau layar sentuh</span>
                                        <button type="button" id="clearSignatureBtn" class="text-[10px] text-rose-600 hover:text-rose-800 font-semibold">Hapus Tanda Tangan</button>
                                    </div>
                                    <input type="hidden" name="signature_data" id="signatureData">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs">
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
                        <div class="p-4 rounded-2xl bg-white/70 border border-slate-200/80 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-100 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-900">
                                        {{ $log->log_date->format('d M Y') }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-md text-[10px] font-semibold bg-slate-900 text-white shadow-xs">
                                        {{ $log->stage }}
                                    </span>
                                    @if($log->work_status === 'selesai')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Selesai
                                        </span>
                                    @elseif($log->work_status === 'ulang')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            Ulang
                                        </span>
                                    @elseif($log->work_status === 'tertunda')
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            Tertunda
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-500">
                                    Oleh: <strong class="text-slate-800">{{ $log->pic_name }}</strong>
                                </span>
                            </div>
                            
                            <p class="text-xs text-slate-700 leading-relaxed">
                                {{ $log->notes }}
                            </p>

                            @php $attachmentList = $log->attachment_paths; @endphp
                            @if(!empty($attachmentList) || $log->signature_path)
                                <div class="flex flex-wrap items-center gap-4 pt-2 border-t border-slate-100">
                                    @if(!empty($attachmentList))
                                        <div>
                                            <span class="text-[10px] font-semibold text-slate-400 block mb-1">Lampiran Berkas ({{ count($attachmentList) }}):</span>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @foreach($attachmentList as $filePath)
                                                    @if(Str::endsWith(strtolower($filePath), ['.png', '.jpg', '.jpeg', '.webp']))
                                                        <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="inline-block border border-slate-200 rounded-xl overflow-hidden shadow-xs hover:opacity-90 transition">
                                                            <img src="{{ asset('storage/' . $filePath) }}" alt="Lampiran Log" class="h-14 w-auto object-cover">
                                                        </a>
                                                    @else
                                                        <a href="{{ asset('storage/' . $filePath) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-300 transition">
                                                            Lihat Berkas
                                                        </a>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($log->signature_path)
                                        <div>
                                            <span class="text-[10px] font-semibold text-slate-400 block mb-1">Tanda Tangan PIC:</span>
                                            <div class="p-1 bg-white border border-slate-200 rounded-xl shadow-xs inline-block">
                                                @php
                                                    $sigSrc = \Illuminate\Support\Str::startsWith($log->signature_path, ['data:image', 'http://', 'https://']) 
                                                        ? $log->signature_path 
                                                        : (\Illuminate\Support\Str::startsWith($log->signature_path, 'storage/') 
                                                            ? asset($log->signature_path) 
                                                            : asset('storage/' . $log->signature_path));
                                                @endphp
                                                <img src="{{ $sigSrc }}" alt="Tanda Tangan PIC" class="h-10 w-auto object-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[10px] text-slate-400 italic px-2\'>Berkas belum terunggah di server</span>';">
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
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

<script>
const availableMaterials = @json($materials ?? []);
let logRowIndex = 0;

function addMaterialRowLog(selectedMatId = null, qty = 1) {
    const container = document.getElementById('materialRowsContainerLog');
    if (!container) return;
    const rowId = `mat_log_row_${logRowIndex}`;

    let optionsHtml = '<option value="">-- Pilih Bahan Baku --</option>';
    availableMaterials.forEach(m => {
        const isSelected = selectedMatId == m.id ? 'selected' : '';
        optionsHtml += `<option value="${m.id}" data-unit="${m.unit}" data-stock="${m.stock_quantity}" ${isSelected}>[${m.code}] ${m.name}</option>`;
    });

    const rowHtml = `
        <tr id="${rowId}" class="hover:bg-white/60 transition">
            <td class="py-2 px-2">
                <select name="materials[${logRowIndex}][material_id]" required onchange="onMaterialSelectLog(this, '${rowId}')" class="w-full px-2.5 py-1.5 rounded-xl text-xs apple-input bg-white">
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2 px-2">
                <span id="${rowId}_stock" class="font-mono font-semibold text-slate-600 text-xs">-</span>
            </td>
            <td class="py-2 px-2">
                <div class="flex items-center gap-1.5">
                    <input type="number" step="0.01" min="0.01" name="materials[${logRowIndex}][quantity_used]" value="${qty}" required placeholder="0.00" class="w-24 px-2.5 py-1.5 rounded-xl text-xs apple-input font-mono">
                    <span id="${rowId}_unit" class="text-xs text-slate-500 font-semibold font-sans">-</span>
                </div>
            </td>
            <td class="py-2 px-2 text-center">
                <button type="button" onclick="removeMaterialRowLog('${rowId}')" class="p-1 text-slate-400 hover:text-rose-600 text-sm font-bold">
                    ✕
                </button>
            </td>
        </tr>
    `;

    container.insertAdjacentHTML('beforeend', rowHtml);

    const newSelect = document.querySelector(`#${rowId} select`);
    if (selectedMatId && newSelect) {
        onMaterialSelectLog(newSelect, rowId);
    }

    logRowIndex++;
}

function removeMaterialRowLog(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
    }
}

function onMaterialSelectLog(select, rowId) {
    const option = select.options[select.selectedIndex];
    const stock = option ? option.getAttribute('data-stock') : '-';
    const unit = option ? option.getAttribute('data-unit') : '';

    const stockSpan = document.getElementById(`${rowId}_stock`);
    const unitSpan = document.getElementById(`${rowId}_unit`);

    if (stockSpan) {
        stockSpan.innerText = stock !== '-' ? `${parseFloat(stock).toFixed(1)} ${unit}` : '-';
    }
    if (unitSpan) {
        unitSpan.innerText = unit;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                document.getElementById('signatureData').value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        const clearBtn = document.getElementById('clearSignatureBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                document.getElementById('signatureData').value = '';
            });
        }
    }

    const stageSelect = document.getElementById('stageSelect');
    const workStatusSelect = document.querySelector('select[name="work_status"]');
    const actualQtyContainer = document.getElementById('actualQtyContainer');
    const workStatusContainer = document.getElementById('workStatusContainer');
    const bomAllocationContainer = document.getElementById('bomAllocationContainer');
    const tembakMetricsBox = document.getElementById('tembakMetricsBox');
    const jemurMetricsBox = document.getElementById('jemurMetricsBox');
    const vendorMetricsBox = document.getElementById('vendorMetricsBox');

    function checkStageState() {
        const stageVal = stageSelect ? stageSelect.value : '';

        // Actual quantity container (saat selesai)
        if (stageVal === 'Selesai') {
            actualQtyContainer?.classList.remove('hidden');
            workStatusContainer?.classList.add('hidden');
        } else {
            actualQtyContainer?.classList.add('hidden');
            workStatusContainer?.classList.remove('hidden');
        }

        // Tembak Metrics
        if (stageVal === 'Tembak') {
            tembakMetricsBox?.classList.remove('hidden');
            bomAllocationContainer?.classList.remove('hidden');
            if (document.querySelectorAll('#materialRowsContainerLog tr').length === 0) {
                addMaterialRowLog();
            }
        } else {
            tembakMetricsBox?.classList.add('hidden');
        }

        // Jemur Metrics
        if (stageVal.includes('Jemur')) {
            jemurMetricsBox?.classList.remove('hidden');
        } else {
            jemurMetricsBox?.classList.add('hidden');
        }

        // Vendor Bor Pak Kholil Metrics
        if (stageVal.includes('Bor') || stageVal.includes('Pak Kholil')) {
            vendorMetricsBox?.classList.remove('hidden');
        } else {
            vendorMetricsBox?.classList.add('hidden');
        }

        // Celup BOM allocation
        if (stageVal.includes('Celup')) {
            bomAllocationContainer?.classList.remove('hidden');
        }
    }

    if (stageSelect) stageSelect.addEventListener('change', checkStageState);
    if (workStatusSelect) workStatusSelect.addEventListener('change', checkStageState);
    checkStageState(); // Initial check on load
});

function selectProcessStage(stage) {
    const stageSelect = document.getElementById('stageSelect');
    if (!stageSelect) return;

    for (let i = 0; i < stageSelect.options.length; i++) {
        const optVal = stageSelect.options[i].value;
        if (optVal === stage || optVal.includes(stage) || stage.includes(optVal)) {
            stageSelect.selectedIndex = i;
            stageSelect.dispatchEvent(new Event('change'));
            break;
        }
    }

    const formSection = document.querySelector('form[action*="store-log"]');
    if (formSection) {
        formSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

let fileGroupCounter = 0;

function escapeJs(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

function triggerAddNewAttachment() {
    fileGroupCounter++;
    const groupId = `file_group_${fileGroupCounter}`;
    const container = document.getElementById('dynamicFileInputsContainer');
    if (!container) return;

    const input = document.createElement('input');
    input.type = 'file';
    input.name = 'attachments[]';
    input.multiple = true;
    input.accept = 'image/*,.pdf,.doc,.docx';
    input.className = 'hidden';
    input.id = groupId;

    input.addEventListener('change', function(e) {
        if (e.target.files && e.target.files.length > 0) {
            renderDynamicFilesPreview();
        } else {
            input.remove();
        }
    });

    container.appendChild(input);
    input.click();
}

function removeFileGroup(groupId) {
    const el = document.getElementById(groupId);
    if (el) {
        el.remove();
    }
    renderDynamicFilesPreview();
}

function renderDynamicFilesPreview() {
    const previewContainer = document.getElementById('attachmentsPreviewList');
    if (!previewContainer) return;
    previewContainer.innerHTML = '';

    const inputs = document.querySelectorAll('#dynamicFileInputsContainer input[type="file"]');
    if (inputs.length === 0) return;

    inputs.forEach(input => {
        const files = Array.from(input.files);
        files.forEach((file) => {
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            const itemHtml = `
                <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200 text-xs shadow-xs">
                    <div class="flex items-center gap-2 truncate pr-2">
                        <span class="font-bold text-slate-800 truncate">${escapeJs(file.name)}</span>
                        <span class="text-[10px] text-slate-400 font-mono">(${sizeMb} MB)</span>
                    </div>
                    <button type="button" onclick="removeFileGroup('${input.id}')" class="text-slate-400 hover:text-rose-600 font-bold text-sm">✕</button>
                </div>
            `;
            previewContainer.insertAdjacentHTML('beforeend', itemHtml);
        });
    });
}

function openAddMaterialModal() {
    const modal = document.getElementById('addMaterialModal');
    if (modal) modal.classList.remove('hidden');
}

function closeAddMaterialModal() {
    const modal = document.getElementById('addMaterialModal');
    if (modal) modal.classList.add('hidden');
}

function onBomSelectMaterial(select) {
    const opt = select.options[select.selectedIndex];
    const unit = opt ? opt.getAttribute('data-unit') : 'kg';
    const stock = opt ? opt.getAttribute('data-stock') : null;
    const unitBadge = document.getElementById('bom_unit_badge');
    const stockInfo = document.getElementById('bom_stock_info');

    unitBadge.innerText = unit || 'kg';

    if (stock !== null && select.value !== '') {
        stockInfo.innerText = `Sisa stok tersedia di gudang: ${parseFloat(stock).toFixed(2)} ${unit}`;
    } else {
        stockInfo.innerText = '';
    }
}
</script>
@endsection

@push('modals')
<!-- Modal Tambah Bahan ke Komposisi BOM -->
<div id="addMaterialModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <h3 class="text-sm font-bold text-slate-900">Tambah Bahan Baku ke BOM</h3>
            <button type="button" onclick="closeAddMaterialModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form action="{{ route('production.batches.add-material', $batch) }}" method="POST" class="mt-4 space-y-4">
            @csrf

            <!-- Pilih Bahan Baku Gudang -->
            <div>
                <label for="bom_material_id" class="block text-xs font-semibold text-slate-800 mb-1">
                    Pilih Bahan Baku <span class="text-red-500">*</span>
                </label>
                <select 
                    name="material_id" 
                    id="bom_material_id" 
                    required
                    onchange="onBomSelectMaterial(this)"
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 border-slate-300 font-medium"
                >
                    <option value="">-- Pilih Bahan Baku dari Gudang --</option>
                    @foreach($materials as $mat)
                        <option 
                            value="{{ $mat->id }}" 
                            data-unit="{{ $mat->unit }}" 
                            data-stock="{{ $mat->stock_quantity }}"
                            data-name="{{ $mat->name }}"
                        >
                            {{ $mat->name }} ({{ $mat->code }}) — Stok: {{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Kuantitas Alokasi Pemakaian -->
            <div>
                <label for="bom_quantity_used" class="block text-xs font-semibold text-slate-800 mb-1">
                    Kuantitas Dialokasikan <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        step="0.01" 
                        min="0.01" 
                        name="quantity_used" 
                        id="bom_quantity_used" 
                        required 
                        placeholder="0.00"
                        class="w-full pl-3 pr-16 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono font-bold"
                    >
                    <span 
                        id="bom_unit_badge" 
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-bold"
                    >
                        kg
                    </span>
                </div>
                <p id="bom_stock_info" class="text-[10px] text-slate-500 mt-1 font-medium"></p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button 
                    type="button" 
                    onclick="closeAddMaterialModal()" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm"
                >
                    Tambahkan ke BOM
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
