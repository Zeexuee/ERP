@extends('layouts.app', ['title' => 'Rincian Sortir #' . $sortBatch->sort_code])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Rincian Sortir #{{ $sortBatch->sort_code }}</h2>
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
                Inisiasi: {{ $sortBatch->sort_date->format('d/m/Y') }} • PIC: {{ $sortBatch->pic_name }}
                @if($sortBatch->isCompleted() && $sortBatch->report_date)
                    • Selesai: {{ $sortBatch->report_date->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.sorts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Kembali
            </a>
            <a href="{{ route('production.sorts.create') }}" class="px-3.5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs transition">
                Inisiasi Baru
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-semibold shadow-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Mentah Asal</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $sortBatch->sourceMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                Berat Awal: {{ number_format($sortBatch->initial_weight, 1) }} kg
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Hasil Sortir</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                @if($sortBatch->isCompleted())
                    {{ number_format($sortBatch->dried_weight, 1) }} kg
                @else
                    -
                @endif
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                @if($sortBatch->isCompleted())
                    Susut: {{ number_format($sortBatch->drying_loss_weight, 1) }} kg 
                    ({{ $sortBatch->initial_weight > 0 ? round(($sortBatch->drying_loss_weight / $sortBatch->initial_weight) * 100, 1) : 0 }}%)
                @else
                    -
                @endif
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                @if($sortBatch->isCompleted())
                    {{ $sortBatch->items->count() }} Grade
                @else
                    Menunggu Laporan
                @endif
            </div>
        </div>
    </div>

    @if(! $sortBatch->isCompleted())
        <!-- FORM LAPORAN HASIL SORTIR -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-6">
            <div class="border-b border-slate-900/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">Tahap 2</span>
                    <h3 class="text-base font-bold text-slate-900">
                        Laporan Hasil Sortir
                    </h3>
                </div>
            </div>

            <form action="{{ route('production.sorts.store-report', $sortBatch) }}" method="POST" id="reportSortForm" class="space-y-6">
                @csrf

                <div class="p-4 bg-white/60 border border-slate-200/80 rounded-2xl space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        Timbangan Akhir Sortir
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Berat Awal
                            </label>
                            <input 
                                type="text" 
                                value="{{ number_format($sortBatch->initial_weight, 1) }} kg" 
                                disabled 
                                class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-mono font-bold text-slate-600 cursor-not-allowed"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Hasil Timbang Sortir (kg)
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="dried_weight" 
                                id="driedWeightInput" 
                                value="{{ old('dried_weight') }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-xs font-mono font-bold" 
                                required
                            >
                        </div>

                        <div class="p-3 bg-white border border-slate-200 rounded-xl flex flex-col justify-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Susut Sortir</span>
                            <div class="text-base font-bold font-mono text-slate-900 mt-0.5" id="lossDisplay">
                                0.00 kg
                            </div>
                            <span class="text-[10px] font-mono text-slate-500" id="lossPercentageDisplay">
                                0.0%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-900/10 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                            Pembagian Hasil Sortir
                        </h4>

                        <div id="balanceBadge" class="px-3 py-1 rounded-xl text-xs font-bold font-mono border transition inline-block bg-slate-100 text-slate-800 border-slate-300">
                            Total Sortir: 0.00 / {{ number_format($sortBatch->initial_weight, 2) }} {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}
                        </div>
                    </div>

                    <div class="overflow-visible">
                        <table class="w-full text-left text-xs overflow-visible" id="itemsTable">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500 font-extrabold uppercase text-[10px]">
                                    <th class="py-2 px-2 w-1/2">Jenis Bahan (Pilih / Ketik Baru)</th>
                                    <th class="py-2 px-2 w-1/4">Hasil Timbang ({{ $sortBatch->sourceMaterial->unit ?? 'kg' }})</th>
                                    <th class="py-2 px-2">Catatan</th>
                                    <th class="py-2 px-2 text-center w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="sortItemsBody" class="divide-y divide-slate-100 overflow-visible">
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="addRow()" class="px-3 py-1.5 rounded-xl btn-dark text-xs font-semibold shadow-xs">
                                Tambah Baris
                            </button>
                            <button type="button" onclick="openQuickMaterialModal()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-xs font-semibold transition shadow-xs">
                                + Tambah Jenis Bahan Baru
                            </button>
                        </div>

                        <div class="text-xs text-slate-600 font-mono">
                            Total Terbagi: <strong id="totalAllocatedText" class="text-slate-900 font-bold">0.00 {{ $sortBatch->sourceMaterial->unit ?? 'kg' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-900/10 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggal Pelaporan
                            </label>
                            <input 
                                type="date" 
                                name="report_date" 
                                value="{{ old('report_date', date('Y-m-d')) }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                PIC
                            </label>
                            <input 
                                type="text" 
                                name="report_pic_name" 
                            
                                class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                                required
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Catatan
                            </label>
                            <textarea 
                                name="notes" 
                                rows="2" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">
                                Tanda Tangan Digital PIC
                            </label>
                            <button type="button" onclick="clearReportSignature()" class="text-[10px] text-slate-500 hover:text-slate-900 underline">
                                Hapus
                            </button>
                        </div>

                        <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                            <canvas id="reportSignatureCanvas" class="w-full h-36 cursor-crosshair touch-none block bg-white"></canvas>
                        </div>
                        <input type="hidden" name="report_signature_data" id="reportSignatureInput">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-900/10">
                    <button type="submit" id="submitReportBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                        Simpan Laporan Sortir
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- TABEL RINCIAN HASIL SORTIR -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Rincian Pembagian Hasil Sortir
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="border-b border-slate-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-3">Kode Bahan</th>
                            <th class="py-3 px-3">Nama Bahan</th>
                            <th class="py-3 px-3 text-right">Hasil Timbang</th>
                            <th class="py-3 px-3 text-right">Porsi (%)</th>
                            <th class="py-3 px-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php $totalHasil = 0; @endphp
                        @foreach($sortBatch->items as $item)
                            @php 
                                $totalHasil += $item->result_weight;
                                $porsi = $sortBatch->dried_weight > 0 ? round(($item->result_weight / $sortBatch->dried_weight) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-white/50 transition">
                                <td class="py-3 px-3 font-mono font-bold text-slate-600">
                                    {{ $item->targetMaterial->code ?? '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $item->targetMaterial->name ?? '-' }}</div>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 text-sm">
                                    {{ number_format($item->result_weight, 1) }} kg
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-600">
                                    {{ $porsi }}%
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $item->notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-900/20 font-bold bg-slate-50/50">
                            <td colspan="2" class="py-3 px-3 text-right uppercase text-[10px] tracking-wider text-slate-600">
                                Total:
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-900 text-sm">
                                {{ number_format($totalHasil, 1) }} kg
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-900">
                                100%
                            </td>
                            <td class="py-3 px-3 text-slate-600 text-[10px]">
                                Sesuai timbangan ({{ number_format($sortBatch->dried_weight, 1) }} kg)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Catatan</span>
                <p class="text-xs text-slate-700 leading-relaxed pt-1">
                    {{ $sortBatch->notes ?: '-' }}
                </p>
                <div class="text-[11px] text-slate-600 pt-2 border-t border-slate-100">
                    Petugas: <strong class="text-slate-800">{{ $sortBatch->report_pic_name ?: $sortBatch->pic_name }}</strong>
                </div>
            </div>

            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tanda Tangan PIC</span>
                <div class="pt-2 flex items-center gap-4">
                    @php
                        $sigToShow = $sortBatch->report_signature_path ?: $sortBatch->signature_path;
                    @endphp
                    @if($sigToShow)
                        <div class="p-2 bg-white border border-slate-200 rounded-2xl shadow-xs inline-block">
                            @php
                                $sigSrc = \Illuminate\Support\Str::startsWith($sigToShow, ['data:image', 'http://', 'https://']) 
                                    ? $sigToShow 
                                    : (\Illuminate\Support\Str::startsWith($sigToShow, 'storage/') 
                                        ? asset($sigToShow) 
                                        : asset('storage/' . $sigToShow));
                            @endphp
                            <img src="{{ $sigSrc }}" alt="Tanda Tangan PIC" class="h-16 w-auto object-contain">
                        </div>
                    @else
                        <span class="text-xs text-slate-400">-</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>

@if(! $sortBatch->isCompleted())
<!-- MODAL REGISTRASI BAHAN BARU ON THE FLY -->
<div id="quickMaterialModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="handleQuickModalBackdrop(event)">
    <div class="apple-glass-panel bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Tambah Jenis Bahan Baru</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Registrasi bahan baku baru langsung ke katalog gudang.</p>
            </div>
            <button type="button" onclick="closeQuickMaterialModal()" class="text-slate-400 hover:text-slate-700 font-bold text-sm p-1">
                ✕
            </button>
        </div>

        <form id="quickMaterialForm" onsubmit="submitQuickMaterial(event)" class="mt-4 space-y-3">
            <div id="quickMaterialError" class="hidden p-2.5 rounded-xl bg-slate-100 border border-slate-300 text-slate-800 text-xs font-semibold"></div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Nama Bahan Baku <span class="text-slate-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="quickMaterialName" 
                    required 
                    placeholder="Contoh: Kayu Tembak Grade A..." 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input font-medium"
                >
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Kode Bahan <span class="text-[10px] text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input 
                        type="text" 
                        id="quickMaterialCode" 
                        placeholder="Otomatis jika kosong" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono uppercase"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Kategori
                    </label>
                    <input 
                        type="text" 
                        id="quickMaterialCategory" 
                        list="quickCategoriesList" 
                        value="Kayu Tembak" 
                        placeholder="Kayu Tembak" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-medium"
                    >
                    <datalist id="quickCategoriesList">
                        @if(isset($categories))
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        @endif
                    </datalist>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Satuan <span class="text-[10px] text-slate-400 font-normal">(Terkunci: bahan asal)</span>
                    </label>
                    <input 
                        type="text" 
                        id="quickMaterialUnit" 
                        value="{{ strtolower($sortBatch->sourceMaterial->unit ?? 'kg') }}" 
                        readonly 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-slate-100/80 text-slate-700 font-bold cursor-not-allowed border-slate-300"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Estimasi Biaya / Unit (Rp)
                    </label>
                    <input 
                        type="number" 
                        step="100" 
                        id="quickMaterialCost" 
                        value="0" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono font-medium"
                    >
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-200 mt-4">
                <button 
                    type="button" 
                    onclick="closeQuickMaterialModal()" 
                    class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    id="submitQuickMaterialBtn" 
                    class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs transition"
                >
                    Simpan Bahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const materialsList = @json($materials);
    const initialWeight = {{ (float) $sortBatch->initial_weight }};
    const sourceUnit = "{{ strtolower($sortBatch->sourceMaterial->unit ?? 'kg') }}";

    const driedWeightInput = document.getElementById('driedWeightInput');
    const lossDisplay = document.getElementById('lossDisplay');
    const lossPercentageDisplay = document.getElementById('lossPercentageDisplay');
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
        document.getElementById('quickMaterialCode').value = '';
        document.getElementById('quickMaterialCategory').value = 'Kayu Tembak';
        document.getElementById('quickMaterialUnit').value = sourceUnit;
        document.getElementById('quickMaterialCost').value = '0';
        
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
        const code = document.getElementById('quickMaterialCode').value.trim();
        const category = document.getElementById('quickMaterialCategory').value.trim();
        const cost = parseFloat(document.getElementById('quickMaterialCost').value) || 0;

        if (!name) {
            errBox.textContent = 'Nama bahan baku wajib diisi.';
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
                category: category || 'Kayu Tembak',
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

            // 1. POSITION "TAMBAHKAN SEBAGAI BAHAN BARU" AT THE VERY TOP (Rule 7.1)
            const createItem = document.createElement('div');
            createItem.className = 'px-3.5 py-3 bg-slate-900/[0.04] hover:bg-slate-900/[0.08] text-slate-900 text-xs font-bold cursor-pointer border-b border-slate-900/10 flex items-center justify-between transition';
            if (q.length > 0) {
                createItem.innerHTML = `
                    <span>+ Daftarkan "<strong>${escapeHtml(q)}</strong>" sebagai Bahan Baru (${escapeHtml(sourceUnit)})</span>
                    <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Buat Cepat</span>
                `;
                createItem.addEventListener('click', () => {
                    createNewMaterialOnTheFly(q, row);
                });
            } else {
                createItem.innerHTML = `
                    <span>+ Tambah Jenis Bahan Baru ke Katalog (${escapeHtml(sourceUnit)})...</span>
                    <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Ketik Nama</span>
                `;
                createItem.addEventListener('click', () => {
                    searchInput.focus();
                });
            }
            dropdown.appendChild(createItem);

            // 2. BELOW TOP ITEM: RENDER MATCHING / EXISTING MATERIALS (MUST MATCH SOURCE UNIT)
            const matches = materialsList.filter(m => 
                (!sourceUnit || (m.unit && m.unit.toLowerCase() === sourceUnit.toLowerCase())) &&
                (!qLower || 
                (m.name && m.name.toLowerCase().includes(qLower)) || 
                (m.code && m.code.toLowerCase().includes(qLower)) ||
                (m.category && m.category.toLowerCase().includes(qLower)))
            );

            if (matches.length > 0) {
                matches.forEach(m => {
                    const item = document.createElement('div');
                    item.className = 'px-3.5 py-2.5 hover:bg-slate-900/[0.04] cursor-pointer flex items-center justify-between text-xs transition';
                    item.innerHTML = `
                        <div>
                            <span class="font-bold text-slate-900 block">${escapeHtml(m.name)}</span>
                            <span class="text-[10px] text-slate-500 font-mono">[${escapeHtml(m.code)}] • ${escapeHtml(m.category || 'Kayu Tembak')} (${escapeHtml(m.unit)})</span>
                        </div>
                        <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Pilih</span>
                    `;
                    item.addEventListener('click', () => {
                        selectMaterial(row, m);
                    });
                    dropdown.appendChild(item);
                });
            } else if (qLower.length > 0) {
                const noMatch = document.createElement('div');
                noMatch.className = 'px-3.5 py-2.5 text-xs text-slate-400 font-medium italic';
                noMatch.textContent = `Tidak ada bahan satuan ${sourceUnit} yang cocok. Klik opsi di atas untuk mendaftarkannya.`;
                dropdown.appendChild(noMatch);
            }

            row.style.zIndex = '40';
            dropdown.classList.remove('hidden');
        }

        searchInput.addEventListener('focus', () => {
            document.querySelectorAll('.material-dropdown-list').forEach(d => {
                if (d !== dropdown) d.classList.add('hidden');
            });
            document.querySelectorAll('.item-row').forEach(r => {
                if (r !== row) r.style.zIndex = 'auto';
            });
            renderMaterialResults(searchInput.value);
        });

        searchInput.addEventListener('input', () => {
            idInput.value = '';
            renderMaterialResults(searchInput.value);
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
                row.style.zIndex = 'auto';
                if (!idInput.value && searchInput.value) {
                    const valClean = searchInput.value.trim().toLowerCase();
                    const exact = materialsList.find(m => 
                        (m.unit && m.unit.toLowerCase() === sourceUnit.toLowerCase()) &&
                        (m.name.toLowerCase() === valClean || 
                        `[${m.code.toLowerCase()}] ${m.name.toLowerCase()} (${m.unit.toLowerCase()})` === valClean)
                    );
                    if (exact) {
                        selectMaterial(row, exact);
                    }
                }
            }
        });
    }

    function selectMaterial(row, m) {
        if (m.unit && sourceUnit && m.unit.toLowerCase() !== sourceUnit.toLowerCase()) {
            alert(`Satuan bahan [${m.code}] ${m.name} (${m.unit}) berbeda dengan satuan bahan baku asal (${sourceUnit}). Pembagian hasil sortir harus menggunakan satuan yang sama.`);
            return;
        }

        const searchInput = row.querySelector('.material-search-input');
        const idInput = row.querySelector('.material-id-input');
        const dropdown = row.querySelector('.material-dropdown-list');

        searchInput.value = `[${m.code}] ${m.name} (${m.unit})`;
        idInput.value = m.id;
        dropdown.classList.add('hidden');
        row.style.zIndex = 'auto';
        searchInput.classList.remove('ring-2', 'ring-slate-900');
    }

    function createNewMaterialOnTheFly(name, row) {
        const searchInput = row.querySelector('.material-search-input');
        const idInput = row.querySelector('.material-id-input');
        const dropdown = row.querySelector('.material-dropdown-list');

        searchInput.value = 'Mendaftarkan bahan baru...';
        dropdown.classList.add('hidden');
        row.style.zIndex = 'auto';

        fetch("{{ route('production.materials.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                name: name,
                category: 'Kayu Tembak',
                unit: sourceUnit,
                stock_quantity: 0,
                minimum_stock: 0,
                unit_cost: 0
            })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.message || 'Gagal menambahkan bahan baru.');
            }
            return data;
        })
        .then(data => {
            if (data.material) {
                materialsList.push(data.material);
                selectMaterial(row, data.material);
            } else {
                alert('Gagal menambahkan bahan baru.');
                searchInput.value = '';
                idInput.value = '';
            }
        })
        .catch(err => {
            alert(err.message || 'Terjadi kesalahan koneksi saat menyimpan bahan baru.');
            searchInput.value = '';
            idInput.value = '';
        });
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
        tr.className = 'item-row relative hover:bg-white/40 transition';
        tr.dataset.row = rowCount;
        tr.innerHTML = `
            <td class="py-2 px-2 relative">
                <div class="relative material-combobox-wrapper">
                    <input type="text" 
                           placeholder="Pilih atau cari jenis bahan..." 
                           autocomplete="off"
                           value="${escapeHtml(selectedMaterialName)}"
                           class="material-search-input w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold bg-white text-slate-900 border-slate-300">
                    <input type="hidden" 
                           name="items[${rowCount}][target_material_id]" 
                           value="${selectedId}" 
                           class="material-id-input" 
                           required>
                    
                    <!-- Liquid Glass Dropdown List -->
                    <div class="material-dropdown-list hidden absolute left-0 right-0 top-full mt-2 z-50 bg-white/[0.98] rounded-2xl border border-slate-300 shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-slate-900/10 max-h-60 overflow-y-auto divide-y divide-slate-900/5 min-w-[280px]"
                         style="backdrop-filter: blur(24px) saturate(180%); -webkit-backdrop-filter: blur(24px) saturate(180%);">
                    </div>
                </div>
            </td>
            <td class="py-2 px-2">
                <input 
                    type="number" 
                    step="0.01" 
                    name="items[${rowCount}][result_weight]" 
                    value="${weight}" 
                    class="w-full px-2.5 py-2 rounded-xl apple-input text-xs font-mono font-bold item-weight-input" 
                    oninput="recalculateBalance()" 
                    required
                >
            </td>
            <td class="py-2 px-2">
                <input 
                    type="text" 
                    name="items[${rowCount}][notes]" 
                    value="${note}" 
                    placeholder="Catatan..."
                    class="w-full px-2.5 py-2 rounded-xl apple-input text-xs"
                >
            </td>
            <td class="py-2 px-2 text-center">
                <button type="button" onclick="removeRow('${rowId}')" class="text-slate-400 hover:text-slate-900 font-bold text-xs p-1">
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

    function updateDryingLoss() {
        const dried = parseFloat(driedWeightInput.value) || 0;

        const loss = Math.max(0, initialWeight - dried);
        const percent = initialWeight > 0 ? ((loss / initialWeight) * 100).toFixed(1) : '0.0';

        lossDisplay.textContent = `${loss.toFixed(2)} ${sourceUnit}`;
        lossPercentageDisplay.textContent = `Susut: ${percent}%`;

        recalculateBalance();
    }

    function recalculateBalance() {
        const dried = parseFloat(driedWeightInput.value) || 0;
        let totalAllocated = 0;

        document.querySelectorAll('.item-weight-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            totalAllocated += val;
        });

        totalAllocatedText.textContent = `${totalAllocated.toFixed(2)} ${sourceUnit}`;

        const diff = Math.abs(totalAllocated - dried);

        if (dried > 0 && diff <= 0.1) {
            balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-slate-900 text-white border-slate-900';
            balanceBadge.innerHTML = `Balance: ${totalAllocated.toFixed(2)} / ${dried.toFixed(2)} ${sourceUnit}`;
        } else if (dried > 0) {
            balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-slate-100 text-slate-800 border-slate-300';
            const diffText = (totalAllocated - dried).toFixed(2);
            balanceBadge.innerHTML = `Selisih: ${diffText > 0 ? '+' : ''}${diffText} ${sourceUnit} (${totalAllocated.toFixed(2)} / ${dried.toFixed(2)} ${sourceUnit})`;
        } else {
            balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-slate-100 text-slate-800 border-slate-300';
            balanceBadge.innerHTML = `Total: ${totalAllocated.toFixed(2)} ${sourceUnit}`;
        }
    }

    if (driedWeightInput) {
        driedWeightInput.addEventListener('input', updateDryingLoss);
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(old('items'))
            const oldItems = @json(old('items'));
            Object.values(oldItems).forEach(item => {
                addRow(item.target_material_id || '', item.result_weight || '', item.notes || '');
            });
        @else
            addRow();
            addRow();
        @endif
        initReportCanvas();
    });

    let reportCanvas, reportCtx, isReportDrawing = false;

    function initReportCanvas() {
        reportCanvas = document.getElementById('reportSignatureCanvas');
        if (!reportCanvas) return;

        reportCtx = reportCanvas.getContext('2d');
        reportCanvas.width = reportCanvas.parentElement.clientWidth || 350;
        reportCanvas.height = 144;

        reportCtx.strokeStyle = '#0f172a';
        reportCtx.lineWidth = 2.5;
        reportCtx.lineCap = 'round';
        reportCtx.lineJoin = 'round';

        function getPos(e) {
            const rect = reportCanvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function start(e) {
            isReportDrawing = true;
            const pos = getPos(e);
            reportCtx.beginPath();
            reportCtx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!isReportDrawing) return;
            const pos = getPos(e);
            reportCtx.lineTo(pos.x, pos.y);
            reportCtx.stroke();
            e.preventDefault();
        }

        function end() {
            if (isReportDrawing) {
                isReportDrawing = false;
                document.getElementById('reportSignatureInput').value = reportCanvas.toDataURL('image/png');
            }
        }

        reportCanvas.addEventListener('mousedown', start);
        reportCanvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', end);

        reportCanvas.addEventListener('touchstart', start, { passive: false });
        reportCanvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', end);
    }

    function clearReportSignature() {
        if (!reportCtx) return;
        reportCtx.clearRect(0, 0, reportCanvas.width, reportCanvas.height);
        document.getElementById('reportSignatureInput').value = '';
    }

    document.getElementById('reportSortForm').addEventListener('submit', function (e) {
        let hasInvalidMaterial = false;
        let firstInvalidInput = null;

        document.querySelectorAll('.item-row').forEach(row => {
            const idInput = row.querySelector('.material-id-input');
            const searchInput = row.querySelector('.material-search-input');
            if (!idInput || !idInput.value) {
                hasInvalidMaterial = true;
                if (!firstInvalidInput && searchInput) {
                    firstInvalidInput = searchInput;
                }
                if (searchInput) {
                    searchInput.classList.add('ring-2', 'ring-slate-900');
                }
            }
        });

        if (hasInvalidMaterial) {
            e.preventDefault();
            alert('Silakan pilih atau daftarkan bahan baku yang valid untuk setiap baris hasil sortir.');
            if (firstInvalidInput) {
                firstInvalidInput.focus();
            }
            return false;
        }

        if (reportCanvas) {
            const blank = document.createElement('canvas');
            blank.width = reportCanvas.width;
            blank.height = reportCanvas.height;
            if (reportCanvas.toDataURL() !== blank.toDataURL()) {
                document.getElementById('reportSignatureInput').value = reportCanvas.toDataURL('image/png');
            }
        }
    });
</script>
@endif
@endsection
