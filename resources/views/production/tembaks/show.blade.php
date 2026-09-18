@extends('layouts.app', ['title' => 'Rincian Tembak #' . $tembakBatch->tembak_code])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Rincian Tembak #{{ $tembakBatch->tembak_code }}</h2>
                @if($tembakBatch->isCompleted())
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                        Selesai
                    </span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                        Sedang Ditembak
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-600 mt-1">
                Inisiasi: {{ $tembakBatch->tembak_date ? $tembakBatch->tembak_date->format('d/m/Y') : '-' }} • PIC: {{ $tembakBatch->pic_name }}
                @if($tembakBatch->isCompleted() && $tembakBatch->report_date)
                    • Selesai: {{ $tembakBatch->report_date->format('d/m/Y') }}
                    @if($tembakBatch->report_pic_name)
                        • Pelapor: {{ $tembakBatch->report_pic_name }}
                    @endif
                @endif
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.tembaks.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Kembali
            </a>
            <a href="{{ route('production.tembaks.create') }}" class="px-3.5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs transition">
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

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Kayu Asal</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $tembakBatch->woodMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                {{ number_format($tembakBatch->wood_weight, 1) }} {{ $tembakBatch->woodMaterial->unit ?? 'kg' }}
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Minyak / Resin</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $tembakBatch->resinMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                {{ number_format($tembakBatch->resin_weight, 1) }} {{ $tembakBatch->resinMaterial->unit ?? 'kg' }}
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Hasil Basah & Sisa</span>
            <div class="text-sm font-bold font-mono text-slate-900 mt-1">
                @if($tembakBatch->isCompleted() || $tembakBatch->wet_result_weight)
                    Basah: {{ number_format($tembakBatch->wet_result_weight, 1) }} kg
                @else
                    -
                @endif
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                @if($tembakBatch->isCompleted() || $tembakBatch->residual_resin_weight !== null)
                    Sisa Getah: {{ number_format($tembakBatch->residual_resin_weight ?? 0, 1) }} kg
                @else
                    -
                @endif
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Hasil Tembak Kering</span>
            <div class="text-sm font-bold font-mono text-slate-900 mt-1">
                @if($tembakBatch->isCompleted())
                    {{ number_format($tembakBatch->dried_result_weight, 1) }} {{ $tembakBatch->outputMaterial->unit ?? 'kg' }}
                @else
                    Menunggu Laporan
                @endif
            </div>
            <span class="text-xs text-slate-600 block mt-0.5">
                {{ $tembakBatch->outputMaterial->name ?? '-' }}
            </span>
        </div>
    </div>

    @if(! $tembakBatch->isCompleted())
        <!-- FORM LAPORAN HASIL TEMBAK -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-6">
            <div class="border-b border-slate-900/10 pb-3">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">Tahap 2</span>
                    <h3 class="text-base font-bold text-slate-900">
                        Laporan Hasil Tembak
                    </h3>
                </div>
            </div>

            <form action="{{ route('production.tembaks.store-report', $tembakBatch) }}" method="POST" id="reportTembakForm" class="space-y-6">
                @csrf

                <!-- 1. Timbangan Basah & Pengembalian Getah -->
                <div class="p-5 bg-white/60 border border-slate-200/80 rounded-2xl space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        1. Penimbangan Hasil Injeksi & Pengembalian Getah
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Hasil Timbang Basah (kg)
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="wet_result_weight" 
                                id="wetResultWeightInput" 
                                value="{{ old('wet_result_weight') }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" 
                                required
                            >
                            <span class="text-[10px] text-slate-500 mt-1 block">Timbang sesaat setelah proses tembak selesai</span>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    Getah Sisa Tembak (kg)
                                </label>
                                <button type="button" id="setNoResidualBtn" class="text-[10px] text-slate-500 hover:text-slate-900 underline">
                                    Nol (0 kg)
                                </button>
                            </div>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="residual_resin_weight" 
                                id="residualResinWeightInput" 
                                value="{{ old('residual_resin_weight', '0') }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" 
                                required
                            >
                            <span class="text-[10px] text-slate-500 mt-1 block">Maks: {{ number_format($tembakBatch->resin_weight, 1) }} kg (otomatis kembali ke gudang)</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Gudang Penampung Getah Sisa
                            </label>
                            <select name="residual_resin_material_id" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold">
                                @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}" {{ (old('residual_resin_material_id', $tembakBatch->resin_material_id) == $mat->id) ? 'selected' : '' }}>
                                        [{{ $mat->code }}] {{ $mat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. Hasil Kering & Penyimpanan Gudang -->
                <div class="p-5 bg-white/60 border border-slate-200/80 rounded-2xl space-y-4 overflow-visible">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        2. Hasil Tembak Kering & Penyimpanan ke Gudang
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Hasil Timbang Kering (kg)
                            </label>
                            <input 
                                type="number" 
                                step="0.01" 
                                name="dried_result_weight" 
                                id="driedResultWeightInput" 
                                value="{{ old('dried_result_weight') }}" 
                                class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" 
                                required
                            >
                            <span class="text-[10px] text-slate-500 mt-1 block">Timbang setelah proses penjemuran selesai</span>
                        </div>

                        <!-- Search-as-you-type Combobox with Liquid Glass Solid Styling -->
                        <div class="relative overflow-visible">
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Bahan Hasil Tembak di Gudang
                            </label>

                            <input type="hidden" name="output_material_id" id="outputMaterialId" value="{{ old('output_material_id') }}" required>
                            
                            <div class="relative">
                                <input 
                                    type="text" 
                                    id="materialSearchInput" 
                                    autocomplete="off" 
                                    class="w-full px-3 py-2 pr-8 rounded-xl apple-input text-xs font-semibold text-slate-900"
                                    required
                                >
                                <button type="button" id="clearMaterialBtn" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600 hidden text-xs">
                                    ✕
                                </button>
                            </div>

                            <!-- Dropdown Menu Liquid Glass Solid -->
                            <div 
                                id="materialDropdownMenu" 
                                class="hidden absolute left-0 right-0 top-full mt-1.5 apple-glass-panel bg-white/95 backdrop-blur-3xl border border-white/80 shadow-2xl rounded-2xl max-h-56 overflow-y-auto z-50 divide-y divide-slate-100"
                            >
                                <!-- Opsi Registrasi On-The-Fly di Urutan Paling Atas (Rule 7.1) -->
                                <button 
                                    type="button" 
                                    id="quickRegisterTopOption" 
                                    class="w-full text-left px-3.5 py-2.5 text-xs font-bold text-slate-900 hover:bg-slate-900 hover:text-white transition flex items-center justify-between sticky top-0 bg-white/95 backdrop-blur-3xl z-10 border-b border-slate-200/80"
                                >
                                    <span>+ Tambah Bahan Hasil Baru</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded font-mono border border-current">Baru</span>
                                </button>

                                <div id="materialListContainer" class="py-1">
                                    <!-- Dynamic Items Rendered via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Tanggal, Petugas & Tanda Tangan Digital -->
                <div class="p-5 bg-white/60 border border-slate-200/80 rounded-2xl space-y-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        3. Petugas Pelapor & Tanda Tangan
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Tanggal Laporan Selesai
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
                                    PIC Pelapor / Penimbang
                                </label>
                                <input 
                                    type="text" 
                                    name="report_pic_name" 
                                    value="{{ old('report_pic_name', auth()->user()->name ?? '') }}" 
                                    class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Catatan Laporan
                                </label>
                                <textarea 
                                    name="report_notes" 
                                    rows="3" 
                                    class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                                >{{ old('report_notes') }}</textarea>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    Tanda Tangan Digital Pelapor
                                </label>
                                <button type="button" id="clearSignatureBtn" class="text-[10px] text-slate-500 hover:text-slate-900 underline">
                                    Hapus
                                </button>
                            </div>

                            <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                                <canvas id="reportSignatureCanvas" width="340" height="150" class="w-full h-[150px] cursor-crosshair block bg-white"></canvas>
                            </div>
                            <input type="hidden" name="signature_data" id="signatureData">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-900/10">
                    <button type="submit" id="submitReportBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                        Simpan Laporan Hasil Tembak
                    </button>
                </div>
            </form>
        </div>
    @else
        <!-- RINCIAN SELESAI -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-6">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Rincian Penyelesaian Proses Tembak
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-white border border-slate-200">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Hasil Timbang Basah</span>
                    <div class="text-base font-bold font-mono text-slate-900 mt-1">
                        {{ number_format($tembakBatch->wet_result_weight, 1) }} kg
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-slate-200">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Getah Sisa Dikembalikan</span>
                    <div class="text-base font-bold font-mono text-slate-900 mt-1">
                        {{ number_format($tembakBatch->residual_resin_weight ?? 0, 1) }} kg
                    </div>
                    <span class="text-[10px] text-slate-500 block mt-0.5">
                        Ke: {{ $tembakBatch->residualResinMaterial->name ?? '-' }}
                    </span>
                </div>

                <div class="p-4 rounded-2xl bg-white border border-slate-200">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Hasil Tembak Kering Masuk Gudang</span>
                    <div class="text-base font-bold font-mono text-slate-900 mt-1">
                        {{ number_format($tembakBatch->dried_result_weight, 1) }} {{ $tembakBatch->outputMaterial->unit ?? 'kg' }}
                    </div>
                    <span class="text-[10px] text-slate-700 font-semibold block mt-0.5">
                        {{ $tembakBatch->outputMaterial->name ?? '-' }}
                    </span>
                </div>
            </div>

            @if($tembakBatch->report_notes)
                <div class="p-4 rounded-2xl bg-white border border-slate-200">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Catatan Laporan</span>
                    <p class="text-xs text-slate-800 mt-1">{{ $tembakBatch->report_notes }}</p>
                </div>
            @endif
        </div>
    @endif

</div>

<!-- Modal Quick Register Bahan Baru On-the-Fly -->
<div id="quickMaterialModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-3xl border border-white/80 shadow-2xl rounded-3xl w-full max-w-md p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-900/10 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Tambah Bahan Baku Baru (On-the-Fly)</h3>
            <button type="button" onclick="closeQuickMaterialModal()" class="text-slate-400 hover:text-slate-600 text-sm font-bold">✕</button>
        </div>

        <div id="quickMaterialError" class="hidden p-3 rounded-xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-semibold"></div>

        <form id="quickMaterialForm" onsubmit="submitQuickMaterial(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Bahan Baku</label>
                <input type="text" id="quickMaterialName" required class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kode / SKU</label>
                    <input type="text" id="quickMaterialCode" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori</label>
                    <input type="text" id="quickMaterialCategory" value="Kayu Tembak" class="w-full px-3 py-2 rounded-xl apple-input text-xs">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Satuan Unit</label>
                    <input type="text" id="quickMaterialUnit" value="kg" readonly class="w-full px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi HPP (Rp)</label>
                    <input type="number" id="quickMaterialCost" value="0" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-mono">
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeQuickMaterialModal()" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold">
                    Batal
                </button>
                <button type="submit" id="submitQuickMaterialBtn" class="px-5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs">
                    Simpan Bahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Tombol nol residual
    const setNoResidualBtn = document.getElementById('setNoResidualBtn');
    const residualResinInput = document.getElementById('residualResinWeightInput');
    if (setNoResidualBtn && residualResinInput) {
        setNoResidualBtn.addEventListener('click', () => {
            residualResinInput.value = '0';
        });
    }

    // 2. Data Materials untuk Searchable Combobox
    let materialsList = @json($materials);
    const hiddenIdInput = document.getElementById('outputMaterialId');
    const searchInput = document.getElementById('materialSearchInput');
    const dropdownMenu = document.getElementById('materialDropdownMenu');
    const listContainer = document.getElementById('materialListContainer');
    const clearBtn = document.getElementById('clearMaterialBtn');
    const quickRegisterBtn = document.getElementById('quickRegisterTopOption');

    if (searchInput && dropdownMenu && listContainer) {
        function renderItems(filter = '') {
            listContainer.innerHTML = '';
            const normalizedFilter = filter.toLowerCase().trim();
            const filtered = materialsList.filter(m => 
                (m.name && m.name.toLowerCase().includes(normalizedFilter)) ||
                (m.code && m.code.toLowerCase().includes(normalizedFilter)) ||
                (m.category && m.category.toLowerCase().includes(normalizedFilter))
            );

            if (filtered.length === 0) {
                const emptyEl = document.createElement('div');
                emptyEl.className = 'px-3.5 py-2 text-xs text-slate-400 italic';
                emptyEl.textContent = 'Tidak ada bahan yang cocok';
                listContainer.appendChild(emptyEl);
                return;
            }

            filtered.forEach(mat => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-full text-left px-3.5 py-2 text-xs hover:bg-slate-100 flex items-center justify-between transition';
                btn.innerHTML = `
                    <div>
                        <div class="font-bold text-slate-900">${mat.name}</div>
                        <div class="text-[10px] text-slate-500 font-mono">${mat.code || '-'} • ${mat.category || '-'}</div>
                    </div>
                    <span class="text-[11px] font-mono text-slate-600">${parseFloat(mat.stock_quantity || 0).toLocaleString('id-ID', {minimumFractionDigits: 1})} ${mat.unit || 'kg'}</span>
                `;
                btn.addEventListener('click', () => {
                    selectMaterial(mat);
                });
                listContainer.appendChild(btn);
            });
        }

        function selectMaterial(mat) {
            hiddenIdInput.value = mat.id;
            searchInput.value = `[${mat.code || '-'}] ${mat.name}`;
            dropdownMenu.classList.add('hidden');
            clearBtn.classList.remove('hidden');
        }

        function clearSelection() {
            hiddenIdInput.value = '';
            searchInput.value = '';
            clearBtn.classList.add('hidden');
            renderItems('');
            dropdownMenu.classList.remove('hidden');
            searchInput.focus();
        }

        searchInput.addEventListener('focus', () => {
            renderItems(searchInput.value);
            dropdownMenu.classList.remove('hidden');
        });

        searchInput.addEventListener('input', () => {
            renderItems(searchInput.value);
            dropdownMenu.classList.remove('hidden');
            if (searchInput.value) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
                hiddenIdInput.value = '';
            }
        });

        clearBtn.addEventListener('click', clearSelection);

        // Tutup dropdown jika klik di luar
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !dropdownMenu.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });

        // Quick register top button
        quickRegisterBtn.addEventListener('click', () => {
            dropdownMenu.classList.add('hidden');
            openQuickMaterialModal(searchInput.value);
        });

        // Pre-select if old value exists
        if (hiddenIdInput.value) {
            const current = materialsList.find(m => m.id == hiddenIdInput.value);
            if (current) {
                selectMaterial(current);
            }
        }
    }

    // 3. Canvas Tanda Tangan Laporan
    const canvas = document.getElementById('reportSignatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const signatureInput = document.getElementById('signatureData');
        const form = document.getElementById('reportTembakForm');
        let isDrawing = false;
        let hasSignature = false;

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasSignature = true;
            const coords = getCoordinates(e);
            ctx.beginPath();
            ctx.moveTo(coords.x, coords.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const coords = getCoordinates(e);
            ctx.lineTo(coords.x, coords.y);
            ctx.stroke();
            e.preventDefault();
        }

        function stopDrawing() {
            isDrawing = false;
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        clearBtn.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            signatureInput.value = '';
            hasSignature = false;
        });

        form.addEventListener('submit', () => {
            if (hasSignature) {
                signatureInput.value = canvas.toDataURL('image/png');
            }
        });
    }
});

// Quick Material Modal Functions
function openQuickMaterialModal(query = '') {
    const errBox = document.getElementById('quickMaterialError');
    if (errBox) {
        errBox.classList.add('hidden');
        errBox.textContent = '';
    }
    document.getElementById('quickMaterialName').value = query ? query.replace(/^\[.*?\]\s*/, '').trim() : '';
    document.getElementById('quickMaterialCode').value = '';
    document.getElementById('quickMaterialCategory').value = 'Kayu Tembak';
    document.getElementById('quickMaterialCost').value = '0';
    document.getElementById('quickMaterialModal').classList.remove('hidden');
    setTimeout(() => document.getElementById('quickMaterialName').focus(), 100);
}

function closeQuickMaterialModal() {
    document.getElementById('quickMaterialModal').classList.add('hidden');
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
            unit: 'kg',
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
            const hiddenIdInput = document.getElementById('outputMaterialId');
            const searchInput = document.getElementById('materialSearchInput');
            const clearBtn = document.getElementById('clearMaterialBtn');
            hiddenIdInput.value = data.material.id;
            searchInput.value = `[${data.material.code || '-'}] ${data.material.name}`;
            clearBtn.classList.remove('hidden');
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
</script>
@endsection
