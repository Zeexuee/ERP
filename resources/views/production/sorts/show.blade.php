@extends('layouts.app', ['title' => 'Rincian Sortir #' . $sortBatch->sort_code])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Rincian Sortir #{{ $sortBatch->sort_code }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                    Warehouse
                </span>
                @if($sortBatch->isCompleted())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                        Selesai
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                        Sedang Disortir
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-600 mt-1">
                Inisiasi: {{ $sortBatch->sort_date->format('d/m/Y') }}
                @if($sortBatch->isCompleted() && $sortBatch->report_date)
                    • Selesai: {{ $sortBatch->report_date->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.sorts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition">
                Kembali
            </a>
            <a href="{{ route('production.sorts.create') }}" class="px-3.5 py-2 rounded-xl btn-dark text-xs font-bold shadow-xs transition">
                + Inisiasi Baru
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-white border-2 border-slate-900 text-slate-900 text-xs font-semibold shadow-xs">
            <div class="font-bold mb-1 uppercase tracking-wider">Periksa Kembali Data Input:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Kartu Informasi Bahan & Status -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl bg-white border border-slate-300 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Bahan Mentah Asal</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $sortBatch->sourceMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono font-semibold text-slate-700 block mt-1">
                Berat Awal Diambil: {{ number_format($sortBatch->initial_weight, 1) }} kg
                @if($sortBatch->isCompleted() && $sortBatch->returned_weight > 0)
                    • Dikembalikan: {{ number_format($sortBatch->returned_weight, 1) }} kg
                @endif
            </span>
        </div>

        <div class="p-5 rounded-2xl bg-white border border-slate-300 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">
                {{ $sortBatch->isCompleted() ? 'Total Hasil Sortir' : 'Status Proses' }}
            </span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                @if($sortBatch->isCompleted())
                    {{ number_format($sortBatch->dried_weight, 1) }} kg <span class="text-xs text-slate-600 font-sans font-normal">({{ $sortBatch->items->count() }} Grade)</span>
                @else
                    Menunggu Laporan Hasil
                @endif
            </div>
        </div>
    </div>

    @if(! $sortBatch->isCompleted())
        <!-- FORM LAPORAN HASIL SORTIR -->
        <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-6">
            <div class="border-b border-slate-200 pb-3">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">Tahap 2</span>
                    <h3 class="text-base font-bold text-slate-900 uppercase tracking-wider">
                        Laporan Hasil Sortir Kayu
                    </h3>
                </div>
                <p class="text-xs text-slate-600 mt-1">
                    Bagi kayu hasil sortir ke kategori <strong>Bahan Tembak</strong> atau <strong>Bahan Potong Ukir</strong>. Kayu mentah yang tidak selesai disortir dapat dikembalikan ke stok gudang.
                </p>
            </div>

            <form action="{{ route('production.sorts.store-report', $sortBatch) }}" method="POST" id="reportSortForm" class="space-y-6">
                @csrf

                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-900">
                            Pembagian Hasil Sortir
                        </h4>

                        <div id="balanceBadge" class="px-3 py-1.5 rounded-xl text-xs font-bold font-mono border transition inline-block bg-slate-900 text-white border-slate-900">
                            Total Sortir: 0.00 / {{ number_format($sortBatch->initial_weight, 2) }} {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}
                        </div>
                    </div>

                    <div class="overflow-visible">
                        <table class="w-full text-left text-xs overflow-visible" id="itemsTable">
                            <thead>
                                <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                                    <th class="py-2.5 px-3 w-1/2">Jenis Bahan (Pilih / Ketik Baru)</th>
                                    <th class="py-2.5 px-3 w-1/4">Hasil Timbang ({{ $sortBatch->sourceMaterial->unit ?? 'kg' }})</th>
                                    <th class="py-2.5 px-3">Catatan</th>
                                    <th class="py-2.5 px-3 text-center w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="sortItemsBody" class="divide-y divide-slate-200 overflow-visible">
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" onclick="addRow()" class="px-3.5 py-2 rounded-xl btn-dark text-xs font-bold shadow-xs">
                            + Tambah Baris Hasil
                        </button>

                        <div class="text-xs text-slate-700 font-mono">
                            Total Terbagi: <strong id="totalAllocatedText" class="text-slate-900 font-bold">0.00 {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                                Tanggal Pelaporan <span class="text-slate-900">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="report_date" 
                                value="{{ old('report_date', $sortBatch->report_date?->format('Y-m-d') ?? date('Y-m-d')) }}" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-slate-900 focus:outline-none" 
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                                Penanggung Jawab Laporan (PIC) <span class="text-slate-900">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="report_pic_name" 
                                value="{{ old('report_pic_name', $sortBatch->report_pic_name) }}" 
                                placeholder="Nama Petugas Pelapor"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none" 
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                                Catatan Hasil Sortir (Opsional)
                            </label>
                            <textarea 
                                name="notes" 
                                rows="3" 
                                placeholder="Catatan kondisi kayu, kualitas, atau temuan saat pemilahan..."
                                class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-medium placeholder:text-slate-500 focus:border-slate-900 focus:outline-none"
                            >{{ old('notes', $sortBatch->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-300 space-y-3 shadow-xs">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-900">
                                Sisa Bahan Dikembalikan ke Gudang Mentah ({{ $sortBatch->sourceMaterial->unit ?? 'kg' }})
                            </label>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    min="0"
                                    name="returned_weight" 
                                    id="returnedWeightInput"
                                    value="{{ old('returned_weight', (float)($sortBatch->returned_weight ?? 0)) }}" 
                                    class="w-full px-3.5 py-2.5 pr-14 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-mono font-bold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none"
                                    placeholder="0.00"
                                >
                                <span class="absolute right-3.5 top-2.5 text-xs font-bold font-mono text-slate-700">{{ $sortBatch->sourceMaterial->unit ?? 'kg' }}</span>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Jika ada kayu mentah yang tidak selesai disortir dan dikembalikan ke gudang, masukkan beratnya di sini. Stok bahan mentah [{{ $sortBatch->sourceMaterial->code ?? '-' }}] otomatis bertambah kembali.
                            </p>

                            <!-- Ringkasan Live Mass Balance -->
                            <div class="pt-3 border-t border-slate-200 space-y-2 text-xs font-mono">
                                <div class="flex items-center justify-between text-slate-700">
                                    <span>Bahan Awal Diambil:</span>
                                    <span class="font-bold text-slate-900">{{ number_format($sortBatch->initial_weight, 2) }} {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-700">
                                    <span>Hasil Sortir Terbagi:</span>
                                    <span id="summaryAllocated" class="font-bold text-slate-900">0.00 {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}</span>
                                </div>
                                <div class="flex items-center justify-between text-slate-700">
                                    <span>Dikembalikan ke Gudang:</span>
                                    <span id="summaryReturned" class="font-bold text-slate-900">0.00 {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}</span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-300 bg-white p-2 rounded-xl">
                                    <span class="font-bold text-slate-900">Susut Proses Riil:</span>
                                    <span id="summaryLoss" class="font-bold text-slate-900">0.00 {{ $sortBatch->sourceMaterial->unit ?? 'kg' }} (0%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                    <button type="submit" name="action" value="draft" formnovalidate class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition">
                        Simpan & Lanjutkan Nanti
                    </button>
                    <button type="submit" name="action" value="complete" id="submitReportBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                        Simpan Laporan Sortir
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- TABEL RINCIAN HASIL SORTIR -->
        <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-4">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider border-b border-slate-200 pb-2">
                Rincian Pembagian Hasil Sortir
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3.5">Kode Bahan</th>
                            <th class="py-3 px-3.5">Nama Bahan</th>
                            <th class="py-3 px-3.5 text-right">Hasil Timbang</th>
                            <th class="py-3 px-3.5 text-right">Porsi (%)</th>
                            <th class="py-3 px-3.5">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php $totalHasil = 0; @endphp
                        @foreach($sortBatch->items as $item)
                            @php 
                                $totalHasil += $item->result_weight;
                                $porsi = $sortBatch->initial_weight > 0 ? round(($item->result_weight / $sortBatch->initial_weight) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3.5 font-mono font-bold text-slate-700">
                                    {{ $item->targetMaterial->code ?? '-' }}
                                </td>
                                <td class="py-3 px-3.5">
                                    <div class="font-bold text-slate-900">{{ $item->targetMaterial->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-bold text-slate-900 text-sm">
                                    {{ number_format($item->result_weight, 1) }} kg
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono font-semibold text-slate-700">
                                    {{ number_format($porsi, 1) }}%
                                </td>
                                <td class="py-3 px-3.5 text-slate-700">
                                    {{ $item->notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if($sortBatch->returned_weight > 0)
                            @php 
                                $returnedPersen = $sortBatch->initial_weight > 0 ? round(($sortBatch->returned_weight / $sortBatch->initial_weight) * 100, 1) : 0;
                            @endphp
                            <tr class="border-t border-slate-200 font-bold bg-slate-50 text-slate-900">
                                <td colspan="2" class="py-3 px-3.5 text-right uppercase text-[10px] tracking-wider">
                                    Dikembalikan ke Bahan Asal ({{ $sortBatch->sourceMaterial->name ?? 'KCB' }}):
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono text-sm">
                                    {{ number_format($sortBatch->returned_weight, 1) }} kg
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono">
                                    {{ number_format($returnedPersen, 1) }}%
                                </td>
                                <td class="py-3 px-3.5 text-[11px] text-slate-600 font-normal">
                                    (Masuk kembali ke stok gudang)
                                </td>
                            </tr>
                        @endif

                        @php 
                            $susutWeight = (float) $sortBatch->drying_loss_weight;
                            $susutPersen = $sortBatch->initial_weight > 0 ? round(($susutWeight / $sortBatch->initial_weight) * 100, 1) : 0;
                        @endphp
                        @if($susutWeight > 0)
                            <tr class="border-t border-slate-300 font-bold bg-slate-100 text-slate-900">
                                <td colspan="2" class="py-3 px-3.5 text-right uppercase text-[10px] tracking-wider">
                                    Penyusutan Riil (Susut):
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono text-sm">
                                    {{ number_format($susutWeight, 1) }} kg
                                </td>
                                <td class="py-3 px-3.5 text-right font-mono">
                                    {{ number_format($susutPersen, 1) }}%
                                </td>
                                <td class="py-3 px-3.5 text-[11px] text-slate-600 font-normal">
                                    (Selisih proses sortir)
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        @if($sortBatch->notes)
            <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Catatan Pelaporan</span>
                <p class="text-xs text-slate-800 leading-relaxed pt-1">
                    {{ $sortBatch->notes }}
                </p>
            </div>
        @endif
    @endif

</div>

@if(! $sortBatch->isCompleted())
<!-- ALERT MODALS -->
<div id="shortageWarningModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeShortageModal()">
    <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pembagian Belum Sesuai</h3>
                <p class="text-[11px] text-slate-600 mt-0.5">Harap periksa kembali total pembagian.</p>
            </div>
            <button type="button" onclick="closeShortageModal()" class="text-slate-400 hover:text-slate-900 font-bold text-sm p-1">✕</button>
        </div>
        <div class="mt-4">
            <p id="shortageWarningText" class="text-xs text-slate-800 mb-6 font-medium leading-relaxed"></p>
            <div class="flex flex-col gap-2">
                <button type="button" onclick="confirmShortageAndSubmit()" class="w-full py-2.5 rounded-xl btn-dark text-xs font-bold shadow-xs transition">Buang sebagai penyusutan</button>
                <button type="button" onclick="closeShortageModal()" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">Kembali masukan data</button>
            </div>
        </div>
    </div>
</div>

<div id="exceedWarningModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeExceedModal()">
    <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pembagian Melebihi Total</h3>
                <p class="text-[11px] text-slate-600 mt-0.5">Total pembagian tidak valid.</p>
            </div>
            <button type="button" onclick="closeExceedModal()" class="text-slate-400 hover:text-slate-900 font-bold text-sm p-1">✕</button>
        </div>
        <div class="mt-4">
            <p id="exceedWarningText" class="text-xs text-slate-800 mb-6 font-medium leading-relaxed"></p>
            <button type="button" onclick="closeExceedModal()" class="w-full py-2.5 rounded-xl btn-dark text-xs font-bold shadow-xs transition">Mengerti</button>
        </div>
    </div>
</div>

<div id="invalidMaterialModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeInvalidMaterialModal()">
    <div class="bg-white w-full max-w-sm rounded-2xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Bahan Baku Tidak Valid</h3>
                <p class="text-[11px] text-slate-600 mt-0.5">Harap lengkapi semua baris.</p>
            </div>
            <button type="button" onclick="closeInvalidMaterialModal()" class="text-slate-400 hover:text-slate-900 font-bold text-sm p-1">✕</button>
        </div>
        <div class="mt-4">
            <p class="text-xs text-slate-800 mb-6 font-medium leading-relaxed">Silakan pilih atau daftarkan bahan baku yang valid untuk setiap baris hasil sortir sebelum menyimpan laporan.</p>
            <button type="button" onclick="closeInvalidMaterialModal()" class="w-full py-2.5 rounded-xl btn-dark text-xs font-bold shadow-xs transition">Mengerti</button>
        </div>
    </div>
</div>

<!-- MODAL REGISTRASI BAHAN BARU ON THE FLY -->
<div id="quickMaterialModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="handleQuickModalBackdrop(event)">
    <div class="bg-white w-full max-w-md rounded-2xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Tambah Jenis Bahan Baru</h3>
                <p class="text-xs text-slate-600 mt-0.5">Registrasi bahan baku baru langsung ke katalog gudang.</p>
            </div>
            <button type="button" onclick="closeQuickMaterialModal()" class="text-slate-400 hover:text-slate-900 font-bold text-sm p-1">
                ✕
            </button>
        </div>

        <form id="quickMaterialForm" onsubmit="submitQuickMaterial(event)" class="mt-4 space-y-4">
            <div id="quickMaterialError" class="hidden p-3 rounded-xl bg-slate-100 border border-slate-400 text-slate-900 text-xs font-bold"></div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                    Nama Bahan Baku <span class="text-slate-900">*</span>
                </label>
                <input 
                    type="text" 
                    id="quickMaterialName" 
                    required 
                    placeholder="Contoh: Kayu Tembak Grade A..." 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none"
                >
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                    Kategori Bahan <span class="text-slate-900">*</span>
                </label>
                <select 
                    id="quickMaterialCategory" 
                    required 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-slate-900 focus:outline-none"
                >
                    <option value="">-- Pilih Kategori (Bahan Tembak / Bahan Potong Ukir) --</option>
                    <option value="Bahan Tembak">Bahan Tembak</option>
                    <option value="Bahan Potong Ukir">Bahan Potong Ukir</option>
                </select>
                <p class="text-[11px] text-slate-600 mt-1">Hasil sortir kayu khusus dialokasikan ke kategori Bahan Tembak atau Bahan Potong Ukir.</p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 mt-4">
                <button 
                    type="button" 
                    onclick="closeQuickMaterialModal()" 
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-100 transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitQuickMaterialBtn" 
                    class="px-4 py-2 rounded-xl btn-dark text-xs font-bold shadow-xs transition"
                >
                    Simpan Bahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const materialsList = @json($materials);
    const sourceMaterialId = {{ (int) $sortBatch->source_material_id }};
    const allowedCategories = ['bahan tembak', 'bahan potong ukir', 'bahan ukir potong'];
    const initialWeight = {{ (float) $sortBatch->initial_weight }};
    const sourceUnit = "{{ strtolower($sortBatch->sourceMaterial->unit ?? 'kg') }}";
    const sourceUnitCost = {{ (float) ($sortBatch->sourceMaterial->unit_cost ?? 0) }};

    const balanceBadge = document.getElementById('balanceBadge');
    const totalAllocatedText = document.getElementById('totalAllocatedText');
    const sortItemsBody = document.getElementById('sortItemsBody');

    let rowCount = 0;
    let activeRowForModal = null;

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    // --- QUICK REGISTER MODAL ---
    function openQuickMaterialModal(row = null) {
        activeRowForModal = row;
        const errBox = document.getElementById('quickMaterialError');
        if (errBox) {
            errBox.classList.add('hidden');
            errBox.textContent = '';
        }

        const prefillName = (row && row.querySelector('.material-search-input')) ? row.querySelector('.material-search-input').value.trim() : '';

        document.getElementById('quickMaterialName').value = prefillName;
        document.getElementById('quickMaterialCategory').value = '';
        
        const modal = document.getElementById('quickMaterialModal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => document.getElementById('quickMaterialName').focus(), 100);
        }
    }

    function closeQuickMaterialModal() {
        const modal = document.getElementById('quickMaterialModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        activeRowForModal = null;
    }

    function handleQuickModalBackdrop(event) {
        if (event.target.id === 'quickMaterialModal') {
            closeQuickMaterialModal();
        }
    }

    function submitQuickMaterial(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submitQuickMaterialBtn');
        const errBox = document.getElementById('quickMaterialError');
        
        const name = document.getElementById('quickMaterialName').value.trim();
        const code = null;
        const category = document.getElementById('quickMaterialCategory').value.trim();
        const cost = sourceUnitCost;

        if (!name) {
            errBox.textContent = 'Nama bahan baku wajib diisi.';
            errBox.classList.remove('hidden');
            return;
        }

        if (!category) {
            errBox.textContent = 'Pilih salah satu kategori bahan: Bahan Tembak atau Bahan Potong Ukir.';
            errBox.classList.remove('hidden');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
        errBox.classList.add('hidden');

        fetch("{{ route('production.materials.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                name: name,
                code: code || null,
                category: category,
                unit: sourceUnit,
                stock_quantity: 0,
                minimum_stock: 0,
                unit_cost: cost
            })
        })
        .then(async response => {
            const data = await response.json();
            if (!response.ok) {
                const errorMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal menyimpan bahan baku.');
                throw new Error(errorMsg);
            }
            return data;
        })
        .then(data => {
            if (data.material) {
                materialsList.push(data.material);
                
                if (activeRowForModal) {
                    selectMaterial(activeRowForModal, data.material);
                } else {
                    addRow(data.material.id);
                }
                
                closeQuickMaterialModal();
            } else {
                throw new Error('Respons data bahan tidak valid.');
            }
        })
        .catch(err => {
            errBox.textContent = err.message || 'Terjadi kesalahan sistem.';
            errBox.classList.remove('hidden');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan Bahan';
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('quickMaterialModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeQuickMaterialModal();
            }
            document.querySelectorAll('.material-dropdown-list').forEach(d => d.classList.add('hidden'));
            document.querySelectorAll('.item-row').forEach(r => r.style.zIndex = 'auto');
        }
    });

    // --- MATERIAL AUTOCOMPLETE & ON-THE-FLY REGISTER ---
    function bindMaterialCombobox(row) {
        const wrapper = row.querySelector('.material-combobox-wrapper');
        const searchInput = row.querySelector('.material-search-input');
        const idInput = row.querySelector('.material-id-input');
        const dropdown = row.querySelector('.material-dropdown-list');

        function renderMaterialResults(query) {
            dropdown.innerHTML = '';
            const q = (query || '').trim();
            const qLower = q.toLowerCase();

            // 1. POSITION "TAMBAHKAN SEBAGAI BAHAN BARU" AT THE VERY TOP
            const createItem = document.createElement('div');
            createItem.className = 'px-3.5 py-3 bg-slate-50 hover:bg-slate-100 text-slate-900 text-xs font-bold cursor-pointer border-b border-slate-200 flex items-center justify-between transition';
            if (q.length > 0) {
                createItem.innerHTML = `
                    <span>+ Daftarkan "<strong>${escapeHtml(q)}</strong>" sebagai Bahan Baru (${escapeHtml(sourceUnit)})</span>
                    <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full bg-slate-900 text-white font-bold">Buat Cepat</span>
                `;
                createItem.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openQuickMaterialModal(row);
                });
            } else {
                createItem.innerHTML = `
                    <span>+ Tambah Jenis Bahan Baru ke Katalog (${escapeHtml(sourceUnit)})...</span>
                    <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full bg-slate-900 text-white font-bold">Ketik Nama</span>
                `;
                createItem.addEventListener('click', () => {
                    searchInput.focus();
                });
            }
            dropdown.appendChild(createItem);

            // 2. BELOW TOP ITEM: RENDER MATCHING / EXISTING MATERIALS (EXCLUDING SOURCE MATERIAL & STRICT CATEGORY)
            const matches = materialsList.filter(m => 
                (parseInt(m.id) !== sourceMaterialId) &&
                (allowedCategories.includes((m.category || '').toLowerCase().trim())) &&
                (!sourceUnit || (m.unit && m.unit.toLowerCase() === sourceUnit.toLowerCase())) &&
                (!qLower || 
                (m.name && m.name.toLowerCase().includes(qLower)) || 
                (m.code && m.code.toLowerCase().includes(qLower)) ||
                (m.category && m.category.toLowerCase().includes(qLower)))
            );

            if (matches.length > 0) {
                matches.forEach(m => {
                    const item = document.createElement('div');
                    item.className = 'px-3.5 py-2.5 hover:bg-slate-50 cursor-pointer flex items-center justify-between text-xs text-slate-900 font-semibold transition';
                    item.innerHTML = `
                        <div>
                            <span class="font-bold text-slate-900">${escapeHtml(m.name)}</span>
                            <span class="text-[11px] text-slate-600 font-mono font-medium ml-1">(${escapeHtml(m.code || '-')})</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                            ${escapeHtml(m.category || 'Bahan Tembak')}
                        </span>
                    `;
                    item.addEventListener('click', (e) => {
                        e.stopPropagation();
                        selectMaterial(row, m);
                    });
                    dropdown.appendChild(item);
                });
            } else if (q.length > 0) {
                const emptyItem = document.createElement('div');
                emptyItem.className = 'px-3.5 py-2 text-slate-600 text-xs italic bg-white';
                emptyItem.textContent = 'Tidak ada jenis bahan yang cocok.';
                dropdown.appendChild(emptyItem);
            }

            dropdown.classList.remove('hidden');
            row.style.zIndex = '50';
        }

        searchInput.addEventListener('focus', () => {
            document.querySelectorAll('.material-dropdown-list').forEach(d => {
                if (d !== dropdown) d.classList.add('hidden');
            });
            renderMaterialResults(searchInput.value);
        });

        searchInput.addEventListener('input', () => {
            idInput.value = '';
            renderMaterialResults(searchInput.value);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = searchInput.value.trim();
                if (!q) return;

                const exactMatch = materialsList.find(m => 
                    parseInt(m.id) !== sourceMaterialId &&
                    allowedCategories.includes((m.category || '').toLowerCase().trim()) &&
                    m.name.toLowerCase() === q.toLowerCase()
                );
                if (exactMatch) {
                    selectMaterial(row, exactMatch);
                } else {
                    openQuickMaterialModal(row);
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
                row.style.zIndex = 'auto';
            }
        });
    }

    function selectMaterial(row, mat) {
        const searchInput = row.querySelector('.material-search-input');
        const idInput = row.querySelector('.material-id-input');
        const dropdown = row.querySelector('.material-dropdown-list');

        searchInput.value = `[${mat.code}] ${mat.name} (${mat.unit})`;
        idInput.value = mat.id;
        dropdown.classList.add('hidden');
        row.style.zIndex = 'auto';
        searchInput.classList.remove('ring-2', 'ring-slate-900');

        const weightInput = row.querySelector('.item-weight-input');
        if (weightInput) {
            weightInput.focus();
        }
    }

    function addRow(selectedId = '', weight = '', note = '') {
        rowCount++;
        const rowId = `item_row_${rowCount}`;

        let selectedMaterialName = '';
        if (selectedId) {
            const mat = materialsList.find(m => m.id == selectedId);
            if (mat) {
                selectedMaterialName = `[${mat.code}] ${mat.name} (${mat.unit})`;
            }
        }

        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.className = 'item-row relative hover:bg-slate-50 transition';
        tr.dataset.row = rowCount;
        tr.innerHTML = `
            <td class="py-2.5 px-3 relative">
                <div class="relative material-combobox-wrapper">
                    <input type="text" 
                           placeholder="Pilih atau cari jenis bahan..." 
                           autocomplete="off"
                           value="${escapeHtml(selectedMaterialName)}"
                           class="material-search-input w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none">
                    <input type="hidden" 
                           name="items[${rowCount}][target_material_id]" 
                           value="${selectedId}" 
                           class="material-id-input" 
                           required>
                    
                    <!-- Dropdown List -->
                    <div class="material-dropdown-list hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white rounded-2xl border border-slate-300 shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100 min-w-[280px]">
                    </div>
                </div>
            </td>
            <td class="py-2.5 px-3">
                <input 
                    type="number" 
                    step="0.01" 
                    name="items[${rowCount}][result_weight]" 
                    value="${weight}" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-mono font-bold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none item-weight-input" 
                    oninput="recalculateBalance()" 
                    required
                >
            </td>
            <td class="py-2.5 px-3">
                <input 
                    type="text" 
                    name="items[${rowCount}][notes]" 
                    value="${note}" 
                    placeholder="Catatan..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-medium placeholder:text-slate-500 focus:border-slate-900 focus:outline-none"
                >
            </td>
            <td class="py-2.5 px-3 text-center">
                <button type="button" onclick="removeRow('${rowId}')" class="text-slate-400 hover:text-slate-900 font-bold text-base px-2 py-1">
                    ✕
                </button>
            </td>
        `;

        sortItemsBody.appendChild(tr);
        bindMaterialCombobox(tr);
        recalculateBalance();
    }

    function removeRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            recalculateBalance();
        }
    }

    function recalculateBalance() {
        let totalAllocated = 0;
        document.querySelectorAll('.item-weight-input').forEach(input => {
            const val = parseFloat(input.value);
            if (!isNaN(val) && val > 0) {
                totalAllocated += val;
            }
        });

        const retInput = document.getElementById('returnedWeightInput');
        const returnedWeight = retInput ? (parseFloat(retInput.value) || 0) : 0;
        const totalProcessedAndReturned = totalAllocated + returnedWeight;
        const loss = initialWeight - totalProcessedAndReturned;
        const lossPct = initialWeight > 0 ? (loss / initialWeight) * 100 : 0;

        if (totalAllocatedText) {
            totalAllocatedText.textContent = `${totalAllocated.toFixed(2)} ${sourceUnit}`;
        }

        const sumAllocated = document.getElementById('summaryAllocated');
        const sumReturned = document.getElementById('summaryReturned');
        const sumLoss = document.getElementById('summaryLoss');

        if (sumAllocated) sumAllocated.textContent = `${totalAllocated.toFixed(2)} ${sourceUnit}`;
        if (sumReturned) sumReturned.textContent = `${returnedWeight.toFixed(2)} ${sourceUnit}`;
        if (sumLoss) {
            sumLoss.textContent = `${loss.toFixed(2)} ${sourceUnit} (${lossPct.toFixed(1)}%)`;
        }

        if (balanceBadge) {
            if (totalProcessedAndReturned > initialWeight) {
                balanceBadge.className = 'px-3 py-1.5 rounded-xl text-xs font-bold font-mono border bg-slate-100 text-slate-900 border-slate-400';
                balanceBadge.innerHTML = `Total: ${totalProcessedAndReturned.toFixed(2)} / ${initialWeight.toFixed(2)} ${sourceUnit} (Melebihi Awal!)`;
            } else {
                balanceBadge.className = 'px-3 py-1.5 rounded-xl text-xs font-bold font-mono border bg-slate-900 text-white border-slate-900';
                balanceBadge.innerHTML = `Hasil: ${totalAllocated.toFixed(2)} + Kembali: ${returnedWeight.toFixed(2)} = ${(totalProcessedAndReturned).toFixed(2)} / ${initialWeight.toFixed(2)} ${sourceUnit}`;
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(old('items'))
            const oldItems = @json(old('items'));
            Object.values(oldItems).forEach(item => {
                addRow(item.target_material_id || '', item.result_weight || '', item.notes || '');
            });
        @elseif($sortBatch->items->isNotEmpty())
            @foreach($sortBatch->items as $savedItem)
                addRow('{{ $savedItem->target_material_id }}', '{{ (float)$savedItem->result_weight }}', '{{ addslashes($savedItem->notes ?? '') }}');
            @endforeach
        @else
            addRow();
        @endif

        const retInput = document.getElementById('returnedWeightInput');
        if (retInput) {
            retInput.addEventListener('input', recalculateBalance);
        }

        recalculateBalance();
    });

    document.getElementById('reportSortForm').addEventListener('submit', function (e) {
        const submitter = e.submitter || document.activeElement;
        const isDraft = (submitter && submitter.value === 'draft');

        if (isDraft) {
            // Jika memilih Simpan & Lanjutkan Nanti, langsung submit tanpa memunculkan modal validasi
            return true;
        }

        let hasInvalidMaterial = false;
        let firstInvalidInput = null;
        let totalAllocated = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const idInput = row.querySelector('.material-id-input');
            const searchInput = row.querySelector('.material-search-input');
            const weightInput = row.querySelector('.item-weight-input');
            
            if (weightInput) {
                totalAllocated += (parseFloat(weightInput.value) || 0);
            }

            if (!idInput || !idInput.value || parseInt(idInput.value) === sourceMaterialId) {
                hasInvalidMaterial = true;
                if (!firstInvalidInput && searchInput) {
                    firstInvalidInput = searchInput;
                }
                if (searchInput) {
                    searchInput.classList.add('ring-2', 'ring-slate-900');
                }
            }
        });

        const returnedInput = document.getElementById('returnedWeightInput');
        const returnedWeight = returnedInput ? (parseFloat(returnedInput.value) || 0) : 0;
        const totalCombined = totalAllocated + returnedWeight;

        if (hasInvalidMaterial) {
            e.preventDefault();
            window._firstInvalidInputToFocus = firstInvalidInput;
            document.getElementById('invalidMaterialModal').classList.remove('hidden');
            return false;
        }

        if (totalCombined > initialWeight) {
            e.preventDefault();
            document.getElementById('exceedWarningText').innerHTML = `Total pembagian hasil (<strong>${totalAllocated.toFixed(2)} ${sourceUnit}</strong>) ditambah bahan dikembalikan (<strong>${returnedWeight.toFixed(2)} ${sourceUnit}</strong>) adalah <strong>${totalCombined.toFixed(2)} ${sourceUnit}</strong>, melebihi bahan awal (<strong>${initialWeight.toFixed(2)} ${sourceUnit}</strong>). Harap periksa kembali.`;
            document.getElementById('exceedWarningModal').classList.remove('hidden');
            return false;
        }

        if (totalCombined < initialWeight) {
            e.preventDefault();
            const lossVal = (initialWeight - totalCombined).toFixed(2);
            document.getElementById('shortageWarningText').innerHTML = `Total hasil sortir (<strong>${totalAllocated.toFixed(2)} ${sourceUnit}</strong>) + dikembalikan (<strong>${returnedWeight.toFixed(2)} ${sourceUnit}</strong>) adalah <strong>${totalCombined.toFixed(2)} ${sourceUnit}</strong>.<br><br>Selisih <strong>${lossVal} ${sourceUnit}</strong> akan dicatat otomatis sebagai <strong>susut proses sortir</strong>. Lanjutkan simpan?`;
            document.getElementById('shortageWarningModal').classList.remove('hidden');
            return false;
        }
    });

    function closeExceedModal() {
        document.getElementById('exceedWarningModal').classList.add('hidden');
    }

    function closeShortageModal() {
        document.getElementById('shortageWarningModal').classList.add('hidden');
    }

    function confirmShortageAndSubmit() {
        document.getElementById('shortageWarningModal').classList.add('hidden');
        HTMLFormElement.prototype.submit.call(document.getElementById('reportSortForm'));
    }

    function closeInvalidMaterialModal() {
        document.getElementById('invalidMaterialModal').classList.add('hidden');
        if (window._firstInvalidInputToFocus) {
            window._firstInvalidInputToFocus.focus();
            window._firstInvalidInputToFocus = null;
        }
    }
</script>
@endif
@endsection
