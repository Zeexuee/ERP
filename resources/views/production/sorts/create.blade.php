@extends('layouts.app', ['title' => 'Inisiasi Sortir Kayu'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Sortir Kayu</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                Tahap 1
            </span>
        </div>
        <a href="{{ route('production.sorts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
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

    <form action="{{ route('production.sorts.store') }}" method="POST" id="sortInitForm" class="space-y-6">
        @csrf

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Bahan Mentah Asal & Tanggal
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Bahan Baku Mentah Asal
                    </label>
                    <select name="source_material_id" id="sourceMaterialSelect" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        <option value="">Pilih Bahan Baku</option>
                        @foreach($materials as $mat)
                            <option 
                                value="{{ $mat->id }}" 
                                data-stock="{{ (float) $mat->stock_quantity }}"
                                data-unit="{{ $mat->unit }}"
                                data-name="{{ $mat->name }}"
                                {{ old('source_material_id') == $mat->id ? 'selected' : '' }}
                            >
                                [{{ $mat->code }}] {{ $mat->name }} ({{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Indikator Stok Tersedia Otomatis -->
                    <div id="stockDisplayCard" class="mt-2.5 hidden">
                        <div id="stockCardWrapper" class="flex items-center justify-between p-2.5 rounded-xl border border-slate-300 bg-white text-xs">
                            <span class="text-xs font-semibold text-slate-600">Stok Tersedia di Gudang</span>
                            <div class="flex items-center gap-1">
                                <span id="availableStockValue" class="font-mono font-bold text-slate-900 text-xs">0.00</span>
                                <span id="availableStockUnit" class="text-xs font-mono font-bold text-slate-700">kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Tanggal
                    </label>
                    <input 
                        type="date" 
                        name="sort_date" 
                        value="{{ old('sort_date', date('Y-m-d')) }}" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                        required
                    >
                </div>
            </div>

            <div class="pt-2">
                <div class="flex items-center justify-between max-w-sm mb-1">
                    <label class="block text-xs font-bold text-slate-700">
                        Berat Bahan Awal (<span id="weightUnitLabel">kg</span>)
                    </label>
                    <button 
                        type="button" 
                        id="useMaxStockBtn" 
                        class="hidden text-[11px] font-semibold text-slate-600 hover:text-slate-900 underline transition"
                    >
                        Gunakan Semua (<span id="maxStockText" class="font-mono font-bold text-slate-900">0</span> <span id="maxStockUnit">kg</span>)
                    </button>
                </div>
                <div class="relative max-w-sm">
                    <input 
                        type="number" 
                        step="0.01" 
                        name="initial_weight" 
                        id="initialWeightInput" 
                        value="{{ old('initial_weight') }}" 
                        class="w-full px-3 py-2 pr-12 rounded-xl apple-input text-sm font-mono font-bold" 
                        required
                    >
                    <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-400" id="weightInputUnit">kg</span>
                </div>
                <div id="stockWarning" class="hidden text-[11px] font-semibold text-slate-900 bg-slate-100 border border-slate-300 rounded-xl p-2.5 mt-1.5 max-w-sm">
                    Berat input melebihi stok yang tersedia (<span id="warningStockValue">0</span> <span id="warningStockUnit">kg</span>).
                </div>
            </div>
        </div>

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Petugas & Validasi PIC
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            PIC
                        </label>
                        <input 
                            type="text" 
                            name="pic_name" 
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
                            rows="4" 
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Tanda Tangan Digital PIC
                        </label>
                        <button type="button" onclick="clearSignature()" class="text-[10px] text-slate-500 hover:text-slate-900 underline">
                            Hapus
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <canvas id="sortSignatureCanvas" class="w-full h-36 cursor-crosshair touch-none block bg-white"></canvas>
                    </div>
                    <input type="hidden" name="signature_data" id="sortSignatureInput">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.sorts.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                Simpan Inisiasi Sortir
            </button>
        </div>
    </form>

</div>

<script>
    let canvas, ctx, isDrawing = false;

    function initCanvas() {
        canvas = document.getElementById('sortSignatureCanvas');
        if (!canvas) return;

        ctx = canvas.getContext('2d');
        canvas.width = canvas.parentElement.clientWidth || 350;
        canvas.height = 144;

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function start(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function end() {
            if (isDrawing) {
                isDrawing = false;
                document.getElementById('sortSignatureInput').value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', end);

        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', end);
    }

    function clearSignature() {
        if (!ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('sortSignatureInput').value = '';
    }

    const sourceSelect = document.getElementById('sourceMaterialSelect');
    const stockDisplayCard = document.getElementById('stockDisplayCard');
    const stockCardWrapper = document.getElementById('stockCardWrapper');
    const availableStockValue = document.getElementById('availableStockValue');
    const availableStockUnit = document.getElementById('availableStockUnit');
    const weightUnitLabel = document.getElementById('weightUnitLabel');
    const weightInputUnit = document.getElementById('weightInputUnit');
    const useMaxStockBtn = document.getElementById('useMaxStockBtn');
    const maxStockText = document.getElementById('maxStockText');
    const maxStockUnit = document.getElementById('maxStockUnit');
    const initialWeightInput = document.getElementById('initialWeightInput');
    const stockWarning = document.getElementById('stockWarning');
    const warningStockValue = document.getElementById('warningStockValue');
    const warningStockUnit = document.getElementById('warningStockUnit');

    let currentAvailableStock = null;

    function updateStockDisplay() {
        if (!sourceSelect) return;
        const selectedOption = sourceSelect.options[sourceSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            if (stockDisplayCard) stockDisplayCard.classList.add('hidden');
            if (useMaxStockBtn) useMaxStockBtn.classList.add('hidden');
            if (stockWarning) stockWarning.classList.add('hidden');
            currentAvailableStock = null;
            if (weightUnitLabel) weightUnitLabel.textContent = 'kg';
            if (weightInputUnit) weightInputUnit.textContent = 'kg';
            return;
        }

        const rawStock = parseFloat(selectedOption.getAttribute('data-stock')) || 0;
        const unit = selectedOption.getAttribute('data-unit') || 'kg';
        currentAvailableStock = rawStock;

        if (stockDisplayCard) stockDisplayCard.classList.remove('hidden');
        if (availableStockValue) availableStockValue.textContent = rawStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
        if (availableStockUnit) availableStockUnit.textContent = unit;
        if (weightUnitLabel) weightUnitLabel.textContent = unit;
        if (weightInputUnit) weightInputUnit.textContent = unit;
        if (maxStockText) maxStockText.textContent = rawStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
        if (maxStockUnit) maxStockUnit.textContent = unit;
        if (warningStockValue) warningStockValue.textContent = rawStock.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 });
        if (warningStockUnit) warningStockUnit.textContent = unit;

        if (rawStock > 0) {
            if (stockCardWrapper) stockCardWrapper.className = 'flex items-center justify-between p-2.5 rounded-xl border border-slate-300 bg-white text-xs';
            if (useMaxStockBtn) useMaxStockBtn.classList.remove('hidden');
        } else {
            if (stockCardWrapper) stockCardWrapper.className = 'flex items-center justify-between p-2.5 rounded-xl border border-slate-300 bg-slate-100 text-xs';
            if (useMaxStockBtn) useMaxStockBtn.classList.add('hidden');
        }

        validateWeightInput();
    }

    function validateWeightInput() {
        if (!initialWeightInput || !stockWarning) return;

        if (currentAvailableStock === null) {
            stockWarning.classList.add('hidden');
            return;
        }

        const inputVal = parseFloat(initialWeightInput.value) || 0;
        if (inputVal > currentAvailableStock) {
            stockWarning.classList.remove('hidden');
        } else {
            stockWarning.classList.add('hidden');
        }
    }

    if (sourceSelect) {
        sourceSelect.addEventListener('change', updateStockDisplay);
    }

    if (initialWeightInput) {
        initialWeightInput.addEventListener('input', validateWeightInput);
    }

    if (useMaxStockBtn) {
        useMaxStockBtn.addEventListener('click', () => {
            if (currentAvailableStock !== null && currentAvailableStock > 0) {
                initialWeightInput.value = currentAvailableStock;
                validateWeightInput();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initCanvas();
        updateStockDisplay();
    });

    document.getElementById('sortInitForm').addEventListener('submit', function (e) {
        if (canvas) {
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            if (canvas.toDataURL() !== blank.toDataURL()) {
                document.getElementById('sortSignatureInput').value = canvas.toDataURL('image/png');
            }
        }
    });
</script>
@endsection
