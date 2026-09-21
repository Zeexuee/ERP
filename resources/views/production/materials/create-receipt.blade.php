@extends('layouts.app', ['title' => 'Input Barang Masuk'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Input Barang Masuk</h2>
            <p class="text-xs text-slate-700 mt-0.5">Pencatatan pasokan bahan baku yang tiba di gudang pabrik Gaharu Sana'i.</p>
        </div>
        <a href="{{ route('production.materials.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
            ← Kembali ke Warehouse
        </a>
    </div>

    <!-- Form Container -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <form action="{{ route('production.materials.store-receipt') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Hidden input for selected material ID -->
            <input type="hidden" id="material_id" name="material_id" value="{{ old('material_id') }}">

            <!-- Nomor Bukti Masuk & Tanggal -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-800 mb-1">
                        No. Bukti Penerimaan
                    </label>
                    <input 
                        type="text" 
                        value="{{ $nextNumber }}" 
                        disabled 
                        class="w-full px-3 py-2 rounded-xl text-xs bg-slate-100/80 border border-slate-300 text-slate-900 font-mono font-bold"
                    >
                    <span class="text-[10px] text-slate-700 mt-0.5 block">Nomor unik otomatis dari sistem.</span>
                </div>

                <div>
                    <label for="received_date" class="block text-xs font-semibold text-slate-800 mb-1">
                        Tanggal Penerimaan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="received_date" 
                        name="received_date" 
                        value="{{ old('received_date') }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 font-medium border-slate-300"
                    >
                    @error('received_date')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Nama Bahan Baku (Searchable Combobox) -->
            <div class="space-y-3">
                <div class="relative overflow-visible z-30">
                    <label for="material_name" class="block text-xs font-semibold text-slate-800 mb-1">
                        Nama Bahan Baku <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="material_name" 
                        name="material_name" 
                        value="{{ old('material_name') }}" 
                        required 
                        autocomplete="off"
                        oninput="onMaterialNameInput(this.value)"
                        onfocus="onMaterialNameInput(this.value)"
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                    >
                    
                    <!-- Live Suggestions Dropdown Solid White Non-Transparent -->
                    <div id="materialSuggestions" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-56 overflow-y-auto">
                        <!-- Populated by JavaScript -->
                    </div>

                    @error('material_name')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Form Kategori & Satuan (Hanya muncul jika Bahan Baku Baru) -->
                <div id="newMaterialFields" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="relative overflow-visible">
                        <label for="category" class="block text-xs font-semibold text-slate-800 mb-1">
                            Kategori Bahan Baku <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="text" 
                                id="category" 
                                name="category" 
                                value="{{ old('category', '') }}"
                                autocomplete="off"
                                placeholder="Pilih / ketik kategori..."
                                class="w-full px-3 py-2 pr-7 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300 cursor-pointer"
                            >
                            <div class="absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div id="categoryReceiptDropdown" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-48 overflow-y-auto"></div>
                        @error('category')
                            <span class="text-red-600 text-[10px]">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="unit" class="block text-xs font-semibold text-slate-800 mb-1">
                            Satuan Barang <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="unit" 
                            name="unit" 
                            onchange="onUnitChange(this.value)"
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                        >
                            <option value="">Pilih Satuan...</option>
                            <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>kg (Kilogram)</option>
                            <option value="gram" {{ old('unit') == 'gram' ? 'selected' : '' }}>gram (Gram)</option>
                            <option value="unit" {{ old('unit') == 'unit' ? 'selected' : '' }}>unit (Unit)</option>
                            <option value="pcs" {{ old('unit') == 'pcs' ? 'selected' : '' }}>pcs (Pieces)</option>
                            <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>liter (Liter)</option>
                            <option value="meter" {{ old('unit') == 'meter' ? 'selected' : '' }}>meter (Meter)</option>
                            <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>box (Dus)</option>
                            <option value="karung" {{ old('unit') == 'karung' ? 'selected' : '' }}>karung (Karung)</option>
                        </select>
                        @error('unit')
                            <span class="text-red-600 text-[10px]">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Kuantitas: Jumlah yang Dipesan & Jumlah Masuk (Sesuai Fakta Lapangan) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="ordered_quantity" class="block text-xs font-semibold text-slate-800 mb-1">
                        Jumlah yang Dipesan
                    </label>
                    <div class="relative">
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            id="ordered_quantity" 
                            name="ordered_quantity" 
                            value="{{ old('ordered_quantity') }}" 
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300 pr-16"
                        >
                        <span class="unitBadgeDisplay absolute right-3 top-2 text-xs font-bold text-slate-800">
                            kg
                        </span>
                    </div>
                    @error('ordered_quantity')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="quantity" class="block text-xs font-semibold text-slate-800 mb-1">
                        Jumlah Masuk (Sesuai Fakta Lapangan) <span class="text-red-500">*</span>
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
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300 pr-16"
                        >
                        <span class="unitBadgeDisplay absolute right-3 top-2 text-xs font-bold text-slate-800">
                            kg
                        </span>
                    </div>
                    @error('quantity')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Estimasi Biaya & Asal / Pemasok -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="unit_cost" class="block text-xs font-semibold text-slate-800 mb-1">
                        Harga Beli / Biaya per Unit (Rp)
                    </label>
                    <input 
                        type="number" 
                        step="100" 
                        min="0" 
                        id="unit_cost" 
                        name="unit_cost" 
                        value="{{ old('unit_cost') }}" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono text-slate-900 border-slate-300"
                    >
                    <span class="text-[10px] text-slate-700 mt-0.5 block">Kosongkan jika sama dengan harga perolehan sebelumnya.</span>
                    @error('unit_cost')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="source_or_supplier" class="block text-xs font-semibold text-slate-800 mb-1">
                        Asal / Pemasok (Supplier) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="source_or_supplier" 
                        name="source_or_supplier" 
                        value="{{ old('source_or_supplier') }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-medium"
                    >
                    @error('source_or_supplier')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <!-- Catatan Fisik / Kualitas -->
            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-800 mb-1">
                    Catatan Kondisi / Kualitas Bahan
                </label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2" 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                >{{ old('notes') }}</textarea>
                @error('notes')
                    <span class="text-red-600 text-[10px]">{{ $message }}</span>
                @enderror
            </div>

            <!-- Upload Foto Barang Masuk -->
            <div>
                <label for="image" class="block text-xs font-semibold text-slate-800 mb-1">
                    Foto / Gambar Barang Masuk
                </label>
                <input 
                    type="file" 
                    id="image" 
                    name="image" 
                    accept="image/*"
                    onchange="previewImage(this)"
                    class="w-full px-3 py-2 rounded-xl text-xs border border-slate-300 bg-white text-slate-800 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer"
                >
                <div id="imagePreviewContainer" class="hidden mt-2">
                    <img id="imagePreview" src="#" alt="Pratinjau Foto" class="h-32 rounded-xl border border-slate-300 object-cover shadow-sm">
                </div>
                @error('image')
                    <span class="text-red-600 text-[10px]">{{ $message }}</span>
                @enderror
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-900/10">
                <a href="{{ route('production.materials.index') }}" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
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
    const availableMaterials = @json($materials);

    function onMaterialNameInput(value) {
        const suggestionsBox = document.getElementById('materialSuggestions');
        const materialIdInput = document.getElementById('material_id');
        const unitCostInput = document.getElementById('unit_cost');

        const query = value.trim().toLowerCase();

        const matches = query ? availableMaterials.filter(mat => 
            mat.name.toLowerCase().includes(query) || 
            mat.code.toLowerCase().includes(query)
        ) : availableMaterials;

        let html = '';
        let exactMatch = null;

        // Opsi + Tambah Baru WAJIB selalu diposisikan di URUTAN PERTAMA / PALING ATAS (Rule 7.1)
        const newLabel = value.trim() ? `"${value.trim()}"` : 'Bahan Baku';
        html += `
            <div 
                onclick="selectNewMaterial('${escapeJs(value.trim())}')" 
                class="px-3.5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 cursor-pointer border-b border-slate-800 transition flex items-center justify-between"
            >
                <span class="text-xs font-bold">+ Tambah ${newLabel} Baru</span>
                <span class="text-[10px] font-semibold opacity-80">Registrasi On-the-Fly</span>
            </div>
        `;

        matches.forEach(mat => {
            if (query && mat.name.toLowerCase() === query) {
                exactMatch = mat;
            }
            html += `
                <div 
                    onclick="selectMaterial(${mat.id}, '${escapeJs(mat.name)}', '${escapeJs(mat.unit)}', ${mat.unit_cost || 0})" 
                    class="px-3.5 py-2.5 hover:bg-slate-100 cursor-pointer border-b border-slate-100 last:border-0 transition"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">[${mat.code}] ${mat.name}</span>
                        <span class="text-[11px] font-semibold text-slate-700">Stok: ${mat.stock_quantity} ${mat.unit}</span>
                    </div>
                </div>
            `;
        });

        suggestionsBox.innerHTML = html;
        suggestionsBox.classList.remove('hidden');

        if (exactMatch) {
            materialIdInput.value = exactMatch.id;
            showNewMaterialFields(false, exactMatch.unit);
            if (unitCostInput && !unitCostInput.value && exactMatch.unit_cost) {
                unitCostInput.value = exactMatch.unit_cost;
            }
        } else if (query) {
            materialIdInput.value = '';
            showNewMaterialFields(true);
        } else {
            materialIdInput.value = '';
            showNewMaterialFields(false);
        }
    }

    function selectMaterial(id, name, unit, unitCost) {
        document.getElementById('material_id').value = id;
        document.getElementById('material_name').value = name;
        document.getElementById('materialSuggestions').classList.add('hidden');

        showNewMaterialFields(false, unit);

        const unitCostInput = document.getElementById('unit_cost');
        if (unitCostInput && unitCost) {
            unitCostInput.value = unitCost;
        }
    }

    function selectNewMaterial(name) {
        document.getElementById('material_id').value = '';
        document.getElementById('material_name').value = name;
        document.getElementById('materialSuggestions').classList.add('hidden');
        showNewMaterialFields(true);
    }

    function showNewMaterialFields(isNew, unitValue = 'kg') {
        const newFields = document.getElementById('newMaterialFields');
        const unitSelect = document.getElementById('unit');

        if (isNew) {
            newFields.classList.remove('hidden');
            if (unitSelect) {
                unitSelect.value = unitSelect.value || 'kg';
                onUnitChange(unitSelect.value);
            }
        } else {
            newFields.classList.add('hidden');
            if (unitSelect && unitValue) {
                unitSelect.value = unitValue;
            }
            onUnitChange(unitValue);
        }
    }

    function onUnitChange(unit) {
        const badges = document.querySelectorAll('.unitBadgeDisplay');
        badges.forEach(b => {
            b.innerText = unit || 'kg';
        });
    }

    function escapeJs(str) {
        return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    document.addEventListener('click', (e) => {
        const input = document.getElementById('material_name');
        const suggestionsBox = document.getElementById('materialSuggestions');
        if (input && suggestionsBox && !input.contains(e.target) && !suggestionsBox.contains(e.target)) {
            suggestionsBox.classList.add('hidden');
        }
    });

    function previewImage(input) {
        const container = document.getElementById('imagePreviewContainer');
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                container.classList.remove('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        } else {
            container.classList.add('hidden');
        }
    }

    let canvas, ctx, signatureInput;
    let isDrawing = false;

    function initSignatureCanvas() {
        canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;

        ctx = canvas.getContext('2d');
        signatureInput = document.getElementById('signature_data');

        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const r = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - r.left,
                y: clientY - r.top
            };
        }

        function startDraw(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            if (e.cancelable) e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            if (e.cancelable) e.preventDefault();

            signatureInput.value = canvas.toDataURL('image/png');
        }

        function stopDraw() {
            if (isDrawing) {
                isDrawing = false;
                signatureInput.value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDraw);
    }

    function clearSignature() {
        if (!canvas || !ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        if (signatureInput) signatureInput.value = '';
    }

    // Searchable Combobox with Register On-the-Fly for Categories (Rule 7.1)
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

    window.addEventListener('DOMContentLoaded', () => {
        initSignatureCanvas();
        setupCategoryCombobox('category', 'categoryReceiptDropdown', @json($categories));
        const matNameInput = document.getElementById('material_name');
        if (matNameInput && matNameInput.value) {
            onMaterialNameInput(matNameInput.value);
        } else {
            showNewMaterialFields(true);
        }
    });
</script>
@endsection
