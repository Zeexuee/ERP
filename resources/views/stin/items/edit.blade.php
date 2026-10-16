@extends('layouts.app', ['title' => 'Edit Data Barang'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Edit Data Barang</h2>
        </div>
        <a href="{{ route('stin.items.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
            Kembali ke Data Gudang
        </a>
    </div>

    <!-- Error Alerts -->
    @if($errors->any())
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-semibold shadow-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Container -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <form action="{{ route('stin.items.update', $item) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Nama Barang -->
            <div>
                <label for="name" class="block text-xs font-semibold text-slate-800 mb-1">
                    Nama Barang <span class="text-slate-400">*</span>
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    value="{{ old('name', $item->name) }}" 
                    required 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 font-medium border-slate-300"
                >
            </div>

            <!-- Kategori Barang (List & Register On-the-Fly) -->
            <div class="relative overflow-visible z-20">
                <label for="category" class="block text-xs font-semibold text-slate-800 mb-1">
                    Kategori Barang
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="category" 
                        name="category" 
                        value="{{ old('category', $item->category) }}" 
                        autocomplete="off"
                        placeholder="Pilih atau ketik kategori baru..." 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300 cursor-pointer"
                    >
                </div>
                <div id="categoryDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"></div>
            </div>

            <!-- Stok Saat Ini & Satuan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-800 mb-1">
                        Stok Saat Ini (Hanya Baca)
                    </label>
                    <input 
                        type="text" 
                        value="{{ number_format($item->current_stock) }} {{ $item->unit ?? 'pcs' }}" 
                        disabled 
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100/80 border border-slate-300 text-slate-700 font-bold"
                    >
                </div>

                <div>
                    <label for="unit" class="block text-xs font-semibold text-slate-800 mb-1">
                        Satuan Barang
                    </label>
                    <select 
                        id="unit" 
                        name="unit" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                    >
                        @foreach(['pcs', 'botol', 'box', 'kg', 'gram', 'liter', 'meter', 'karung'] as $u)
                            <option value="{{ $u }}" {{ old('unit', $item->unit ?? 'pcs') == $u ? 'selected' : '' }}>
                                {{ $u }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Harga HPP & SKU -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="unit_price" class="block text-xs font-semibold text-slate-800 mb-1">
                        Harga HPP (Rp)
                    </label>
                    <input 
                        type="number" 
                        min="0" 
                        step="any" 
                        id="unit_price" 
                        name="unit_price" 
                        value="{{ old('unit_price', $item->unit_price) }}" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300"
                    >
                </div>

                <div>
                    <label for="sku" class="block text-xs font-semibold text-slate-800 mb-1">
                        SKU
                    </label>
                    <input 
                        type="text" 
                        id="sku" 
                        name="sku" 
                        value="{{ old('sku', $item->sku) }}" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300"
                    >
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-900/10">
                <a href="{{ route('stin.items.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    const availableCategories = @json($existingCategories ?? []);

    function setupCategoryCombobox(inputId, dropdownId, initialCategories) {
        const input = document.getElementById(inputId);
        const dropdown = document.getElementById(dropdownId);
        if (!input || !dropdown) return;

        let categories = Array.isArray(initialCategories) ? [...initialCategories] : [];

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.innerText = text || '';
            return div.innerHTML;
        }

        function render(query = '') {
            const q = (query || '').trim();
            const qLower = q.toLowerCase();

            dropdown.innerHTML = '';

            const topItem = document.createElement('div');
            topItem.className = 'px-3.5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 cursor-pointer flex items-center justify-between border-b border-slate-800 transition select-none';
            if (q.length > 0) {
                topItem.innerHTML = `
                    <span class="text-xs font-bold">+ Tambahkan "<strong>${escapeHtml(q)}</strong>"</span>
                    <span class="text-[10px] font-semibold opacity-80 uppercase tracking-wider">Register On-the-Fly</span>
                `;
                topItem.onmousedown = (e) => {
                    e.preventDefault();
                    selectCat(q);
                };
            } else {
                topItem.innerHTML = `
                    <span class="text-xs font-bold">+ Tambah Kategori Baru...</span>
                    <span class="text-[10px] font-semibold opacity-80 uppercase tracking-wider">Ketik Nama</span>
                `;
                topItem.onmousedown = (e) => {
                    e.preventDefault();
                    input.focus();
                };
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
                    item.onmousedown = (e) => {
                        e.preventDefault();
                        selectCat(c);
                    };
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
        input.addEventListener('blur', () => {
            setTimeout(() => dropdown.classList.add('hidden'), 200);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        setupCategoryCombobox('category', 'categoryDropdown', availableCategories);
    });
</script>
@endsection
