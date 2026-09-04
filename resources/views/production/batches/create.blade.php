@extends('layouts.app', ['title' => 'Buat Produksi Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Buat Produksi Baru</h2>
            <p class="text-xs text-slate-500 mt-0.5">Mulai batch pengerjaan Gaharu Sana'i dan alokasikan bahan baku dari gudang.</p>
        </div>
        <a href="{{ route('production.batches.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
            ← Kembali ke Daftar
        </a>
    </div>

    @if($errors->has('materials'))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
            {{ $errors->first('materials') }}
        </div>
    @endif

    <form action="{{ route('production.batches.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Informasi Utama Batch Produksi -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Informasi Target & Perintah Kerja
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        No. Batch Produksi
                    </label>
                    <input 
                        type="text" 
                        value="{{ $nextBatchNumber }}" 
                        disabled 
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100/80 border border-slate-200 text-slate-800 font-mono font-bold"
                    >
                </div>

                <div class="sm:col-span-2">
                    <label for="product_id" class="block text-xs font-semibold text-slate-700 mb-1">
                        Target Produk Jadi <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="product_id" 
                        name="product_id" 
                        required 
                        onchange="updateTargetUnit(this)"
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white"
                    >
                        <option value="">-- Pilih Produk Jadi yang Diproduksi --</option>
                        @foreach($products as $prod)
                            <option 
                                value="{{ $prod->id }}" 
                                data-unit="{{ $prod->unit ?? 'unit' }}"
                                {{ old('product_id') == $prod->id ? 'selected' : '' }}
                            >
                                [{{ $prod->sku }}] {{ $prod->name }} (Satuan: {{ $prod->unit ?? 'unit' }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="target_quantity" class="block text-xs font-semibold text-slate-700 mb-1">
                        Target Kuantitas Hasil Jadi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            id="target_quantity" 
                            name="target_quantity" 
                            value="{{ old('target_quantity') }}" 
                            required 
                            placeholder="Contoh: 10.00" 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono pr-12"
                        >
                        <span id="targetUnitBadge" class="absolute right-3 top-2 text-xs font-semibold text-slate-400">
                            unit
                        </span>
                    </div>
                    @error('target_quantity')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="start_date" class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        value="{{ old('start_date', date('Y-m-d')) }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                    @error('start_date')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="target_completion_date" class="block text-xs font-semibold text-slate-700 mb-1">
                        Estimasi Tanggal Selesai
                    </label>
                    <input 
                        type="date" 
                        id="target_completion_date" 
                        name="target_completion_date" 
                        value="{{ old('target_completion_date', date('Y-m-d', strtotime('+3 days'))) }}" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                    @error('target_completion_date')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="production_request_id" class="block text-xs font-semibold text-slate-700 mb-1">
                        Referensi Antrean Sales (Opsional)
                    </label>
                    <select 
                        id="production_request_id" 
                        name="production_request_id" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white"
                    >
                        <option value="">-- Bukan dari Pesanan Sales (Produksi Stok Pabrik) --</option>
                        @foreach($pendingRequests as $pr)
                            <option value="{{ $pr->id }}" {{ old('production_request_id') == $pr->id ? 'selected' : '' }}>
                                Permintaan #{{ $pr->request_number }} (Order: {{ $pr->salesOrder->order_number ?? '-' }} - {{ $pr->salesOrder->customer->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('production_request_id')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="pic_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        PIC / Mandor Produksi <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="pic_name" 
                        name="pic_name" 
                        value="{{ old('pic_name', auth()->user()?->name ?? 'Mandor Pabrik Sentul') }}" 
                        required 
                        placeholder="Nama penanggung jawab..." 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                    @error('pic_name')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">
                    Catatan Instruksi Kerja / Formula Olahan
                </label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2" 
                    placeholder="Contoh: Infusi tekanan vacuum 4 bar, suhu oven curing stabil di 45°C selama 24 jam..." 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Alokasi Komposisi Bahan Baku (BOM) -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Alokasi Komposisi Bahan Baku (BOM)</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Bahan-bahan yang akan diambil dan dipotong dari stok gudang untuk batch ini.</p>
                </div>
                <button type="button" onclick="addMaterialRow()" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
                    + Tambah Baris Bahan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="materialsTable">
                    <thead>
                        <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Bahan Baku</th>
                            <th class="py-2.5 px-3">Stok Gudang Tersedia</th>
                            <th class="py-2.5 px-3">Kuantitas Digunakan</th>
                            <th class="py-2.5 px-3 text-center w-16">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="materialRowsContainer" class="divide-y divide-slate-900/5">
                        <!-- Baris Bahan Baku Dimasukkan Dinamis via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('production.batches.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                Mulai Proses Produksi
            </button>
        </div>
    </form>

</div>

<script>
    const availableMaterials = @json($materials);
    let rowIndex = 0;

    function updateTargetUnit(select) {
        const option = select.options[select.selectedIndex];
        const unit = option ? option.getAttribute('data-unit') : 'unit';
        document.getElementById('targetUnitBadge').innerText = unit || 'unit';
    }

    function addMaterialRow(selectedMatId = null, qty = 1) {
        const container = document.getElementById('materialRowsContainer');
        const rowId = `mat_row_${rowIndex}`;

        let optionsHtml = '<option value="">-- Pilih Bahan Baku --</option>';
        availableMaterials.forEach(m => {
            const isSelected = selectedMatId == m.id ? 'selected' : '';
            optionsHtml += `<option value="${m.id}" data-unit="${m.unit}" data-stock="${m.stock_quantity}" ${isSelected}>[${m.code}] ${m.name}</option>`;
        });

        const rowHtml = `
            <tr id="${rowId}" class="hover:bg-white/40 transition">
                <td class="py-2 px-3">
                    <select name="materials[${rowIndex}][material_id]" required onchange="onMaterialSelect(this, '${rowId}')" class="w-full px-3 py-1.5 rounded-xl text-xs apple-input bg-white">
                        ${optionsHtml}
                    </select>
                </td>
                <td class="py-2 px-3">
                    <span id="${rowId}_stock" class="font-mono font-semibold text-slate-600 text-xs">-</span>
                </td>
                <td class="py-2 px-3">
                    <div class="flex items-center gap-1.5">
                        <input type="number" step="0.01" min="0.01" name="materials[${rowIndex}][quantity_used]" value="${qty}" required placeholder="0.00" class="w-28 px-3 py-1.5 rounded-xl text-xs apple-input font-mono">
                        <span id="${rowId}_unit" class="text-xs text-slate-500 font-semibold font-sans">-</span>
                    </div>
                </td>
                <td class="py-2 px-3 text-center">
                    <button type="button" onclick="removeMaterialRow('${rowId}')" class="p-1 text-slate-400 hover:text-red-600 text-sm font-bold">
                        ✕
                    </button>
                </td>
            </tr>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        const newSelect = document.querySelector(`#${rowId} select`);
        if (selectedMatId) {
            onMaterialSelect(newSelect, rowId);
        }

        rowIndex++;
    }

    function removeMaterialRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    }

    function onMaterialSelect(select, rowId) {
        const option = select.options[select.selectedIndex];
        const stock = option ? option.getAttribute('data-stock') : '-';
        const unit = option ? option.getAttribute('data-unit') : '';

        const stockSpan = document.getElementById(`${rowId}_stock`);
        const unitSpan = document.getElementById(`${rowId}_unit`);

        if (stockSpan) {
            stockSpan.innerText = stock !== '-' ? `${parseFloat(stock).toFixed(1)} ${unit}` : '-';
        }
        if (unitSpan) {
            unitSpan.innerText = unit;
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const prodSelect = document.getElementById('product_id');
        if (prodSelect && prodSelect.value) {
            updateTargetUnit(prodSelect);
        }

        // Tambahkan baris awal default
        if (availableMaterials.length > 0) {
            addMaterialRow(availableMaterials[0]?.id, 10);
            if (availableMaterials.length > 1) {
                addMaterialRow(availableMaterials[1]?.id, 2);
            }
        } else {
            addMaterialRow();
        }
    });
</script>
@endsection
