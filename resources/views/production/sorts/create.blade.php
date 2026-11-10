@extends('layouts.app', ['title' => 'Inisiasi Sortir Kayu'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Sortir Kayu</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                Warehouse
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                Tahap 1
            </span>
        </div>
        <a href="{{ route('production.sorts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition">
            Kembali
        </a>
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

    @if($materials->isEmpty())
        <div class="p-4 rounded-2xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold shadow-xs">
            Tidak ada bahan baku berkategori KCB dengan stok tersedia yang siap disortir. Pastikan bahan baku sudah selesai dari proses aktif lainnya (seperti jemur) atau input bahan baku KCB baru di gudang.
        </div>
    @endif

    <form action="{{ route('production.sorts.store') }}" method="POST" id="sortInitForm" class="space-y-6">
        @csrf

        <!-- Panel Form Utama -->
        <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Bahan Mentah Asal & Tanggal Inisiasi
                </h3>
                <p class="text-xs text-slate-600 mt-0.5">Pilih kayu mentah dari gudang yang akan diproses dan tentukan bobot yang ingin dipilah.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                        Bahan Baku Mentah Asal (Khusus KCB) <span class="text-slate-900">*</span>
                    </label>
                    <select name="source_material_id" id="sourceMaterialSelect" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-slate-900 focus:outline-none" required {{ $materials->isEmpty() ? 'disabled' : '' }}>
                        <option value="">{{ $materials->isEmpty() ? 'Tidak ada bahan KCB yang siap disortir' : '-- Pilih Bahan Baku KCB Siap Sortir --' }}</option>
                        @foreach($materials as $mat)
                            <option 
                                value="{{ $mat->id }}" 
                                data-stock="{{ (float) $mat->stock_quantity }}"
                                data-unit="{{ $mat->unit }}"
                                data-name="{{ $mat->name }}"
                                {{ old('source_material_id') == $mat->id ? 'selected' : '' }}
                            >
                                [{{ $mat->code }}] {{ $mat->name }} (Stok: {{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }})
                            </option>
                        @endforeach
                    </select>

                    <!-- Indikator Stok Tersedia Otomatis -->
                    <div id="stockDisplayCard" class="mt-3 hidden">
                        <div id="stockCardWrapper" class="flex items-center justify-between p-3 rounded-xl border border-slate-300 bg-slate-50 text-xs">
                            <span class="text-xs font-semibold text-slate-700">Stok Tersedia di Gudang:</span>
                            <div class="flex items-center gap-1">
                                <span id="availableStockValue" class="font-mono font-bold text-slate-900 text-sm">0.00</span>
                                <span id="availableStockUnit" class="text-xs font-mono font-bold text-slate-800">kg</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                        Tanggal Inisiasi <span class="text-slate-900">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="sort_date" 
                        value="{{ old('sort_date', date('Y-m-d')) }}" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-slate-900 focus:outline-none" 
                        required
                    >
                </div>
            </div>

            <div class="pt-2 grid grid-cols-1 md:grid-cols-2 gap-5 border-t border-slate-200">
                <div>
                    <div class="flex items-center justify-between max-w-sm mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-900">
                            Berat yang Ingin Digunakan (<span id="weightUnitLabel">kg</span>) <span class="text-slate-900">*</span>
                        </label>
                        <button 
                            type="button" 
                            id="useMaxStockBtn" 
                            class="hidden text-[11px] font-bold text-slate-900 hover:underline transition"
                        >
                            Gunakan Semua (<span id="maxStockText" class="font-mono font-bold">0</span> <span id="maxStockUnit">kg</span>)
                        </button>
                    </div>
                    <div class="relative max-w-sm">
                        <input 
                            type="number" 
                            step="0.01" 
                            name="initial_weight" 
                            id="initialWeightInput" 
                            value="{{ old('initial_weight') }}" 
                            class="w-full px-3.5 py-2.5 pr-14 rounded-xl bg-white border border-slate-300 text-slate-900 text-sm font-mono font-bold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none" 
                            placeholder="0.00"
                            required
                        >
                        <span class="absolute right-3.5 top-2.5 text-xs font-bold font-mono text-slate-700" id="weightInputUnit">kg</span>
                    </div>
                    <div id="stockWarning" class="hidden text-xs font-bold text-slate-900 bg-slate-100 border border-slate-400 rounded-xl p-3 mt-2 max-w-sm">
                        Berat input melebihi stok yang tersedia (<span id="warningStockValue">0</span> <span id="warningStockUnit">kg</span>).
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                        Penanggung Jawab (PIC) <span class="text-slate-900">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="pic_name" 
                        value="{{ old('pic_name') }}" 
                        placeholder="Nama Petugas / PIC Sortir"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none" 
                        required
                    >
                </div>
            </div>
        </div>

        <!-- Panel Catatan -->
        <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-2">
                Catatan Operasional (Opsional)
            </h3>

            <div>
                <textarea 
                    name="notes" 
                    rows="3" 
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-medium placeholder:text-slate-500 focus:border-slate-900 focus:outline-none"
                    placeholder="Keterangan tambahan mengenai bahan baku, instruksi pemilahan, dsb..."
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.sorts.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed" {{ $materials->isEmpty() ? 'disabled' : '' }}>
                Simpan Inisiasi Sortir
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
    const submitBtn = document.getElementById('submitBtn');

    let currentAvailableStock = null;

    function updateStockDisplay() {
        if (!sourceSelect) return;
        const selectedOption = sourceSelect.options[sourceSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            if (stockDisplayCard) stockDisplayCard.classList.add('hidden');
            if (useMaxStockBtn) useMaxStockBtn.classList.add('hidden');
            currentAvailableStock = null;
            return;
        }

        const stock = parseFloat(selectedOption.dataset.stock) || 0;
        const unit = selectedOption.dataset.unit || 'kg';
        currentAvailableStock = stock;

        if (availableStockValue) availableStockValue.textContent = stock.toFixed(2);
        if (availableStockUnit) availableStockUnit.textContent = unit;
        if (weightUnitLabel) weightUnitLabel.textContent = unit;
        if (weightInputUnit) weightInputUnit.textContent = unit;
        if (maxStockText) maxStockText.textContent = stock.toFixed(2);
        if (maxStockUnit) maxStockUnit.textContent = unit;

        if (stockDisplayCard) stockDisplayCard.classList.remove('hidden');
        if (useMaxStockBtn && stock > 0) useMaxStockBtn.classList.remove('hidden');

        validateWeightInput();
    }

    function validateWeightInput() {
        if (!initialWeightInput || currentAvailableStock === null) return;

        const val = parseFloat(initialWeightInput.value);
        if (isNaN(val) || val <= 0) {
            if (stockWarning) stockWarning.classList.add('hidden');
            return;
        }

        if (val > currentAvailableStock) {
            if (stockWarning) {
                stockWarning.classList.remove('hidden');
                if (warningStockValue) warningStockValue.textContent = currentAvailableStock.toFixed(2);
                if (warningStockUnit) warningStockUnit.textContent = availableStockUnit ? availableStockUnit.textContent : 'kg';
            }
        } else {
            if (stockWarning) stockWarning.classList.add('hidden');
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
