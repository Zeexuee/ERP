@extends('layouts.app', ['title' => 'Detail Sesi Tembak Kayu'])

@section('content')
@php
    $woods = $tembakBatch->materials->where('type', 'wood');
    $resins = $tembakBatch->materials->where('type', 'resin');
    $totalInputCost = $tembakBatch->materials->sum(
        fn ($material) => (float) $material->weight * (float) $material->unit_cost
    );
    $totalResinWeight = (float) $resins->sum('weight');
    $currentDryingWeight = $tembakBatch->current_drying_weight;
    $resolveSignatureUrl = static function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        if (\Illuminate\Support\Str::startsWith($path, ['data:image', 'http://', 'https://'])) {
            return $path;
        }

        if (\Illuminate\Support\Str::startsWith($path, 'storage/')) {
            return asset($path);
        }

        return asset('storage/'.ltrim($path, '/'));
    };
    $initialSignatureUrl = $resolveSignatureUrl($tembakBatch->signature_path);
    $reportSignatureUrl = $resolveSignatureUrl($tembakBatch->report_signature_path);
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">
                Sesi Tembak <span class="text-slate-500 font-mono">{{ $tembakBatch->tembak_code }}</span>
            </h2>
            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest
                {{ $tembakBatch->isCompleted()
                    ? 'border-slate-900 bg-slate-900 text-white'
                    : ($tembakBatch->isDrying()
                        ? 'border-amber-300 bg-amber-50 text-amber-800'
                        : 'border-slate-300 bg-slate-100 text-slate-900') }}">
                {{ $tembakBatch->status_label }}
            </span>
        </div>
        <a href="{{ route('production.tembaks.index') }}" class="self-start rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 sm:self-auto uppercase tracking-widest">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800 shadow-xs">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 shadow-xs">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 shadow-xs">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <section class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
            <h3 class="border-b border-slate-900/10 pb-2 text-sm font-bold text-slate-900 uppercase tracking-widest">Informasi Inisiasi</h3>

            <dl class="space-y-3 text-sm text-slate-900">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanggal</dt>
                    <dd class="font-bold">{{ $tembakBatch->tembak_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">PIC</dt>
                    <dd class="font-bold">{{ $tembakBatch->pic_name }}</dd>
                </div>
                @if($tembakBatch->notes)
                    <div class="space-y-1 pt-2">
                        <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Catatan</dt>
                        <dd class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-900">{{ $tembakBatch->notes }}</dd>
                    </div>
                @endif
            </dl>

            <div class="space-y-2 border-t border-slate-200 pt-4">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda Tangan Inisiasi</span>
                @if($initialSignatureUrl)
                    <div class="inline-block rounded-xl border border-slate-300 bg-white p-2">
                        <img src="{{ $initialSignatureUrl }}" alt="Tanda tangan inisiasi" class="h-16 w-auto object-contain grayscale" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=&quot;px-2 text-[10px] font-bold text-red-600&quot;>Berkas tanda tangan tidak ditemukan</span>';">
                    </div>
                @else
                    <p class="text-xs font-bold italic text-slate-400">Tidak ada tanda tangan inisiasi.</p>
                @endif
            </div>
        </section>

        <section class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
            <div class="flex items-center justify-between gap-3 border-b border-slate-900/10 pb-2">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Bahan Digunakan</h3>
                <span class="font-mono text-sm font-bold text-slate-900">Rp {{ number_format($totalInputCost, 0, ',', '.') }}</span>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="space-y-2">
                    <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bahan Tembak</h4>
                    @foreach($woods as $material)
                        <div class="rounded-xl border border-slate-200 bg-slate-100 p-3 text-xs">
                            <div class="font-bold text-slate-900">{{ $material->material->name }}</div>
                            <div class="mt-1 flex items-center justify-between gap-2 font-mono text-[11px] font-bold text-slate-500">
                                <span>{{ number_format($material->weight, 2) }} {{ $material->material->unit }}</span>
                                <span>Rp {{ number_format($material->weight * $material->unit_cost, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2">
                    <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Getah</h4>
                    @foreach($resins as $material)
                        <div class="rounded-xl border border-slate-200 bg-slate-100 p-3 text-xs">
                            <div class="font-bold text-slate-900">{{ $material->material->name }}</div>
                            <div class="mt-1 flex items-center justify-between gap-2 font-mono text-[11px] font-bold text-slate-500">
                                <span>{{ number_format($material->weight, 2) }} {{ $material->material->unit }}</span>
                                <span>Rp {{ number_format($material->weight * $material->unit_cost, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <section class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="mb-5 flex flex-col gap-2 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Laporan Hasil Tembak</h3>
                <p class="mt-1 text-xs text-slate-500">Catat hasil tembak dan perlakuan getah sisa sebelum proses jemur dimulai.</p>
            </div>
            @if(! $tembakBatch->isInProgress())
                <span class="self-start rounded-full bg-slate-900 px-2.5 py-1 text-[10px] font-bold text-white uppercase tracking-widest">Tersimpan</span>
            @endif
        </div>

        @if($tembakBatch->isInProgress())
            <form action="{{ route('production.tembaks.store-report', $tembakBatch) }}" method="POST" id="reportForm" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div class="rounded-2xl border border-slate-300 bg-white p-4">
                        <label for="wet_result_weight" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Hasil Tembak / Berat Awal Jemur</label>
                        <div class="relative">
                            <input id="wet_result_weight" type="number" name="wet_result_weight" value="{{ old('wet_result_weight') }}" min="0.01" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 pr-10 font-mono text-sm font-bold" required>
                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500">kg</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4">
                        <span class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Total Getah Dipakai</span>
                        <div class="font-mono text-2xl font-bold text-slate-900"><span id="usedResinDisplay">{{ number_format($totalResinWeight - (float) old('residual_resin_weight', 0), 2) }}</span> <span class="text-xs text-slate-500">kg</span></div>
                        <p class="mt-1 text-[10px] font-bold text-slate-400">Dihitung otomatis: Total awal ({{ number_format($totalResinWeight, 2) }} kg) dikurangi sisa getah.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4">
                        <label for="residual_resin_weight" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Sisa Getah</label>
                        <div class="relative">
                            <input id="residual_resin_weight" type="number" name="residual_resin_weight" value="{{ old('residual_resin_weight', 0) }}" min="0" max="{{ $totalResinWeight }}" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 pr-10 font-mono text-sm font-bold">
                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500">kg</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-300 bg-slate-50 p-4">
                    <label for="residual_destination_type" class="mb-2 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Perlakuan Sisa Getah</label>
                    <select id="residual_destination_type" name="residual_destination_type" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        <option value="none" @selected(old('residual_destination_type', 'none') === 'none')>Catat saja — tidak dimasukkan ke stok gudang</option>
                        <option value="existing" @selected(old('residual_destination_type') === 'existing')>Gabungkan ke barang Getah yang sudah ada</option>
                        <option value="new" @selected(old('residual_destination_type') === 'new')>Buat barang Getah baru di gudang</option>
                    </select>

                    <div id="residualExistingPanel" class="mt-4 hidden">
                        <label for="existing_residual_material_id" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Barang Getah Tujuan</label>
                        <select id="existing_residual_material_id" name="existing_residual_material_id" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold">
                            <option value="">Pilih barang Getah</option>
                            @foreach($residualMaterials as $material)
                                <option value="{{ $material->id }}" @selected((string) old('existing_residual_material_id') === (string) $material->id)>
                                    [{{ $material->code }}] {{ $material->name }} — stok {{ number_format($material->stock_quantity, 2) }} kg
                                </option>
                            @endforeach
                        </select>
                        @if($residualMaterials->isEmpty())
                            <p class="mt-2 text-[10px] font-bold text-amber-700">Belum ada barang berkategori Getah dengan satuan kg. Pilih opsi barang baru.</p>
                        @endif
                    </div>

                    <div id="residualNewPanel" class="mt-4 hidden grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="new_residual_name" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nama Barang Getah Baru</label>
                            <input id="new_residual_name" type="text" name="new_residual_name" value="{{ old('new_residual_name') }}" maxlength="255" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" placeholder="Contoh: Getah Sisa Tembak A">
                        </div>
                        <div>
                            <label for="new_residual_code" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kode Barang (Opsional)</label>
                            <input id="new_residual_code" type="text" name="new_residual_code" value="{{ old('new_residual_code') }}" maxlength="50" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold uppercase" placeholder="Otomatis bila kosong">
                        </div>
                        <div class="sm:col-span-2 text-[10px] font-bold text-slate-500">Jenis barang otomatis <span class="text-slate-900">Getah</span>, satuan <span class="text-slate-900">kg</span>.</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 border-t border-slate-200 pt-5 lg:grid-cols-2">
                    <div class="space-y-4">
                        <div>
                            <label for="report_date" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanggal Laporan</label>
                            <input id="report_date" type="date" name="report_date" value="{{ old('report_date', now()->toDateString()) }}" min="{{ $tembakBatch->tembak_date->toDateString() }}" max="{{ now()->toDateString() }}" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        </div>
                        <div>
                            <label for="report_pic_name" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Nama PIC Laporan</label>
                            <input id="report_pic_name" type="text" name="report_pic_name" value="{{ old('report_pic_name', auth()->user()->name ?? '') }}" maxlength="100" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        </div>
                        <div>
                            <label for="report_notes" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Catatan Laporan</label>
                            <textarea id="report_notes" name="report_notes" rows="3" maxlength="1000" class="apple-input w-full rounded-xl px-3 py-2 text-xs" placeholder="Catatan hasil proses tembak">{{ old('report_notes') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan Pelapor <span class="text-red-600">*</span></label>
                            <button type="button" id="clearReportSignature" class="text-[10px] font-bold text-slate-500 transition hover:text-slate-900 uppercase tracking-widest">Hapus</button>
                        </div>
                        <div class="space-y-2 rounded-2xl border border-slate-300 bg-white p-3">
                            <div class="overflow-hidden rounded-xl border border-slate-300 bg-slate-50">
                                <canvas id="reportSignatureCanvas" width="680" height="300" class="h-[150px] w-full cursor-crosshair touch-none"></canvas>
                            </div>
                            <p id="signatureHelp" class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda tangani menggunakan mouse atau sentuhan jari</p>
                        </div>
                        <input type="hidden" name="signature_data" id="reportSignatureData">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-dark rounded-xl px-6 py-3 text-xs font-bold shadow-md transition uppercase tracking-widest">
                        Simpan & Mulai Jemur
                    </button>
                </div>
            </form>
        @else
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Hasil Tembak</span>
                        <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($tembakBatch->wet_result_weight, 2) }} kg</strong>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Getah Dipakai</span>
                        <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($totalResinWeight - (float) $tembakBatch->residual_resin_weight, 2) }} kg</strong>
                        <span class="mt-1 block text-[10px] font-bold text-slate-500">Dari total awal {{ number_format($totalResinWeight, 2) }} kg</span>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sisa Getah</span>
                        <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($tembakBatch->residual_resin_weight, 2) }} kg</strong>
                        <span class="mt-1 block text-[10px] font-bold text-slate-500">
                            {{ $tembakBatch->residualResinMaterial?->name ?? 'Tidak masuk stok' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanggal Laporan</span>
                        <span class="font-bold text-slate-900">{{ $tembakBatch->report_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">PIC Laporan</span>
                        <span class="font-bold text-slate-900">{{ $tembakBatch->report_pic_name ?? '-' }}</span>
                    </div>
                    @if($tembakBatch->report_notes)
                        <div class="space-y-1 border-t border-slate-100 pt-3">
                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Catatan</span>
                            <p class="text-xs text-slate-900">{{ $tembakBatch->report_notes }}</p>
                        </div>
                    @endif
                    <div class="space-y-2 border-t border-slate-100 pt-3">
                        <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda Tangan Laporan</span>
                        @if($reportSignatureUrl)
                            <div class="inline-block rounded-xl border border-slate-300 bg-white p-2">
                                <img src="{{ $reportSignatureUrl }}" alt="Tanda tangan laporan tembak" class="h-14 w-auto object-contain grayscale" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=&quot;px-2 text-[10px] font-bold text-red-600&quot;>Berkas tanda tangan tidak ditemukan</span>';">
                            </div>
                        @else
                            <p class="text-[10px] font-bold italic text-slate-400">Tidak tersedia pada data laporan lama.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </section>

    @if(! $tembakBatch->isInProgress())
        <section class="apple-glass-panel space-y-6 rounded-3xl p-6 shadow-md">
            <div class="flex flex-col gap-2 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Laporan Proses Jemur</h3>
                    <p class="mt-1 text-xs text-slate-500">Setiap timbang disimpan sebagai riwayat susut dan tidak mengubah stok sebelum proses selesai.</p>
                </div>
                <span class="self-start rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest {{ $tembakBatch->isCompleted() ? 'bg-slate-900 text-white' : 'border border-amber-300 bg-amber-50 text-amber-800' }}">
                    {{ $tembakBatch->isCompleted() ? 'Jemur Selesai' : 'Sedang Dijemur' }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Berat Awal</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($tembakBatch->wet_result_weight, 2) }} kg</strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Getah Dipakai</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($totalResinWeight - (float) $tembakBatch->residual_resin_weight, 2) }} kg</strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Berat Terkini</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($currentDryingWeight, 2) }} kg</strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Susut</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($tembakBatch->total_shrinkage_weight, 2) }} kg</strong>
                    <span class="text-[10px] font-bold text-slate-500">{{ number_format($tembakBatch->shrinkage_percentage, 2) }}%</span>
                </div>
                <div class="col-span-2 rounded-2xl bg-slate-900 p-4 text-white lg:col-span-1">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Durasi Jemur</span>
                    <strong class="mt-1 block font-mono text-lg">{{ number_format($tembakBatch->drying_duration_days) }} hari</strong>
                    <span class="text-[10px] font-bold text-slate-400">Sejak {{ $tembakBatch->report_date?->format('d/m/Y') }}</span>
                </div>
            </div>

            @if($tembakBatch->isCompleted())
                <div class="flex flex-col gap-3 rounded-2xl bg-slate-900 p-5 text-white sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Barang Baku Hasil Jemur</span>
                        <strong class="mt-1 block text-base">[{{ $tembakBatch->outputMaterial?->code ?? '-' }}] {{ $tembakBatch->outputMaterial?->name ?? 'Data barang tidak tersedia' }}</strong>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Berat Akhir Masuk Gudang</span>
                        <strong class="mt-1 block font-mono text-2xl">{{ number_format($tembakBatch->dried_result_weight, 2) }} kg</strong>
                    </div>
                </div>
            @else
                <form action="{{ route('production.tembaks.store-drying-log', $tembakBatch) }}" method="POST" id="dryingForm" class="space-y-5 rounded-2xl border border-slate-300 bg-slate-50 p-5">
                    @csrf
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Tambah Timbang Jemur</h4>
                            <p class="mt-1 text-[11px] text-slate-500">Berat tidak boleh lebih besar dari timbang sebelumnya.</p>
                        </div>
                        <span class="self-start rounded-full border border-slate-300 bg-white px-2.5 py-1 font-mono text-[10px] font-bold text-slate-700">Sebelumnya {{ number_format($currentDryingWeight, 2) }} kg</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="weighed_date" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanggal Timbang</label>
                            <input id="weighed_date" type="date" name="weighed_date" value="{{ old('weighed_date', now()->toDateString()) }}" min="{{ ($tembakBatch->dryingLogs->last()?->weighed_date ?? $tembakBatch->report_date)->toDateString() }}" max="{{ now()->toDateString() }}" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        </div>
                        <div>
                            <label for="new_weight" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Berat Setelah Jemur</label>
                            <div class="relative">
                                <input id="new_weight" type="number" name="new_weight" value="{{ old('new_weight') }}" min="0.01" max="{{ $currentDryingWeight }}" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 pr-10 font-mono text-xs font-bold" required>
                                <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500">kg</span>
                            </div>
                        </div>
                        <div>
                            <label for="drying_pic_name" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Nama PIC</label>
                            <input id="drying_pic_name" type="text" name="pic_name" value="" maxlength="100" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        </div>
                    </div>

                    <div>
                        <label for="drying_notes" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Catatan Sesi Jemur</label>
                        <textarea id="drying_notes" name="notes" rows="2" maxlength="1000" class="apple-input w-full rounded-xl px-3 py-2 text-xs" placeholder="Kondisi, cuaca, atau catatan timbang">{{ old('notes') }}</textarea>
                    </div>

                    <input type="hidden" name="is_completed" value="0">
                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-300 bg-white p-4">
                        <input id="isCompleted" type="checkbox" name="is_completed" value="1" @checked(old('is_completed') == 1) class="mt-0.5 size-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span>
                            <span class="block text-xs font-bold text-slate-900">Jemur telah selesai</span>
                            <span class="mt-1 block text-[11px] text-slate-500">Berat ini menjadi hasil final dan langsung dimasukkan ke barang baku gudang.</span>
                        </span>
                    </label>

                    <div id="completionPanel" class="hidden space-y-4 rounded-2xl border border-slate-900 bg-white p-4">
                        <div>
                            <label for="destination_type" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tujuan Barang Baku</label>
                            <select id="destination_type" name="destination_type" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold">
                                <option value="">Pilih tujuan hasil jemur</option>
                                <option value="existing" @selected(old('destination_type') === 'existing')>Tambahkan ke barang baku yang sudah ada</option>
                                <option value="new" @selected(old('destination_type') === 'new')>Tambahkan sebagai barang baku baru</option>
                            </select>
                        </div>

                        <div id="outputExistingPanel" class="hidden">
                            <label for="output_material_id" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pilih Barang Baku Gudang</label>
                            <select id="output_material_id" name="output_material_id" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold">
                                <option value="">Pilih barang baku</option>
                                @foreach($outputMaterials as $material)
                                    <option value="{{ $material->id }}" @selected((string) old('output_material_id') === (string) $material->id)>
                                        [{{ $material->code }}] {{ $material->name }} — {{ number_format($material->stock_quantity, 2) }} kg
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="outputNewPanel" class="hidden grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="new_output_name" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nama Barang Baku Baru</label>
                                <input id="new_output_name" type="text" name="new_output_name" value="{{ old('new_output_name') }}" maxlength="255" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" placeholder="Nama hasil tembak kering">
                            </div>
                            <div>
                                <label for="new_output_code" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kode Barang (Opsional)</label>
                                <input id="new_output_code" type="text" name="new_output_code" value="{{ old('new_output_code') }}" maxlength="50" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold uppercase" placeholder="Otomatis bila kosong">
                            </div>
                            <div class="sm:col-span-2 text-[10px] font-bold text-slate-500">Jenis barang otomatis <span class="text-slate-900">Bahan Tembak</span>, satuan <span class="text-slate-900">kg</span>.</div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-dark rounded-xl px-6 py-3 text-xs font-bold shadow-md transition uppercase tracking-widest">
                            Simpan Laporan Jemur
                        </button>
                    </div>
                </form>
            @endif

            <div class="space-y-3">
                <div class="flex items-center justify-between gap-3">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Riwayat Susut Jemur</h4>
                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">{{ $tembakBatch->dryingLogs->count() }} sesi</span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="w-full min-w-[850px] text-left text-xs text-slate-900">
                        <thead class="bg-slate-100 text-[9px] font-bold text-slate-500 uppercase tracking-widest">
                            <tr>
                                <th class="px-3 py-3">Sesi / Tanggal</th>
                                <th class="px-3 py-3 text-right">Sebelum</th>
                                <th class="px-3 py-3 text-right">Setelah</th>
                                <th class="px-3 py-3 text-right">Susut</th>
                                <th class="px-3 py-3">Jarak Waktu</th>
                                <th class="px-3 py-3">PIC & Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @php $previousDryingDate = $tembakBatch->report_date; @endphp
                            @forelse($tembakBatch->dryingLogs as $log)
                                @php
                                    $intervalDays = $previousDryingDate ? (int) $previousDryingDate->diffInDays($log->weighed_date) : 0;
                                    $sessionShrinkagePercentage = (float) $log->previous_weight > 0
                                        ? ((float) $log->shrinkage_weight / (float) $log->previous_weight) * 100
                                        : 0;
                                    $previousDryingDate = $log->weighed_date;
                                @endphp
                                <tr>
                                    <td class="px-3 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold">#{{ $loop->iteration }}</span>
                                            @if($log->is_final)
                                                <span class="rounded-full bg-slate-900 px-2 py-0.5 text-[8px] font-bold text-white uppercase tracking-widest">Final</span>
                                            @endif
                                        </div>
                                        <div class="mt-1 text-[10px] font-bold text-slate-500">{{ $log->weighed_date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-3 py-3 text-right font-mono font-bold">{{ number_format($log->previous_weight, 2) }} kg</td>
                                    <td class="px-3 py-3 text-right font-mono font-bold">{{ number_format($log->new_weight, 2) }} kg</td>
                                    <td class="px-3 py-3 text-right">
                                        <div class="font-mono font-bold text-red-700">-{{ number_format($log->shrinkage_weight, 2) }} kg</div>
                                        <div class="text-[9px] font-bold text-slate-400">{{ number_format($sessionShrinkagePercentage, 2) }}%</div>
                                    </td>
                                    <td class="px-3 py-3 font-bold text-slate-600">{{ $intervalDays }} hari</td>
                                    <td class="max-w-xs px-3 py-3">
                                        <div class="font-bold">{{ $log->pic_name }}</div>
                                        @if($log->notes)
                                            <div class="mt-1 text-[10px] leading-relaxed text-slate-500">{{ $log->notes }}</div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                        {{ $tembakBatch->isCompleted()
                                            ? 'Data legacy selesai sebelum riwayat sesi jemur diterapkan.'
                                            : 'Belum ada timbang jemur yang dicatat.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const residualInput = document.getElementById('residual_resin_weight');
        const usedResinDisplay = document.getElementById('usedResinDisplay');
        const totalInitialResin = {{ (float) $totalResinWeight }};

        if (residualInput && usedResinDisplay) {
            const updateUsedResin = () => {
                const residual = parseFloat(residualInput.value) || 0;
                const used = Math.max(0, totalInitialResin - residual);
                usedResinDisplay.textContent = used.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            };
            residualInput.addEventListener('input', updateUsedResin);
        }

        const residualDestination = document.getElementById('residual_destination_type');
        const residualExistingPanel = document.getElementById('residualExistingPanel');
        const residualNewPanel = document.getElementById('residualNewPanel');
        const existingResidualMaterial = document.getElementById('existing_residual_material_id');
        const newResidualName = document.getElementById('new_residual_name');

        const updateResidualPanels = () => {
            if (!residualDestination) {
                return;
            }

            const showExisting = residualDestination.value === 'existing';
            const showNew = residualDestination.value === 'new';
            residualExistingPanel?.classList.toggle('hidden', !showExisting);
            residualNewPanel?.classList.toggle('hidden', !showNew);
            residualNewPanel?.classList.toggle('grid', showNew);

            if (existingResidualMaterial) {
                existingResidualMaterial.required = showExisting;
            }

            if (newResidualName) {
                newResidualName.required = showNew;
            }
        };

        residualDestination?.addEventListener('change', updateResidualPanels);
        updateResidualPanels();

        const reportForm = document.getElementById('reportForm');
        const canvas = document.getElementById('reportSignatureCanvas');
        const signatureInput = document.getElementById('reportSignatureData');
        const clearSignatureButton = document.getElementById('clearReportSignature');
        const signatureHelp = document.getElementById('signatureHelp');

        if (reportForm && canvas && signatureInput) {
            const context = canvas.getContext('2d');
            let isDrawing = false;
            let hasSignature = false;
            let previousPoint = null;

            context.strokeStyle = '#0f172a';
            context.lineWidth = 5;
            context.lineCap = 'round';
            context.lineJoin = 'round';

            const getPoint = (event) => {
                const bounds = canvas.getBoundingClientRect();

                return {
                    x: (event.clientX - bounds.left) * (canvas.width / bounds.width),
                    y: (event.clientY - bounds.top) * (canvas.height / bounds.height),
                };
            };

            canvas.addEventListener('pointerdown', (event) => {
                isDrawing = true;
                previousPoint = getPoint(event);
                canvas.setPointerCapture(event.pointerId);
                event.preventDefault();
            });

            canvas.addEventListener('pointermove', (event) => {
                if (!isDrawing || !previousPoint) {
                    return;
                }

                const currentPoint = getPoint(event);
                context.beginPath();
                context.moveTo(previousPoint.x, previousPoint.y);
                context.lineTo(currentPoint.x, currentPoint.y);
                context.stroke();
                previousPoint = currentPoint;
                hasSignature = true;
                signatureHelp?.classList.remove('text-red-600');
                event.preventDefault();
            });

            const stopDrawing = (event) => {
                if (isDrawing && canvas.hasPointerCapture(event.pointerId)) {
                    canvas.releasePointerCapture(event.pointerId);
                }

                isDrawing = false;
                previousPoint = null;
            };

            canvas.addEventListener('pointerup', stopDrawing);
            canvas.addEventListener('pointercancel', stopDrawing);

            clearSignatureButton?.addEventListener('click', () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
                hasSignature = false;
            });

            reportForm.addEventListener('submit', (event) => {
                if (!hasSignature) {
                    event.preventDefault();
                    signatureHelp.textContent = 'Tanda tangan pelapor wajib diisi';
                    signatureHelp.classList.add('text-red-600');
                    canvas.focus();

                    return;
                }

                signatureInput.value = canvas.toDataURL('image/png');
            });
        }

        const completionCheckbox = document.getElementById('isCompleted');
        const completionPanel = document.getElementById('completionPanel');
        const destinationType = document.getElementById('destination_type');
        const outputExistingPanel = document.getElementById('outputExistingPanel');
        const outputNewPanel = document.getElementById('outputNewPanel');
        const outputMaterial = document.getElementById('output_material_id');
        const newOutputName = document.getElementById('new_output_name');

        const updateOutputPanels = () => {
            if (!destinationType) {
                return;
            }

            const isCompleting = completionCheckbox?.checked ?? false;
            const showExisting = isCompleting && destinationType.value === 'existing';
            const showNew = isCompleting && destinationType.value === 'new';
            outputExistingPanel?.classList.toggle('hidden', !showExisting);
            outputNewPanel?.classList.toggle('hidden', !showNew);
            outputNewPanel?.classList.toggle('grid', showNew);

            destinationType.required = isCompleting;

            if (outputMaterial) {
                outputMaterial.required = showExisting;
            }

            if (newOutputName) {
                newOutputName.required = showNew;
            }
        };

        const updateCompletionPanel = () => {
            const isCompleting = completionCheckbox?.checked ?? false;
            completionPanel?.classList.toggle('hidden', !isCompleting);
            updateOutputPanels();
        };

        completionCheckbox?.addEventListener('change', updateCompletionPanel);
        destinationType?.addEventListener('change', updateOutputPanels);
        updateCompletionPanel();
    });
</script>
@endsection
