@extends('layouts.app', ['title' => 'Rincian Potong Ukir #' . $carveBatch->carve_code])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Rincian Potong Ukir #{{ $carveBatch->carve_code }}</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Warehouse
                </span>
                @if($carveBatch->isCompleted())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                        Selesai
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                        Sedang Dipotong ukir
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-600 mt-1">
                Inisiasi: {{ $carveBatch->carve_date->format('d/m/Y') }}
                @if($carveBatch->isCompleted() && $carveBatch->report_date)
                    • Selesai: {{ $carveBatch->report_date->format('d/m/Y') }}
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.carves.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Kembali
            </a>
            <a href="{{ route('production.carves.create') }}" class="px-3.5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs transition">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Mentah Asal</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $carveBatch->sourceMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                Berat Awal: {{ number_format($carveBatch->initial_weight, 1) }} kg
            </span>
        </div>


        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                @if($carveBatch->isCompleted())
                    {{ $carveBatch->items->count() }} Grade
                @else
                    Menunggu Laporan
                @endif
            </div>
        </div>
    </div>

    @if(! $carveBatch->isCompleted())
        <!-- FORM LAPORAN HASIL SORTIR -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-6">
            <div class="border-b border-slate-900/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">Tahap 2</span>
                    <h3 class="text-base font-bold text-slate-900">
                        Laporan Hasil Potong Ukir
                    </h3>
                </div>
            </div>

            <form action="{{ route('production.carves.store-report', $carveBatch) }}" method="POST" id="reportCarveForm" class="space-y-6">
                @csrf


                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-900/10 pb-2">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                            Pembagian Hasil Potong Ukir
                        </h4>

                        <div id="balanceBadge" class="px-3 py-1 rounded-xl text-xs font-bold font-mono border transition inline-block bg-slate-100 text-slate-800 border-slate-300">
                            Total Potong Ukir: 0.00 / {{ number_format($carveBatch->initial_weight, 2) }} {{ $carveBatch->sourceMaterial->unit ?? 'kg' }}
                        </div>
                    </div>

                    <div class="overflow-visible">
                        <table class="w-full text-left text-xs overflow-visible" id="itemsTable">
                            <thead>
                                <tr class="border-b border-slate-200 text-slate-500 font-extrabold uppercase text-[10px]">
                                    <th class="py-2 px-2 w-1/2">Jenis Bahan (Pilih / Ketik Baru)</th>
                                    <th class="py-2 px-2 w-1/4">Hasil Timbang ({{ $carveBatch->sourceMaterial->unit ?? 'kg' }})</th>
                                    <th class="py-2 px-2">Catatan</th>
                                    <th class="py-2 px-2 text-center w-12">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="carveItemsBody" class="divide-y divide-slate-100 overflow-visible">
                            </tbody>
                        </table>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="addRow()" class="px-3 py-1.5 rounded-xl btn-dark text-xs font-semibold shadow-xs">
                                Tambah Baris
                            </button>
                        </div>

                        <div class="text-xs text-slate-600 font-mono">
                            Total Terbagi: <strong id="totalAllocatedText" class="text-slate-900 font-bold">0.00 {{ $carveBatch->sourceMaterial->unit ?? 'kg' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-900/10 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggal Pelaporan
                            </label>
                            <input 
                                type="date" 
                                name="report_date" 
                                value="{{ old('report_date') }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                                required
                            >
                        </div>

                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Penanggung Jawab Laporan (PIC)
                            </label>
                            <input 
                                type="text" 
                                name="report_pic_name" 
                                value="{{ old('report_pic_name') }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                                required
                            >
                        </div>

                        <div class="mt-4">
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
                </div>
                
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-900/10">
                    <button type="submit" id="submitReportBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                        Simpan Laporan Potong Ukir
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- TABEL RINCIAN HASIL SORTIR -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Rincian Pembagian Hasil Potong Ukir
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
                        @foreach($carveBatch->items as $item)
                            @php 
                                $totalHasil += $item->result_weight;
                                $porsi = $carveBatch->initial_weight > 0 ? round(($item->result_weight / $carveBatch->initial_weight) * 100, 1) : 0;
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
                                    {{ number_format($porsi, 1) }}%
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $item->notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if($carveBatch->initial_weight > $totalHasil)
                            @php 
                                $susutWeight = $carveBatch->initial_weight - $totalHasil;
                                $susutPersen = $carveBatch->initial_weight > 0 ? round(($susutWeight / $carveBatch->initial_weight) * 100, 1) : 0;
                            @endphp
                            <tr class="border-t-2 border-slate-900/10 font-bold bg-slate-50/50 text-slate-600">
                                <td colspan="2" class="py-3 px-3 text-right uppercase text-[10px] tracking-wider">
                                    Penyusutan (Susut):
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-sm">
                                    {{ number_format($susutWeight, 1) }} kg
                                </td>
                                <td class="py-3 px-3 text-right font-mono">
                                    {{ number_format($susutPersen, 1) }}%
                                </td>
                                <td class="py-3 px-3 text-[10px]">
                                    (Selisih bahan awal)
                                </td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Catatan</span>
                <p class="text-xs text-slate-700 leading-relaxed pt-1">
                    {{ $carveBatch->notes ?: '-' }}
                </p>

            </div>


        </div>
    @endif

</div>

@if(! $carveBatch->isCompleted())
<!-- ALERT MODALS -->
<div id="shortageWarningModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeShortageModal()">
    <div class="apple-glass-panel bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pembagian Belum Sesuai</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Harap periksa kembali total pembagian.</p>
            </div>
            <button type="button" onclick="closeShortageModal()" class="text-slate-400 hover:text-slate-700 font-bold text-sm p-1">✕</button>
        </div>
        <div class="mt-4">
            <p id="shortageWarningText" class="text-xs text-slate-700 mb-6 font-medium leading-relaxed"></p>
            <div class="flex flex-col gap-2">
                <button type="button" onclick="confirmShortageAndSubmit()" class="w-full py-2.5 rounded-xl btn-dark text-xs font-bold shadow-xs transition">Buang sebagai penyusutan</button>
                <button type="button" onclick="closeShortageModal()" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">Kembali masukan data</button>
            </div>
        </div>
    </div>
</div>

<div id="exceedWarningModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeExceedModal()">
    <div class="apple-glass-panel bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Pembagian Melebihi Total</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Total pembagian tidak valid.</p>
            </div>
            <button type="button" onclick="closeExceedModal()" class="text-slate-400 hover:text-slate-700 font-bold text-sm p-1">✕</button>
        </div>
        <div class="mt-4">
            <p id="exceedWarningText" class="text-xs text-slate-700 mb-6 font-medium leading-relaxed"></p>
            <button type="button" onclick="closeExceedModal()" class="w-full py-2.5 rounded-xl btn-dark text-xs font-bold shadow-xs transition">Mengerti</button>
        </div>
    </div>
</div>

<div id="invalidMaterialModal" class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4" onclick="closeInvalidMaterialModal()">
    <div class="apple-glass-panel bg-white w-full max-w-sm rounded-3xl p-6 shadow-2xl border border-slate-300 relative" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Bahan Baku Tidak Valid</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Harap lengkapi semua baris.</p>
            </div>
            <button type="button" onclick="closeInvalidMaterialModal()" class="text-slate-400 hover:text-slate-700 font-bold text-sm p-1">✕</button>
        </div>
        <div class="mt-4">
            <p class="text-xs text-slate-700 mb-6 font-medium leading-relaxed">Silakan pilih atau daftarkan bahan baku yang valid untuk setiap baris hasil potong ukir sebelum menyimpan laporan.</p>
            <button type="button" onclick="closeInvalidMaterialModal()" class="w-full py-2.5 rounded-xl btn-dark text-xs font-bold shadow-xs transition">Mengerti</button>
        </div>
    </div>
</div>

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

            <div class="relative overflow-visible">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Kategori <span class="text-slate-500">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="quickMaterialCategory" 
                        value="" 
                        autocomplete="off"
                        placeholder="Pilih / ketik kategori..." 
                        class="w-full px-3 py-2 pr-7 rounded-xl text-xs apple-input font-medium cursor-pointer"
                    >
                    <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
                <div id="quickCategoryDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"></div>
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
    const initialWeight = {{ (float) $carveBatch->initial_weight }};
    const sourceUnit = "{{ strtolower($carveBatch->sourceMaterial->unit ?? 'kg') }}";
    const sourceUnitCost = {{ (float) ($carveBatch->sourceMaterial->unit_cost ?? 0) }};

    const balanceBadge = document.getElementById('balanceBadge');
    const totalAllocatedText = document.getElementById('totalAllocatedText');
    const carveItemsBody = document.getElementById('carveItemsBody');

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
                createItem.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openQuickMaterialModal(row);
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
            alert(`Satuan bahan [${m.code}] ${m.name} (${m.unit}) berbeda dengan satuan bahan baku asal (${sourceUnit}). Pembagian hasil potong ukir harus menggunakan satuan yang sama.`);
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
                category: 'Bahan Susut',
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

        carveItemsBody.appendChild(tr);
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
            const val = parseFloat(input.value) || 0;
            totalAllocated += val;
        });

        totalAllocatedText.textContent = `${totalAllocated.toFixed(2)} ${sourceUnit}`;

        balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-slate-900 text-white border-slate-900';
        balanceBadge.innerHTML = `Total: ${totalAllocated.toFixed(2)} ${sourceUnit}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        @if(old('items'))
            const oldItems = @json(old('items'));
            Object.values(oldItems).forEach(item => {
                addRow(item.target_material_id || '', item.result_weight || '', item.notes || '');
            });
        @else
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

    document.getElementById('reportCarveForm').addEventListener('submit', function (e) {
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

        if (reportCanvas) {
            const blank = document.createElement('canvas');
            blank.width = reportCanvas.width;
            blank.height = reportCanvas.height;
            if (reportCanvas.toDataURL() !== blank.toDataURL()) {
                document.getElementById('reportSignatureInput').value = reportCanvas.toDataURL('image/png');
            }
        }

        if (hasInvalidMaterial) {
            e.preventDefault();
            window._firstInvalidInputToFocus = firstInvalidInput;
            document.getElementById('invalidMaterialModal').classList.remove('hidden');
            return false;
        }

        if (totalAllocated > initialWeight) {
            e.preventDefault();
            document.getElementById('exceedWarningText').innerHTML = `Total pembagian (<strong>${totalAllocated.toFixed(2)} ${sourceUnit}</strong>) melebihi bahan awal (<strong>${initialWeight.toFixed(2)} ${sourceUnit}</strong>). Harap periksa kembali.`;
            document.getElementById('exceedWarningModal').classList.remove('hidden');
            return false;
        }

        if (totalAllocated < initialWeight) {
            e.preventDefault();
            document.getElementById('shortageWarningText').innerHTML = `Total pembagian (<strong>${totalAllocated.toFixed(2)} ${sourceUnit}</strong>) lebih kecil dari bahan awal (<strong>${initialWeight.toFixed(2)} ${sourceUnit}</strong>).`;
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
        HTMLFormElement.prototype.submit.call(document.getElementById('reportCarveForm'));
    }

    function closeInvalidMaterialModal() {
        document.getElementById('invalidMaterialModal').classList.add('hidden');
        if (window._firstInvalidInputToFocus) {
            window._firstInvalidInputToFocus.focus();
            window._firstInvalidInputToFocus = null;
        }
    }

    // Searchable Combobox with Register On-the-Fly for Categories
    function setupCategoryCombobox(inputId, dropdownId, initialCategories) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        if (!input || !dropdown) return;

        let categories = Array.isArray(initialCategories) ? [...initialCategories] : [];

        function render(query = '') {
            const q = (query || '').trim();
            const qLower = q.toLowerCase();
            dropdown.innerHTML = '';

            const topItem = document.createElement('div');
            topItem.className = 'px-3.5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 cursor-pointer flex items-center justify-between border-b border-slate-800 transition select-none';
            if (q.length > 0) {
                topItem.innerHTML = `
                    <span class="text-xs font-bold">+ Tambahkan "<strong>${escapeHtml(q)}</strong>"</span>
                    <span class="text-[10px] font-semibold opacity-80 uppercase tracking-wider">Kategori Baru</span>
                `;
                topItem.onmousedown = (e) => { e.preventDefault(); selectCat(q); };
            } else {
                topItem.innerHTML = `
                    <span class="text-xs font-bold">+ Tambah Kategori Baru...</span>
                    <span class="text-[10px] font-semibold opacity-80 uppercase tracking-wider">Ketik Nama</span>
                `;
                topItem.onmousedown = (e) => { e.preventDefault(); input.focus(); };
            }
            dropdown.appendChild(topItem);

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
                    item.onmousedown = (e) => { e.preventDefault(); selectCat(c); };
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
        input.addEventListener('blur', () => { setTimeout(() => dropdown.classList.add('hidden'), 200); });
    }

    @php
        $defaultCarveCategories = ['Bahan Susut'];
        $mergedCategories = array_unique(array_merge($defaultCarveCategories, $categories ?? \App\Models\Material::DEFAULT_CATEGORIES));
    @endphp
    setupCategoryCombobox('quickMaterialCategory', 'quickCategoryDropdown', @json(array_values($mergedCategories)));
</script>
@endif
@endsection
