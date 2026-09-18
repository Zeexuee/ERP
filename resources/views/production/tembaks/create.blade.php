@extends('layouts.app', ['title' => 'Inisiasi Tembak Kayu'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Tembak Kayu</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                Tahap 1
            </span>
        </div>
        <a href="{{ route('production.tembaks.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
            Kembali
        </a>
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

    <form action="{{ route('production.tembaks.store') }}" method="POST" id="tembakInitForm" class="space-y-6">
        @csrf

        <!-- Alokasi Bahan Kayu -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                1. Alokasi Bahan Kayu
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Bahan Kayu Asal
                    </label>
                    <select name="wood_material_id" id="woodMaterialSelect" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        <option value="">Pilih Bahan Kayu</option>
                        @foreach($materials as $mat)
                            <option 
                                value="{{ $mat->id }}" 
                                data-stock="{{ (float) $mat->stock_quantity }}"
                                data-unit="{{ $mat->unit }}"
                                data-name="{{ $mat->name }}"
                                {{ old('wood_material_id') == $mat->id ? 'selected' : '' }}
                            >
                                [{{ $mat->code }}] {{ $mat->name }} ({{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Indikator Stok Kayu -->
                    <div id="woodStockCard" class="mt-2.5 hidden">
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-300 bg-white text-xs">
                            <span class="text-xs font-semibold text-slate-600">Stok Tersedia di Gudang</span>
                            <div class="flex items-center gap-1">
                                <span id="woodStockValue" class="font-mono font-bold text-slate-900 text-xs">0.00</span>
                                <span id="woodStockUnit" class="text-xs font-mono font-bold text-slate-700">kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Berat Kayu (<span id="woodWeightUnitLabel">kg</span>)
                        </label>
                        <button 
                            type="button" 
                            id="useMaxWoodStockBtn" 
                            class="hidden text-[11px] font-semibold text-slate-600 hover:text-slate-900 underline transition"
                        >
                            Gunakan Semua (<span id="maxWoodStockText" class="font-mono font-bold text-slate-900">0</span> <span id="maxWoodStockUnit">kg</span>)
                        </button>
                    </div>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            name="wood_weight" 
                            id="woodWeightInput" 
                            value="{{ old('wood_weight') }}" 
                            class="w-full px-3 py-2 pr-12 rounded-xl apple-input text-sm font-mono font-bold" 
                            required
                        >
                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400" id="woodWeightInputUnit">kg</span>
                    </div>
                    <div id="woodStockWarning" class="hidden text-[11px] font-semibold text-slate-900 bg-slate-100 border border-slate-300 rounded-xl p-2.5 mt-1.5">
                        Berat input melebihi stok yang tersedia (<span id="warningWoodStockValue">0</span> <span id="warningWoodStockUnit">kg</span>).
                    </div>
                </div>
            </div>
        </div>

        <!-- Alokasi Bahan Minyak / Resin -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                2. Alokasi Minyak / Resin Gaharu
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Bahan Minyak / Resin
                    </label>
                    <select name="resin_material_id" id="resinMaterialSelect" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        <option value="">Pilih Minyak / Resin</option>
                        @foreach($materials as $mat)
                            <option 
                                value="{{ $mat->id }}" 
                                data-stock="{{ (float) $mat->stock_quantity }}"
                                data-unit="{{ $mat->unit }}"
                                data-name="{{ $mat->name }}"
                                {{ old('resin_material_id') == $mat->id ? 'selected' : '' }}
                            >
                                [{{ $mat->code }}] {{ $mat->name }} ({{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Indikator Stok Resin -->
                    <div id="resinStockCard" class="mt-2.5 hidden">
                        <div class="flex items-center justify-between p-2.5 rounded-xl border border-slate-300 bg-white text-xs">
                            <span class="text-xs font-semibold text-slate-600">Stok Tersedia di Gudang</span>
                            <div class="flex items-center gap-1">
                                <span id="resinStockValue" class="font-mono font-bold text-slate-900 text-xs">0.00</span>
                                <span id="resinStockUnit" class="text-xs font-mono font-bold text-slate-700">kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Kuantitas Resin (<span id="resinWeightUnitLabel">kg</span>)
                        </label>
                        <button 
                            type="button" 
                            id="useMaxResinStockBtn" 
                            class="hidden text-[11px] font-semibold text-slate-600 hover:text-slate-900 underline transition"
                        >
                            Gunakan Semua (<span id="maxResinStockText" class="font-mono font-bold text-slate-900">0</span> <span id="maxResinStockUnit">kg</span>)
                        </button>
                    </div>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            name="resin_weight" 
                            id="resinWeightInput" 
                            value="{{ old('resin_weight') }}" 
                            class="w-full px-3 py-2 pr-12 rounded-xl apple-input text-sm font-mono font-bold" 
                            required
                        >
                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400" id="resinWeightInputUnit">kg</span>
                    </div>
                    <div id="resinStockWarning" class="hidden text-[11px] font-semibold text-slate-900 bg-slate-100 border border-slate-300 rounded-xl p-2.5 mt-1.5">
                        Kuantitas input melebihi stok yang tersedia (<span id="warningResinStockValue">0</span> <span id="warningResinStockUnit">kg</span>).
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Tanggal & Petugas -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                3. Detail Tanggal & Penanggung Jawab
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tanggal Proses Tembak
                        </label>
                        <input 
                            type="date" 
                            name="tembak_date" 
                            value="{{ old('tembak_date', date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            PIC (Operator Tembak)
                        </label>
                        <input 
                            type="text" 
                            name="pic_name" 
                            value="{{ old('pic_name', auth()->user()->name ?? '') }}"
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Catatan Operasional
                        </label>
                        <textarea 
                            name="notes" 
                            rows="4" 
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Tanda Tangan Digital PIC
                    </label>
                    <div class="p-3 rounded-2xl bg-white border border-slate-200 space-y-2">
                        <div class="border border-dashed border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative">
                            <canvas id="signatureCanvas" width="340" height="150" class="w-full h-[150px] cursor-crosshair"></canvas>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-400">Gunakan mouse atau sentuhan jari</span>
                            <button type="button" id="clearSignatureBtn" class="text-[11px] font-semibold text-slate-600 hover:text-slate-900 underline">
                                Hapus
                            </button>
                        </div>
                    </div>
                    <input type="hidden" name="signature_data" id="signatureData">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.tembaks.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-semibold shadow-md transition">
                Simpan & Inisiasi Tembak
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Logika Stok Bahan Kayu
    const woodSelect = document.getElementById('woodMaterialSelect');
    const woodStockCard = document.getElementById('woodStockCard');
    const woodStockValue = document.getElementById('woodStockValue');
    const woodStockUnit = document.getElementById('woodStockUnit');
    const woodWeightUnitLabel = document.getElementById('woodWeightUnitLabel');
    const woodWeightInputUnit = document.getElementById('woodWeightInputUnit');
    const useMaxWoodStockBtn = document.getElementById('useMaxWoodStockBtn');
    const maxWoodStockText = document.getElementById('maxWoodStockText');
    const maxWoodStockUnit = document.getElementById('maxWoodStockUnit');
    const woodWeightInput = document.getElementById('woodWeightInput');
    const woodStockWarning = document.getElementById('woodStockWarning');
    const warningWoodStockValue = document.getElementById('warningWoodStockValue');
    const warningWoodStockUnit = document.getElementById('warningWoodStockUnit');

    let currentWoodStock = 0;

    function updateWoodStock() {
        const selected = woodSelect.options[woodSelect.selectedIndex];
        if (selected && selected.value) {
            currentWoodStock = parseFloat(selected.getAttribute('data-stock') || 0);
            const unit = selected.getAttribute('data-unit') || 'kg';

            woodStockValue.textContent = currentWoodStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            woodStockUnit.textContent = unit;
            woodWeightUnitLabel.textContent = unit;
            woodWeightInputUnit.textContent = unit;
            maxWoodStockText.textContent = currentWoodStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            maxWoodStockUnit.textContent = unit;
            warningWoodStockValue.textContent = currentWoodStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            warningWoodStockUnit.textContent = unit;

            woodStockCard.classList.remove('hidden');
            useMaxWoodStockBtn.classList.remove('hidden');
            validateWoodInput();
        } else {
            currentWoodStock = 0;
            woodStockCard.classList.add('hidden');
            useMaxWoodStockBtn.classList.add('hidden');
            woodStockWarning.classList.add('hidden');
        }
    }

    function validateWoodInput() {
        const inputVal = parseFloat(woodWeightInput.value || 0);
        if (inputVal > currentWoodStock && currentWoodStock > 0) {
            woodStockWarning.classList.remove('hidden');
        } else {
            woodStockWarning.classList.add('hidden');
        }
    }

    woodSelect.addEventListener('change', updateWoodStock);
    woodWeightInput.addEventListener('input', validateWoodInput);
    useMaxWoodStockBtn.addEventListener('click', () => {
        woodWeightInput.value = currentWoodStock;
        validateWoodInput();
    });

    if (woodSelect.value) {
        updateWoodStock();
    }

    // 2. Logika Stok Bahan Resin
    const resinSelect = document.getElementById('resinMaterialSelect');
    const resinStockCard = document.getElementById('resinStockCard');
    const resinStockValue = document.getElementById('resinStockValue');
    const resinStockUnit = document.getElementById('resinStockUnit');
    const resinWeightUnitLabel = document.getElementById('resinWeightUnitLabel');
    const resinWeightInputUnit = document.getElementById('resinWeightInputUnit');
    const useMaxResinStockBtn = document.getElementById('useMaxResinStockBtn');
    const maxResinStockText = document.getElementById('maxResinStockText');
    const maxResinStockUnit = document.getElementById('maxResinStockUnit');
    const resinWeightInput = document.getElementById('resinWeightInput');
    const resinStockWarning = document.getElementById('resinStockWarning');
    const warningResinStockValue = document.getElementById('warningResinStockValue');
    const warningResinStockUnit = document.getElementById('warningResinStockUnit');

    let currentResinStock = 0;

    function updateResinStock() {
        const selected = resinSelect.options[resinSelect.selectedIndex];
        if (selected && selected.value) {
            currentResinStock = parseFloat(selected.getAttribute('data-stock') || 0);
            const unit = selected.getAttribute('data-unit') || 'kg';

            resinStockValue.textContent = currentResinStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            resinStockUnit.textContent = unit;
            resinWeightUnitLabel.textContent = unit;
            resinWeightInputUnit.textContent = unit;
            maxResinStockText.textContent = currentResinStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            maxResinStockUnit.textContent = unit;
            warningResinStockValue.textContent = currentResinStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
            warningResinStockUnit.textContent = unit;

            resinStockCard.classList.remove('hidden');
            useMaxResinStockBtn.classList.remove('hidden');
            validateResinInput();
        } else {
            currentResinStock = 0;
            resinStockCard.classList.add('hidden');
            useMaxResinStockBtn.classList.add('hidden');
            resinStockWarning.classList.add('hidden');
        }
    }

    function validateResinInput() {
        const inputVal = parseFloat(resinWeightInput.value || 0);
        if (inputVal > currentResinStock && currentResinStock > 0) {
            resinStockWarning.classList.remove('hidden');
        } else {
            resinStockWarning.classList.add('hidden');
        }
    }

    resinSelect.addEventListener('change', updateResinStock);
    resinWeightInput.addEventListener('input', validateResinInput);
    useMaxResinStockBtn.addEventListener('click', () => {
        resinWeightInput.value = currentResinStock;
        validateResinInput();
    });

    if (resinSelect.value) {
        updateResinStock();
    }

    // 3. Canvas Tanda Tangan
    const canvas = document.getElementById('signatureCanvas');
    const ctx = canvas.getContext('2d');
    const clearBtn = document.getElementById('clearSignatureBtn');
    const signatureInput = document.getElementById('signatureData');
    const form = document.getElementById('tembakInitForm');
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
});
</script>
@endsection
