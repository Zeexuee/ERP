@extends('layouts.app', ['title' => 'Inisiasi Pencairan Getah'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Pencairan Getah</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                Warehouse
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                Tahap 1
            </span>
        </div>
        <a href="{{ route('production.melts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
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

    <form action="{{ route('production.melts.store') }}" method="POST" id="meltInitForm" class="space-y-6">
        @csrf

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Bahan Mentah Asal & Tanggal
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Bahan Baku (Getah Padat)
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
                        name="melt_date" 
                        value="{{ old('melt_date', date('Y-m-d')) }}" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                        required
                    >
                </div>
            </div>

            <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
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
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Penanggung Jawab (PIC)
                    </label>
                    <input 
                        type="text" 
                        name="pic_name" 
                        value="{{ old('pic_name', auth()->user()?->name) }}" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                        required
                    >
                </div>
            </div>
        </div>

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Catatan
            </h3>

            <div>
                <textarea 
                    name="notes" 
                    rows="4" 
                    class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.melts.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                Simpan Inisiasi Pencairan
            </button>
        </div>
    </form>

</div>

<script>
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
        updateStockDisplay();
    });
</script>
@endsection
