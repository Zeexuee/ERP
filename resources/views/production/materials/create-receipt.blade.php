@extends('layouts.app', ['title' => 'Input Barang Masuk'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Input Barang Masuk</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pencatatan pasokan bahan baku yang tiba di gudang pabrik Gaharu Sana'i.</p>
        </div>
        <a href="{{ route('production.materials.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
            ← Kembali ke Gudang
        </a>
    </div>

    <!-- Form Container -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <form action="{{ route('production.materials.store-receipt') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Nomor Bukti Masuk & Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        No. Bukti Penerimaan
                    </label>
                    <input 
                        type="text" 
                        value="{{ $nextNumber }}" 
                        disabled 
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100/70 border border-slate-200 text-slate-700 font-mono font-bold"
                    >
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Nomor unik otomatis dari sistem.</span>
                </div>

                <div>
                    <label for="received_date" class="block text-xs font-semibold text-slate-700 mb-1">
                        Tanggal Penerimaan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="received_date" 
                        name="received_date" 
                        value="{{ old('received_date', date('Y-m-d')) }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                    @error('received_date')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Pilih Bahan Baku -->
            <div>
                <label for="material_id" class="block text-xs font-semibold text-slate-700 mb-1">
                    Pilih Bahan Baku <span class="text-red-500">*</span>
                </label>
                <select 
                    id="material_id" 
                    name="material_id" 
                    required 
                    onchange="updateMaterialUnitHint(this)" 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white"
                >
                    <option value="">-- Pilih Bahan Baku yang Masuk --</option>
                    @foreach($materials as $mat)
                        <option 
                            value="{{ $mat->id }}" 
                            data-unit="{{ $mat->unit }}" 
                            data-cost="{{ (float) $mat->unit_cost }}" 
                            data-stock="{{ (float) $mat->stock_quantity }}"
                            {{ old('material_id') == $mat->id ? 'selected' : '' }}
                        >
                            [{{ $mat->code }}] {{ $mat->name }} (Stok saat ini: {{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }})
                        </option>
                    @endforeach
                </select>
                @error('material_id')
                    <span class="text-red-600 text-[10px]">{{ $message }}</span>
                @enderror
            </div>

            <!-- Kuantitas Masuk & Estimasi Biaya -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-xs font-semibold text-slate-700 mb-1">
                        Jumlah Masuk <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0.01" 
                            id="quantity" 
                            name="quantity" 
                            value="{{ old('quantity') }}" 
                            required 
                            placeholder="Contoh: 25.00" 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono pr-12"
                        >
                        <span id="unitBadge" class="absolute right-3 top-2 text-xs font-semibold text-slate-400">
                            unit
                        </span>
                    </div>
                    @error('quantity')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="unit_cost" class="block text-xs font-semibold text-slate-700 mb-1">
                        Harga Beli / Biaya per Unit (Rp)
                    </label>
                    <input 
                        type="number" 
                        step="100" 
                        min="0" 
                        id="unit_cost" 
                        name="unit_cost" 
                        value="{{ old('unit_cost') }}" 
                        placeholder="Harga beli satuan..." 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono"
                    >
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Kosongkan jika sama dengan harga perolehan sebelumnya.</span>
                    @error('unit_cost')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Asal / Pemasok & Nama Penerima -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="source_or_supplier" class="block text-xs font-semibold text-slate-700 mb-1">
                        Asal / Pemasok (Supplier) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="source_or_supplier" 
                        name="source_or_supplier" 
                        value="{{ old('source_or_supplier') }}" 
                        required 
                        placeholder="Contoh: Mitra Petani Hutan Kalbar / CV Merauke Oud" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                    @error('source_or_supplier')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="received_by" class="block text-xs font-semibold text-slate-700 mb-1">
                        Petugas Penerima Gudang <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="received_by" 
                        name="received_by" 
                        value="{{ old('received_by', auth()->user()?->name ?? 'Admin Produksi') }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                    >
                    @error('received_by')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Catatan Fisik / Kualitas -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">
                    Catatan Kondisi / Kualitas Bahan
                </label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="3" 
                    placeholder="Contoh: Kadar air kayu < 12%, minyak beraroma manis pekat bersih tanpa bau hangus..." 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="text-red-600 text-[10px]">{{ $message }}</span>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-900/10">
                <a href="{{ route('production.materials.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Simpan Barang Masuk
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function updateMaterialUnitHint(select) {
        const option = select.options[select.selectedIndex];
        const unit = option ? option.getAttribute('data-unit') : 'unit';
        const cost = option ? option.getAttribute('data-cost') : '';
        
        document.getElementById('unitBadge').innerText = unit || 'unit';
        if (cost && !document.getElementById('unit_cost').value) {
            document.getElementById('unit_cost').value = cost;
        }
    }

    // Jalankan saat pertama kali dimuat jika ada old value
    window.addEventListener('DOMContentLoaded', () => {
        const select = document.getElementById('material_id');
        if (select && select.value) {
            updateMaterialUnitHint(select);
        }
    });
</script>
@endsection
