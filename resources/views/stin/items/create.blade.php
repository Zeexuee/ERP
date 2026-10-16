@extends('layouts.app', ['title' => 'Input Data Barang'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Back Button (Gaya Modul Produksi) -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Input Data Barang</h2>
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

    <!-- Form Container (Apple Glass Panel) -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <form action="{{ route('stin.items.store') }}" method="POST" id="itemForm" class="space-y-6">
            @csrf
            <input type="hidden" name="entry_type" id="entry_type" value="{{ old('entry_type', 'existing') }}">

            <!-- Opsi Pilihan Mode: Tambah ke Barang yang Ada vs Buat Barang Baru -->
            <div>
                <label class="block text-xs font-semibold text-slate-800 mb-2">
                    Pilihan input barang
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label id="modeCardExisting" onclick="switchMode('existing')" class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-900 bg-slate-900 text-white p-4 transition shadow-xs">
                        <input type="radio" name="mode_radio" value="existing" class="mt-0.5 accent-white" checked>
                        <div>
                            <span class="block text-xs font-bold">Tambah ke Barang yang Sudah Ada</span>
                        </div>
                    </label>

                    <label id="modeCardNew" onclick="switchMode('new')" class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-300 bg-white text-slate-900 p-4 transition hover:bg-slate-50 shadow-xs">
                        <input type="radio" name="mode_radio" value="new" class="mt-0.5 accent-slate-900">
                        <div>
                            <span class="block text-xs font-bold">Buat Barang Baru</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- OPSI 1: TAMBAH KE BARANG YANG SUDAH ADA -->
            <div id="section-existing" class="space-y-4 pt-2 border-t border-slate-900/10">
                <!-- Searchable Combobox Nama Barang -->
                <div class="space-y-3">
                    <div class="relative overflow-visible z-30">
                        <label for="item_search_input" class="block text-xs font-semibold text-slate-800 mb-1">
                            Nama Barang Gudang <span class="text-slate-400">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="item_search_input" 
                            autocomplete="off"
                            placeholder="Ketik untuk mencari barang atau SKU..."
                            oninput="onItemSearchInput(this.value)"
                            onfocus="onItemSearchInput(this.value)"
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                        >

                        <!-- Live Suggestions Dropdown Solid White Non-Transparent -->
                        <div id="itemSuggestions" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-56 overflow-y-auto">
                            <!-- Populated by JavaScript -->
                        </div>

                        <input type="hidden" name="warehouse_item_id" id="warehouse_item_id" value="{{ old('warehouse_item_id') }}">
                    </div>

                    <!-- Indikator Informasi Barang Terpilih -->
                    <div id="selectedItemCard" class="hidden p-3.5 rounded-2xl border border-slate-300 bg-white/80 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900" id="selectedItemName">-</span>
                            <span class="font-mono text-xs font-semibold text-slate-600" id="selectedItemSku">-</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-[11px] pt-2 border-t border-slate-100">
                            <div>
                                <span class="text-slate-500 block">Kategori</span>
                                <span class="font-semibold text-slate-800" id="selectedItemCategory">-</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Stok Saat Ini</span>
                                <span class="font-bold text-slate-900" id="selectedItemStock">-</span>
                            </div>
                            <div>
                                <span class="text-slate-500 block">Harga HPP</span>
                                <span class="font-semibold text-slate-800" id="selectedItemPrice">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input Kuantitas Masuk -->
                <div>
                    <label for="quantity_add" class="block text-xs font-semibold text-slate-800 mb-1">
                        Jumlah Masuk <span class="text-slate-400">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            min="1" 
                            id="quantity_add" 
                            name="quantity_add" 
                            value="{{ old('quantity_add', 1) }}" 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono font-bold text-slate-900 border-slate-300 pr-16"
                        >
                        <span class="unitBadgeDisplay absolute right-3 top-2 text-xs font-bold text-slate-500">
                            pcs
                        </span>
                    </div>
                </div>
            </div>

            <!-- OPSI 2: BUAT BARANG BARU -->
            <div id="section-new" class="hidden space-y-4 pt-2 border-t border-slate-900/10">
                <!-- Nama Barang Baru -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-800 mb-1">
                        Nama Barang <span class="text-slate-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="{{ old('name') }}" 
                        placeholder="Nama barang baru" 
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
                            value="{{ old('category', '') }}" 
                            autocomplete="off"
                            placeholder="Pilih atau ketik kategori baru..." 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300 cursor-pointer"
                        >
                    </div>
                    <!-- Dropdown Solid White List & Register On-the-Fly -->
                    <div id="categoryDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"></div>
                </div>

                <!-- Jumlah Stok Awal & Satuan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="initial_stock" class="block text-xs font-semibold text-slate-800 mb-1">
                            Jumlah Barang
                        </label>
                        <div class="relative">
                            <input 
                                type="number" 
                                min="0" 
                                id="initial_stock" 
                                name="initial_stock" 
                                value="{{ old('initial_stock', 0) }}" 
                                class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono font-bold text-slate-900 border-slate-300 pr-16"
                            >
                            <span class="newUnitBadgeDisplay absolute right-3 top-2 text-xs font-bold text-slate-500">
                                pcs
                            </span>
                        </div>
                    </div>

                    <div>
                        <label for="unit" class="block text-xs font-semibold text-slate-800 mb-1">
                            Satuan
                        </label>
                        <select 
                            id="unit" 
                            name="unit" 
                            onchange="onNewUnitChange(this.value)"
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                        >
                            <option value="pcs" {{ old('unit', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs (Pieces)</option>
                            <option value="botol" {{ old('unit') == 'botol' ? 'selected' : '' }}>botol (Botol)</option>
                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>box (Dus)</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg (Kilogram)</option>
                            <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>gram (Gram)</option>
                            <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>liter (Liter)</option>
                            <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>meter (Meter)</option>
                            <option value="karung" {{ old('unit') == 'karung' ? 'selected' : '' }}>karung (Karung)</option>
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
                            value="{{ old('unit_price') }}" 
                            placeholder="0" 
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
                            value="{{ old('sku') }}" 
                            placeholder="Dibuat otomatis jika dikosongkan" 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300"
                        >
                    </div>
                </div>
            </div>

            <!-- DETAIL WAKTU, PENANGGUNG JAWAB (PIC) & TANDA TANGAN (Shared) -->
            <div class="pt-4 border-t border-slate-900/10 space-y-4">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest pb-1 border-b border-slate-900/10">
                    Detail Waktu & Penanggung Jawab
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kolom Kiri: Waktu, PIC, dan Catatan -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-800 mb-1">
                                Waktu Proses
                            </label>
                            <input 
                                type="text" 
                                value="{{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB" 
                                disabled 
                                class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100/80 border border-slate-300 text-slate-900 font-bold"
                            >
                        </div>

                        <div>
                            <label for="pic_name" class="block text-xs font-semibold text-slate-800 mb-1">
                                PIC <span class="text-slate-400">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="pic_name" 
                                name="pic_name"
                                required 
                                class="w-full px-3 py-2 rounded-xl text-xs apple-input font-bold text-slate-900 border-slate-300"
                                placeholder="Nama"
                            >
                        </div>

                        <div>
                            <label for="notes" class="block text-xs font-semibold text-slate-800 mb-1">
                                Catatan
                            </label>
                            <textarea 
                                name="notes" 
                                id="notes" 
                                rows="2" 
                                class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300" 
                                placeholder="Catatan proses..."
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Tanda Tangan Digital PIC -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-slate-800">
                                Tanda Tangan Digital 
                            </label>
                            <button type="button" id="clearSignatureBtn" class="text-[11px] font-bold text-slate-900 hover:underline">
                                HAPUS
                            </button>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-300 space-y-2">
                            <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 relative">
                                <canvas id="signatureCanvas" width="340" height="150" class="w-full h-[150px] cursor-crosshair"></canvas>
                            </div>
                            <div class="text-[10px] text-slate-500 font-medium text-center">
                                Gunakan mouse atau sentuhan jari
                            </div>
                        </div>
                        <input type="hidden" name="signature_data" id="signatureData">
                    </div>
                </div>
            </div>

            <!-- Action Buttons (Gaya Modul Produksi) -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-900/10">
                <a href="{{ route('stin.items.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </a>
                <button type="submit" id="submitBtn" class="px-5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition">
                    Simpan Data Barang
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    const availableItems = @json($existingItems);
    const availableCategories = @json($existingCategories ?? []);

    function switchMode(mode) {
        const hiddenInput = document.getElementById('entry_type');
        const cardExisting = document.getElementById('modeCardExisting');
        const cardNew = document.getElementById('modeCardNew');
        const secExisting = document.getElementById('section-existing');
        const secNew = document.getElementById('section-new');

        hiddenInput.value = mode;

        if (mode === 'existing') {
            cardExisting.className = 'flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-900 bg-slate-900 text-white p-4 transition shadow-xs';
            cardExisting.querySelector('input[type="radio"]').checked = true;
            cardNew.className = 'flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-300 bg-white text-slate-900 p-4 transition hover:bg-slate-50 shadow-xs';
            cardNew.querySelector('input[type="radio"]').checked = false;
            secExisting.classList.remove('hidden');
            secNew.classList.add('hidden');
        } else {
            cardNew.className = 'flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-900 bg-slate-900 text-white p-4 transition shadow-xs';
            cardNew.querySelector('input[type="radio"]').checked = true;
            cardExisting.className = 'flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-300 bg-white text-slate-900 p-4 transition hover:bg-slate-50 shadow-xs';
            cardExisting.querySelector('input[type="radio"]').checked = false;
            secNew.classList.remove('hidden');
            secExisting.classList.add('hidden');
        }
    }

    function onItemSearchInput(value) {
        const suggestionsBox = document.getElementById('itemSuggestions');
        const query = value.trim().toLowerCase();

        const matches = query ? availableItems.filter(item => 
            item.name.toLowerCase().includes(query) || 
            (item.sku && item.sku.toLowerCase().includes(query)) ||
            (item.category && item.category.toLowerCase().includes(query))
        ) : availableItems;

        let html = '';

        if (matches.length === 0) {
            html = '<div class="px-3.5 py-3 text-xs text-slate-500 text-center">Tidak ada barang yang cocok.</div>';
        } else {
            matches.forEach(item => {
                const formattedPrice = new Intl.NumberFormat('id-ID').format(item.unit_price || 0);
                html += `
                    <div 
                        onclick="selectItem(${item.id}, '${escapeJs(item.name)}', '${escapeJs(item.sku || '')}', '${escapeJs(item.category || '-')}', ${item.current_stock || 0}, '${escapeJs(item.unit || 'pcs')}', '${formattedPrice}')" 
                        class="px-3.5 py-2.5 hover:bg-slate-100 cursor-pointer border-b border-slate-100 last:border-0 transition"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-900">${item.name}</span>
                            <span class="font-mono text-[11px] font-semibold text-slate-600">${item.sku || '-'}</span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500 mt-0.5">
                            <span>${item.category || '-'}</span>
                            <span>Stok: ${item.current_stock} ${item.unit || 'pcs'} | Rp ${formattedPrice}</span>
                        </div>
                    </div>
                `;
            });
        }

        suggestionsBox.innerHTML = html;
        suggestionsBox.classList.remove('hidden');
    }

    function selectItem(id, name, sku, category, stock, unit, price) {
        document.getElementById('warehouse_item_id').value = id;
        document.getElementById('item_search_input').value = name;
        document.getElementById('itemSuggestions').classList.add('hidden');

        document.getElementById('selectedItemName').textContent = name;
        document.getElementById('selectedItemSku').textContent = sku;
        document.getElementById('selectedItemCategory').textContent = category;
        document.getElementById('selectedItemStock').textContent = stock + ' ' + unit;
        document.getElementById('selectedItemPrice').textContent = 'Rp ' + price;
        document.getElementById('selectedItemCard').classList.remove('hidden');

        const badges = document.querySelectorAll('.unitBadgeDisplay');
        badges.forEach(b => b.textContent = unit);
    }

    function onNewUnitChange(unit) {
        const badge = document.querySelector('.newUnitBadgeDisplay');
        if (badge) {
            badge.textContent = unit || 'pcs';
        }
    }

    // List & Register On-the-Fly untuk Kategori Barang
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

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    document.addEventListener('click', (e) => {
        const input = document.getElementById('item_search_input');
        const box = document.getElementById('itemSuggestions');
        if (input && box && !input.contains(e.target) && !box.contains(e.target)) {
            box.classList.add('hidden');
        }
    });

    // Inisialisasi Tanda Tangan Digital & Script Pendukung
    document.addEventListener('DOMContentLoaded', () => {
        const initialMode = "{{ old('entry_type', 'existing') }}";
        switchMode(initialMode);

        const oldItemId = "{{ old('warehouse_item_id') }}";
        if (oldItemId) {
            const found = availableItems.find(i => String(i.id) === String(oldItemId));
            if (found) {
                const formattedPrice = new Intl.NumberFormat('id-ID').format(found.unit_price || 0);
                selectItem(found.id, found.name, found.sku || '', found.category || '-', found.current_stock || 0, found.unit || 'pcs', formattedPrice);
            }
        }

        // Setup kategori combobox dengan list & register on-the-fly
        setupCategoryCombobox('category', 'categoryDropdown', availableCategories);

        // Digital Signature Canvas
        const canvas = document.getElementById('signatureCanvas');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const signatureInput = document.getElementById('signatureData');
        const form = document.getElementById('itemForm');

        if (canvas) {
            const ctx = canvas.getContext('2d');
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);

            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';

            let isDrawing = false;
            let hasSignature = false;

            function getCoordinates(e) {
                const r = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - r.left,
                    y: clientY - r.top
                };
            }

            canvas.addEventListener('mousedown', (e) => {
                isDrawing = true;
                hasSignature = true;
                const coords = getCoordinates(e);
                ctx.beginPath();
                ctx.moveTo(coords.x, coords.y);
                e.preventDefault();
            });

            canvas.addEventListener('mousemove', (e) => {
                if (!isDrawing) return;
                const coords = getCoordinates(e);
                ctx.lineTo(coords.x, coords.y);
                ctx.stroke();
                e.preventDefault();
            });

            const stopDrawing = () => { isDrawing = false; };
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseleave', stopDrawing);

            canvas.addEventListener('touchstart', (e) => {
                isDrawing = true;
                hasSignature = true;
                const coords = getCoordinates(e);
                ctx.beginPath();
                ctx.moveTo(coords.x, coords.y);
                e.preventDefault();
            }, { passive: false });

            canvas.addEventListener('touchmove', (e) => {
                if (!isDrawing) return;
                const coords = getCoordinates(e);
                ctx.lineTo(coords.x, coords.y);
                ctx.stroke();
                e.preventDefault();
            }, { passive: false });

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
</script>
@endsection
