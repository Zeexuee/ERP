@extends('layouts.app', ['title' => 'Detail Proses Finishing'])

@section('content')
@php
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
    $initialSignatureUrl = $resolveSignatureUrl($finishingSession->signature_path);
    $completionSignatureUrl = $resolveSignatureUrl($finishingSession->completion_signature_path);
    $mappedSupportMaterials = $supportMaterials->map(fn ($material) => [
        'id' => $material->id,
        'label' => '['.$material->code.'] '.$material->name,
        'stock' => (float) $material->stock_quantity,
        'unit' => $material->unit,
        'cost' => (float) $material->unit_cost,
    ])->values();
@endphp

<div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">
                Finishing <span class="text-slate-500 font-mono">{{ $finishingSession->finishing_code }}</span>
            </h2>
            <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest
                {{ $finishingSession->isCompleted()
                    ? 'border-slate-900 bg-slate-900 text-white'
                    : 'border-amber-300 bg-amber-50 text-amber-800' }}">
                {{ $finishingSession->status_label }}
            </span>
            <span class="rounded-full border border-indigo-300 bg-indigo-50 px-2.5 py-1 font-mono text-[10px] font-bold text-indigo-900 uppercase tracking-widest">
                Branch {{ $finishingSession->branch_label }}
            </span>
        </div>
        <a href="{{ route('production.finishings.index') }}" class="self-start rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 sm:self-auto uppercase tracking-widest">
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
            <h3 class="border-b border-slate-900/10 pb-2 text-sm font-bold text-slate-900 uppercase tracking-widest">Informasi Sesi</h3>

            <dl class="space-y-3 text-sm text-slate-900">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanggal Mulai</dt>
                    <dd class="font-bold">{{ $finishingSession->finishing_date->format('d/m/Y') }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">PIC</dt>
                    <dd class="font-bold">{{ $finishingSession->pic_name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bahan Asal</dt>
                    <dd class="text-right font-bold">{{ $finishingSession->sourceMaterial?->name ?? '-' }}</dd>
                </div>
                @if($finishingSession->branch_is_merged)
                    <div class="space-y-1 rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                        <dt class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest">Branch Gabungan</dt>
                        <dd class="font-mono text-xs font-bold text-indigo-900">{{ implode(' + ', $finishingSession->source_branch_codes ?? []) }}</dd>
                    </div>
                @endif
                @if($finishingSession->notes)
                    <div class="space-y-1 pt-2">
                        <dt class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Catatan</dt>
                        <dd class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-900">{{ $finishingSession->notes }}</dd>
                    </div>
                @endif
            </dl>

            <div class="space-y-2 border-t border-slate-200 pt-4">
                <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda Tangan PIC</span>
                @if($initialSignatureUrl)
                    <div class="inline-block rounded-xl border border-slate-300 bg-white p-2">
                        <img src="{{ $initialSignatureUrl }}" alt="Tanda tangan inisiasi finishing" class="h-16 w-auto object-contain grayscale" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=&quot;px-2 text-[10px] font-bold text-red-600&quot;>Berkas tanda tangan tidak ditemukan</span>';">
                    </div>
                @else
                    <p class="text-xs font-bold italic text-slate-400">Tidak ada tanda tangan inisiasi.</p>
                @endif
            </div>
        </section>

        <section class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
            <h3 class="border-b border-slate-900/10 pb-2 text-sm font-bold text-slate-900 uppercase tracking-widest">Ringkasan Berat & Modal</h3>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Berat Masuk</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($finishingSession->initial_weight, 2) }} kg</strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Berat Terkini</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($finishingSession->current_weight, 2) }} kg</strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Susut</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($finishingSession->total_weight_loss, 2) }} kg</strong>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-widest">Ampas Tersimpan</span>
                    <strong class="mt-1 block font-mono text-lg text-slate-900">{{ number_format($finishingSession->total_waste_weight, 2) }} kg</strong>
                </div>
                <div class="col-span-2 rounded-2xl bg-slate-900 p-4 text-white">
                    <span class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest">Modal Bahan Pendukung</span>
                    <strong class="mt-1 block font-mono text-xl">Rp {{ number_format($finishingSession->total_material_cost, 0, ',', '.') }}</strong>
                    <span class="text-[10px] font-bold text-slate-400">{{ $finishingSession->steps->count() }} proses tercatat</span>
                </div>
            </div>
        </section>
    </div>

    @if($finishingSession->isCompleted())
        <section class="apple-glass-panel space-y-5 rounded-3xl p-6 shadow-md">
            <div class="flex flex-col gap-2 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Hasil Barang Jadi Siap Jual</h3>
                <span class="self-start rounded-full bg-slate-900 px-2.5 py-1 text-[10px] font-bold text-white uppercase tracking-widest">Selesai</span>
            </div>

            <div class="flex flex-col gap-3 rounded-2xl bg-slate-900 p-5 text-white sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Barang Jadi</span>
                    <strong class="mt-1 block text-base">
                        [{{ $finishingSession->product?->sku ?? '-' }}] {{ $finishingSession->product?->name ?? 'Data barang jadi tidak tersedia' }}
                    </strong>
                    <span class="mt-1 block font-mono text-[11px] font-bold text-indigo-200">
                        Branch {{ $finishingSession->productBranch?->branch_code ?? $finishingSession->branch_label }}
                    </span>
                </div>
                <div class="text-left sm:text-right">
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest">Masuk Stok</span>
                    <strong class="mt-1 block font-mono text-2xl">{{ number_format($finishingSession->output_quantity) }} {{ $finishingSession->product?->unit ?? 'unit' }}</strong>
                    <span class="text-[10px] font-bold text-slate-400">Berat akhir {{ number_format($finishingSession->output_weight, 2) }} kg</span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanggal Selesai</span>
                        <span class="font-bold text-slate-900">{{ $finishingSession->completed_date?->format('d/m/Y') ?? '-' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">PIC Penyelesaian</span>
                        <span class="font-bold text-slate-900">{{ $finishingSession->completion_pic_name ?? '-' }}</span>
                    </div>
                    @if($finishingSession->completion_notes)
                        <div class="space-y-1 border-t border-slate-100 pt-3">
                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Catatan</span>
                            <p class="text-xs text-slate-900">{{ $finishingSession->completion_notes }}</p>
                        </div>
                    @endif
                </div>

                <div class="space-y-2 rounded-2xl border border-slate-200 bg-white p-4">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda Tangan Penyelesaian</span>
                    @if($completionSignatureUrl)
                        <div class="inline-block rounded-xl border border-slate-300 bg-white p-2">
                            <img src="{{ $completionSignatureUrl }}" alt="Tanda tangan penyelesaian finishing" class="h-14 w-auto object-contain grayscale" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=&quot;px-2 text-[10px] font-bold text-red-600&quot;>Berkas tanda tangan tidak ditemukan</span>';">
                        </div>
                    @else
                        <p class="text-[10px] font-bold italic text-slate-400">Tidak tersedia.</p>
                    @endif
                </div>
            </div>
        </section>
    @else
        <section class="apple-glass-panel rounded-3xl p-6 shadow-md">
            <div class="mb-5 flex flex-col gap-2 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Catat Proses Finishing</h3>
                    <p class="mt-1 text-xs text-slate-500">Urutan proses bebas sesuai kebutuhan produk. Sesi hanya selesai bila admin menyatakannya.</p>
                </div>
                <span class="self-start rounded-full border border-slate-300 bg-white px-2.5 py-1 font-mono text-[10px] font-bold text-slate-700">
                    Berat saat ini {{ number_format($finishingSession->current_weight, 2) }} kg
                </span>
            </div>

            <form action="{{ route('production.finishings.store-step', $finishingSession) }}" method="POST" id="stepForm" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div>
                        <label for="process_type" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Jenis Proses</label>
                        <select id="process_type" name="process_type" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                            <option value="">Pilih proses</option>
                            @foreach($processTypes as $processType)
                                <option value="{{ $processType->value }}" @selected(old('process_type') === $processType->value)>
                                    {{ $processType->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="step_date" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanggal Proses</label>
                        <input id="step_date" type="date" name="step_date" value="{{ old('step_date', now()->toDateString()) }}" min="{{ ($finishingSession->steps->last()?->step_date ?? $finishingSession->finishing_date)->toDateString() }}" max="{{ now()->toDateString() }}" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                    </div>
                    <div>
                        <label for="weight_after" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Berat Setelah Proses</label>
                        <div class="relative">
                            <input id="weight_after" type="number" name="weight_after" value="{{ old('weight_after') }}" min="0.01" max="{{ $finishingSession->current_weight }}" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 pr-10 font-mono text-xs font-bold" required>
                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500">kg</span>
                        </div>
                    </div>
                    <div>
                        <label for="step_pic_name" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Nama PIC</label>
                        <input id="step_pic_name" type="text" name="pic_name" value="{{ old('pic_name', auth()->user()->name ?? '') }}" maxlength="100" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-300 bg-slate-50 p-4">
                    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <span class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Bahan Yang Dipakai</span>
                            <p class="mt-1 text-[11px] text-slate-500">Contohnya metanol pada proses celup. Harga tiap bahan otomatis tercatat.</p>
                        </div>
                        <button type="button" id="addStepMaterial" class="self-start rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 sm:self-auto uppercase tracking-widest">
                            Tambah Bahan
                        </button>
                    </div>

                    <div id="stepMaterialContainer" class="space-y-3"></div>

                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Modal Bahan Proses Ini</span>
                        <span id="stepMaterialTotal" class="font-mono text-sm font-bold text-slate-900">Rp 0</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-300 bg-slate-50 p-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="waste_weight" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Ampas Buangan</label>
                            <div class="relative">
                                <input id="waste_weight" type="number" name="waste_weight" value="{{ old('waste_weight', 0) }}" min="0" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 pr-10 font-mono text-xs font-bold">
                                <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500">kg</span>
                            </div>
                            <p class="mt-1 text-[10px] font-bold text-slate-400">Tidak boleh melebihi total susut proses ini.</p>
                        </div>
                        <div>
                            <label for="waste_destination_type" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Perlakuan Ampas</label>
                            <select id="waste_destination_type" name="waste_destination_type" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                                <option value="none" @selected(old('waste_destination_type', 'none') === 'none')>Catat saja — tidak disimpan ke gudang</option>
                                <option value="existing" @selected(old('waste_destination_type') === 'existing')>Gabungkan ke barang gudang yang sudah ada</option>
                                <option value="new" @selected(old('waste_destination_type') === 'new')>Simpan sebagai barang gudang baru</option>
                            </select>
                        </div>
                    </div>

                    <div id="wasteExistingPanel" class="mt-4 hidden">
                        <label for="waste_material_id" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Barang Gudang Tujuan</label>
                        <select id="waste_material_id" name="waste_material_id" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold">
                            <option value="">Pilih barang tujuan ampas</option>
                            @foreach($wasteMaterials as $material)
                                <option value="{{ $material->id }}" @selected((string) old('waste_material_id') === (string) $material->id)>
                                    [{{ $material->code }}] {{ $material->name }} — stok {{ number_format($material->stock_quantity, 2) }} kg
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="wasteNewPanel" class="mt-4 hidden grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="new_waste_name" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nama Barang Ampas Baru</label>
                            <input id="new_waste_name" type="text" name="new_waste_name" value="{{ old('new_waste_name') }}" maxlength="255" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" placeholder="Contoh: Ampas Molen Gaharu A">
                        </div>
                        <div>
                            <label for="new_waste_code" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kode Barang (Opsional)</label>
                            <input id="new_waste_code" type="text" name="new_waste_code" value="{{ old('new_waste_code') }}" maxlength="50" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold uppercase" placeholder="Otomatis bila kosong">
                        </div>
                        <div class="sm:col-span-2 text-[10px] font-bold text-slate-500">Jenis barang otomatis <span class="text-slate-900">Ampas Finishing</span>, satuan <span class="text-slate-900">kg</span>.</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 border-t border-slate-200 pt-5 lg:grid-cols-2">
                    <div>
                        <label for="step_notes" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Catatan Proses</label>
                        <textarea id="step_notes" name="notes" rows="4" maxlength="1000" class="apple-input w-full rounded-xl px-3 py-2 text-xs" placeholder="Kondisi mesin, hasil bentuk, atau catatan lainnya">{{ old('notes') }}</textarea>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3">
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan PIC Proses</label>
                            <button type="button" id="clearStepSignature" class="text-[10px] font-bold text-slate-500 transition hover:text-slate-900 uppercase tracking-widest">Hapus</button>
                        </div>
                        <div class="space-y-2 rounded-2xl border border-slate-300 bg-white p-3">
                            <div class="overflow-hidden rounded-xl border border-slate-300 bg-slate-50">
                                <canvas id="stepSignatureCanvas" width="680" height="300" class="h-[130px] w-full cursor-crosshair touch-none"></canvas>
                            </div>
                            <p class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">Opsional</p>
                        </div>
                        <input type="hidden" name="signature_data" id="stepSignatureData">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-dark rounded-xl px-6 py-3 text-xs font-bold shadow-md transition uppercase tracking-widest">
                        Simpan Proses
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Riwayat Proses & Perpindahan</h3>
                <p class="mt-1 text-xs text-slate-500">Seluruh proses tercatat berurutan sesuai waktu pengerjaannya.</p>
            </div>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold text-slate-600">{{ $finishingSession->steps->count() }} proses</span>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full min-w-[1000px] text-left text-xs text-slate-900">
                <thead class="bg-slate-100 text-[9px] font-bold text-slate-500 uppercase tracking-widest">
                    <tr>
                        <th class="px-3 py-3">Urutan & Proses</th>
                        <th class="px-3 py-3 text-right">Sebelum</th>
                        <th class="px-3 py-3 text-right">Setelah</th>
                        <th class="px-3 py-3 text-right">Susut</th>
                        <th class="px-3 py-3">Ampas</th>
                        <th class="px-3 py-3">Bahan Dipakai</th>
                        <th class="px-3 py-3">PIC & Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($finishingSession->steps as $step)
                        <tr>
                            <td class="px-3 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold">#{{ $step->sequence }}</span>
                                    <span class="rounded-full border border-slate-300 bg-slate-50 px-2 py-0.5 text-[9px] font-bold text-slate-800 uppercase tracking-widest">
                                        {{ $step->process_type->label() }}
                                    </span>
                                </div>
                                <div class="mt-1 text-[10px] font-bold text-slate-500">{{ $step->step_date->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-3 py-3 text-right font-mono font-bold">{{ number_format($step->weight_before, 2) }} kg</td>
                            <td class="px-3 py-3 text-right font-mono font-bold">{{ number_format($step->weight_after, 2) }} kg</td>
                            <td class="px-3 py-3 text-right">
                                <div class="font-mono font-bold text-red-700">-{{ number_format($step->weight_loss, 2) }} kg</div>
                                <div class="text-[9px] font-bold text-slate-400">Hilang {{ number_format($step->unrecovered_loss, 2) }} kg</div>
                            </td>
                            <td class="px-3 py-3">
                                @if((float) $step->waste_weight > 0)
                                    <div class="font-mono font-bold text-slate-900">{{ number_format($step->waste_weight, 2) }} kg</div>
                                    <div class="mt-1 text-[10px] font-bold text-slate-500">
                                        {{ $step->wasteMaterial?->name ?? 'Tidak masuk gudang' }}
                                    </div>
                                @else
                                    <span class="text-[10px] font-bold italic text-slate-400">Tidak ada</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                @forelse($step->stepMaterials as $stepMaterial)
                                    <div class="mb-1 last:mb-0">
                                        <div class="font-bold text-slate-900">{{ $stepMaterial->material?->name ?? '-' }}</div>
                                        <div class="font-mono text-[10px] font-bold text-slate-500">
                                            {{ number_format($stepMaterial->quantity, 2) }} {{ $stepMaterial->unit }} · Rp {{ number_format($stepMaterial->total_cost, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-[10px] font-bold italic text-slate-400">Tanpa bahan</span>
                                @endforelse
                                @if((float) $step->material_cost > 0)
                                    <div class="mt-1 border-t border-slate-100 pt-1 font-mono text-[10px] font-bold text-slate-900">
                                        Total Rp {{ number_format($step->material_cost, 0, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="max-w-xs px-3 py-3">
                                <div class="font-bold">{{ $step->pic_name }}</div>
                                @if($step->notes)
                                    <div class="mt-1 text-[10px] leading-relaxed text-slate-500">{{ $step->notes }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                Belum ada proses finishing yang dicatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if(! $finishingSession->isCompleted())
        <section class="apple-glass-panel space-y-5 rounded-3xl p-6 shadow-md">
            <div class="flex flex-col gap-2 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">Nyatakan Finishing Selesai</h3>
                    <p class="mt-1 text-xs text-slate-500">Hasilnya masuk stok barang jadi siap jual dengan identitas branch {{ $finishingSession->branch_label }}.</p>
                </div>
            </div>

            @if($finishingSession->steps->isEmpty())
                <p class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-bold text-amber-800">
                    Catat minimal satu proses finishing sebelum menyatakan sesi ini selesai.
                </p>
            @else
                <form action="{{ route('production.finishings.complete', $finishingSession) }}" method="POST" id="completionForm" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="completed_date" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanggal Selesai</label>
                            <input id="completed_date" type="date" name="completed_date" value="{{ old('completed_date', now()->toDateString()) }}" min="{{ ($finishingSession->steps->last()?->step_date ?? $finishingSession->finishing_date)->toDateString() }}" max="{{ now()->toDateString() }}" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        </div>
                        <div>
                            <label for="completion_pic_name" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Nama PIC Penyelesaian</label>
                            <input id="completion_pic_name" type="text" name="completion_pic_name" value="{{ old('completion_pic_name', auth()->user()->name ?? '') }}" maxlength="100" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                        </div>
                        <div>
                            <label for="output_quantity" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Jumlah Barang Jadi</label>
                            <input id="output_quantity" type="number" name="output_quantity" value="{{ old('output_quantity', max(1, (int) round((float) $finishingSession->current_weight))) }}" min="1" step="1" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold" required>
                            <p class="mt-1 text-[10px] font-bold text-slate-400">Berat akhir {{ number_format($finishingSession->current_weight, 2) }} kg tetap tercatat utuh.</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-900 bg-white p-4">
                        <label for="product_destination_type" class="mb-2 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tujuan Barang Jadi</label>
                        <select id="product_destination_type" name="product_destination_type" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                            <option value="">Pilih tujuan barang jadi</option>
                            <option value="existing" @selected(old('product_destination_type') === 'existing')>Tambahkan ke barang jadi yang sudah ada</option>
                            <option value="new" @selected(old('product_destination_type') === 'new')>Tambahkan sebagai barang jadi baru</option>
                        </select>

                        <div id="productExistingPanel" class="mt-4 hidden">
                            <label for="product_id" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Pilih Barang Jadi Katalog</label>
                            <select id="product_id" name="product_id" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold">
                                <option value="">Pilih barang jadi</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>
                                        [{{ $product->sku }}] {{ $product->name }} — stok {{ number_format($product->stock_quantity) }} {{ $product->unit }}
                                    </option>
                                @endforeach
                            </select>
                            @if($products->isEmpty())
                                <p class="mt-2 text-[10px] font-bold text-amber-700">Belum ada barang jadi di katalog. Pilih opsi barang jadi baru.</p>
                            @endif
                        </div>

                        <div id="productNewPanel" class="mt-4 hidden grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label for="new_product_name" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Nama Barang Jadi Baru</label>
                                <input id="new_product_name" type="text" name="new_product_name" value="{{ old('new_product_name') }}" maxlength="255" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" placeholder="Contoh: Gaharu Finishing Grade A">
                            </div>
                            <div>
                                <label for="new_product_sku" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">SKU (Opsional)</label>
                                <input id="new_product_sku" type="text" name="new_product_sku" value="{{ old('new_product_sku') }}" maxlength="100" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold uppercase" placeholder="Otomatis bila kosong">
                            </div>
                            <div>
                                <label for="new_product_price" class="mb-1 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Harga Jual</label>
                                <input id="new_product_price" type="number" name="new_product_price" value="{{ old('new_product_price') }}" min="0" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 border-t border-slate-200 pt-5 lg:grid-cols-2">
                        <div>
                            <label for="completion_notes" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Catatan Penyelesaian</label>
                            <textarea id="completion_notes" name="completion_notes" rows="4" maxlength="1000" class="apple-input w-full rounded-xl px-3 py-2 text-xs" placeholder="Catatan quality control atau kondisi akhir">{{ old('completion_notes') }}</textarea>
                        </div>

                        <div>
                            <div class="mb-1 flex items-center justify-between gap-3">
                                <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan Penyelesaian <span class="text-red-600">*</span></label>
                                <button type="button" id="clearCompletionSignature" class="text-[10px] font-bold text-slate-500 transition hover:text-slate-900 uppercase tracking-widest">Hapus</button>
                            </div>
                            <div class="space-y-2 rounded-2xl border border-slate-300 bg-white p-3">
                                <div class="overflow-hidden rounded-xl border border-slate-300 bg-slate-50">
                                    <canvas id="completionSignatureCanvas" width="680" height="300" class="h-[130px] w-full cursor-crosshair touch-none"></canvas>
                                </div>
                                <p id="completionSignatureHelp" class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda tangani untuk mengunci hasil finishing</p>
                            </div>
                            <input type="hidden" name="signature_data" id="completionSignatureData">
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-dark rounded-xl px-6 py-3 text-xs font-bold shadow-md transition uppercase tracking-widest">
                            Selesaikan & Masukkan Ke Barang Jadi
                        </button>
                    </div>
                </form>
            @endif
        </section>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const supportMaterials = @json($mappedSupportMaterials);

        const createSignaturePad = (canvasId, inputId, clearButtonId) => {
            const canvas = document.getElementById(canvasId);
            const input = document.getElementById(inputId);

            if (!canvas || !input) {
                return null;
            }

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

            document.getElementById(clearButtonId)?.addEventListener('click', () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
                input.value = '';
                hasSignature = false;
            });

            return {
                hasSignature: () => hasSignature,
                commit: () => {
                    if (hasSignature) {
                        input.value = canvas.toDataURL('image/png');
                    }
                },
            };
        };

        // Formulir catat proses: bahan pendukung dinamis + tanda tangan opsional.
        const stepForm = document.getElementById('stepForm');
        const stepSignature = createSignaturePad('stepSignatureCanvas', 'stepSignatureData', 'clearStepSignature');
        const materialContainer = document.getElementById('stepMaterialContainer');
        const materialTotal = document.getElementById('stepMaterialTotal');
        let materialRowIndex = 0;

        const refreshMaterialTotal = () => {
            if (!materialContainer || !materialTotal) {
                return;
            }

            let total = 0;

            materialContainer.querySelectorAll('[data-material-row]').forEach((row) => {
                const select = row.querySelector('[data-material-select]');
                const quantityInput = row.querySelector('[data-material-quantity]');
                const subtotalLabel = row.querySelector('[data-material-subtotal]');
                const material = supportMaterials.find(
                    (item) => String(item.id) === String(select?.value)
                );
                const quantity = parseFloat(quantityInput?.value ?? '0') || 0;
                const subtotal = material ? quantity * material.cost : 0;

                if (material && quantityInput) {
                    quantityInput.max = material.stock;
                }

                if (subtotalLabel) {
                    subtotalLabel.textContent = material
                        ? `Stok ${material.stock.toFixed(2)} ${material.unit} · Rp ${Math.round(subtotal).toLocaleString('id-ID')}`
                        : 'Pilih bahan untuk melihat stok dan harga';
                }

                total += subtotal;
            });

            materialTotal.textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
        };

        const addMaterialRow = () => {
            if (!materialContainer) {
                return;
            }

            const index = materialRowIndex++;
            const row = document.createElement('div');
            row.dataset.materialRow = String(index);
            row.className = 'rounded-xl border border-slate-200 bg-white p-3';
            row.innerHTML = `
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_140px_auto] sm:items-end">
                    <div>
                        <label class="mb-1 block text-[9px] font-bold text-slate-500 uppercase tracking-widest" for="materials-${index}-material">Bahan</label>
                        <select id="materials-${index}-material" name="materials[${index}][material_id]" data-material-select class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold">
                            <option value="">Pilih bahan</option>
                            ${supportMaterials.map((material) => `<option value="${material.id}">${material.label}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-[9px] font-bold text-slate-500 uppercase tracking-widest" for="materials-${index}-quantity">Jumlah</label>
                        <input id="materials-${index}-quantity" type="number" name="materials[${index}][quantity]" data-material-quantity min="0.01" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 font-mono text-xs font-bold">
                    </div>
                    <button type="button" data-remove-material class="rounded-xl border border-slate-300 bg-white px-3 py-2 text-[10px] font-bold text-red-700 shadow-xs transition hover:bg-red-50 uppercase tracking-widest">Hapus</button>
                </div>
                <p data-material-subtotal class="mt-2 text-[10px] font-bold text-slate-400">Pilih bahan untuk melihat stok dan harga</p>
            `;

            materialContainer.appendChild(row);
            row.querySelector('[data-material-select]')?.addEventListener('change', refreshMaterialTotal);
            row.querySelector('[data-material-quantity]')?.addEventListener('input', refreshMaterialTotal);
            row.querySelector('[data-remove-material]')?.addEventListener('click', () => {
                row.remove();
                refreshMaterialTotal();
            });

            refreshMaterialTotal();
        };

        document.getElementById('addStepMaterial')?.addEventListener('click', addMaterialRow);

        const wasteDestination = document.getElementById('waste_destination_type');
        const wasteExistingPanel = document.getElementById('wasteExistingPanel');
        const wasteNewPanel = document.getElementById('wasteNewPanel');
        const wasteMaterialSelect = document.getElementById('waste_material_id');
        const newWasteName = document.getElementById('new_waste_name');

        const refreshWastePanels = () => {
            if (!wasteDestination) {
                return;
            }

            const showExisting = wasteDestination.value === 'existing';
            const showNew = wasteDestination.value === 'new';
            wasteExistingPanel?.classList.toggle('hidden', !showExisting);
            wasteNewPanel?.classList.toggle('hidden', !showNew);
            wasteNewPanel?.classList.toggle('grid', showNew);

            if (wasteMaterialSelect) {
                wasteMaterialSelect.required = showExisting;
            }

            if (newWasteName) {
                newWasteName.required = showNew;
            }
        };

        wasteDestination?.addEventListener('change', refreshWastePanels);
        refreshWastePanels();

        stepForm?.addEventListener('submit', () => stepSignature?.commit());

        // Formulir penyelesaian: tujuan barang jadi + tanda tangan wajib.
        const completionForm = document.getElementById('completionForm');
        const completionSignature = createSignaturePad('completionSignatureCanvas', 'completionSignatureData', 'clearCompletionSignature');
        const completionSignatureHelp = document.getElementById('completionSignatureHelp');
        const productDestination = document.getElementById('product_destination_type');
        const productExistingPanel = document.getElementById('productExistingPanel');
        const productNewPanel = document.getElementById('productNewPanel');
        const productSelect = document.getElementById('product_id');
        const newProductName = document.getElementById('new_product_name');
        const newProductPrice = document.getElementById('new_product_price');

        const refreshProductPanels = () => {
            if (!productDestination) {
                return;
            }

            const showExisting = productDestination.value === 'existing';
            const showNew = productDestination.value === 'new';
            productExistingPanel?.classList.toggle('hidden', !showExisting);
            productNewPanel?.classList.toggle('hidden', !showNew);
            productNewPanel?.classList.toggle('grid', showNew);

            if (productSelect) {
                productSelect.required = showExisting;
            }

            if (newProductName) {
                newProductName.required = showNew;
            }

            if (newProductPrice) {
                newProductPrice.required = showNew;
            }
        };

        productDestination?.addEventListener('change', refreshProductPanels);
        refreshProductPanels();

        completionForm?.addEventListener('submit', (event) => {
            if (!completionSignature?.hasSignature()) {
                event.preventDefault();

                if (completionSignatureHelp) {
                    completionSignatureHelp.textContent = 'Tanda tangan penyelesaian wajib diisi';
                    completionSignatureHelp.classList.add('text-red-600');
                }

                return;
            }

            completionSignature.commit();
        });
    });
</script>
@endsection
