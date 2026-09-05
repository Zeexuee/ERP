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
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4 relative z-10 overflow-visible">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Informasi Target & Perintah Kerja
            </h3>

            <!-- Grid 1: No Batch & Target Produk Jadi (Highest Z-Index) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 relative z-50 overflow-visible">
                <!-- 1. Nomor Batch Produksi (Manual / Otomatis) -->
                <div>
                    <label for="batch_number" class="block text-xs font-semibold text-slate-700 mb-1">
                        No. Batch Produksi
                    </label>
                    <input 
                        type="text" 
                        id="batch_number" 
                        name="batch_number" 
                        value="{{ old('batch_number', $nextBatchNumber) }}" 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono font-bold text-slate-900 border-slate-300"
                    >
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Dapat diisi manual atau otomatis oleh sistem.</span>
                    @error('batch_number')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 2. Target Produk Jadi (Searchable Solid Combobox) -->
                <div class="sm:col-span-2 relative overflow-visible z-50">
                    <label for="product_display" class="block text-xs font-semibold text-slate-700 mb-1">
                        Target Produk Jadi <span class="text-red-500">*</span>
                    </label>

                    <input type="hidden" id="product_id" name="product_id" value="{{ old('product_id') }}">

                    <input 
                        type="text" 
                        id="product_display" 
                        autocomplete="off"
                        oninput="onProductInput(this.value)"
                        onfocus="onProductInput(this.value)"
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                    >
                    
                    <!-- Live Suggestions Dropdown Solid White Non-Transparent -->
                    <div id="productSuggestions" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-60 overflow-y-auto">
                        <!-- Populated by JavaScript -->
                    </div>

                    @error('product_id')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Grid 2: Kuantitas, Tanggal Mulai, Estimasi Selesai -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 relative z-20 overflow-visible">
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
                            class="w-full px-3 py-2 rounded-xl text-xs apple-input font-mono pr-12 text-slate-900 border-slate-300"
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
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
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
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                    >
                    @error('target_completion_date')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Grid 3: Referensi Antrean Sales & PIC -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 relative z-40 overflow-visible">
                <!-- 3. Referensi Antrean Sales (Searchable Solid Combobox) -->
                <div class="relative overflow-visible z-50">
                    <label for="sales_request_display" class="block text-xs font-semibold text-slate-700 mb-1">
                        Referensi Antrean Sales (Opsional)
                    </label>

                    <input type="hidden" id="production_request_id" name="production_request_id" value="{{ old('production_request_id') }}">

                    <input 
                        type="text" 
                        id="sales_request_display" 
                        autocomplete="off"
                        oninput="onSalesRequestInput(this.value)"
                        onfocus="onSalesRequestInput(this.value)"
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input bg-white text-slate-900 font-medium border-slate-300"
                    >
                    
                    <!-- Live Suggestions Dropdown Solid White Non-Transparent -->
                    <div id="salesRequestSuggestions" class="hidden absolute top-full left-0 right-0 mt-1.5 bg-white border border-slate-300 shadow-2xl rounded-2xl z-50 overflow-hidden max-h-60 overflow-y-auto">
                        <!-- Populated by JavaScript -->
                    </div>

                    @error('production_request_id')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>

                <!-- 4. PIC (Bebas Input Default) -->
                <div class="relative z-10">
                    <label for="pic_name" class="block text-xs font-semibold text-slate-700 mb-1">
                        PIC <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="pic_name" 
                        name="pic_name" 
                        value="{{ old('pic_name') }}" 
                        required 
                        class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-medium"
                    >
                    @error('pic_name')
                        <span class="text-red-600 text-[10px]">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Card Preview Detail Pesanan Sales (Muncul otomatis saat Referensi Pesanan dipilih) -->
            <div id="salesOrderPreviewCard" class="hidden p-4 rounded-2xl bg-slate-100/90 border border-slate-300 text-xs space-y-3 relative z-10 transition">
                <div class="flex items-center justify-between border-b border-slate-300/80 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900">Detail Permintaan & Order Sales</span>
                        <span id="previewRequestBadge" class="px-2.5 py-0.5 rounded-lg font-mono text-[11px] font-bold bg-slate-900 text-white"></span>
                    </div>
                    <span id="previewOrderNumber" class="font-mono text-xs font-bold text-slate-800"></span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase tracking-wider font-semibold">Pelanggan</span>
                        <span id="previewCustomerName" class="font-bold text-slate-900 block mt-0.5"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase tracking-wider font-semibold">Tanggal Permintaan</span>
                        <span id="previewRequestDate" class="font-mono text-slate-800 block mt-0.5"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[10px] uppercase tracking-wider font-semibold">Estimasi Target Selesai</span>
                        <span id="previewTargetDate" class="font-mono text-slate-800 block mt-0.5"></span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-300/80">
                    <span class="text-slate-500 block text-[10px] uppercase tracking-wider font-semibold mb-1.5">Rincian Produk yang Dipesan</span>
                    <div id="previewItemsContainer" class="space-y-1.5">
                        <!-- Populated dynamically via JavaScript -->
                    </div>
                </div>
            </div>

            <div class="relative z-10">
                <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1">
                    Catatan Instruksi Kerja / Formula Olahan
                </label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2" 
                    class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Alokasi Komposisi Bahan Baku (BOM) -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4 relative z-0">
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
    const availableProducts = @json($products);
    const availableRequests = @json($pendingRequests);
    const availableMaterials = @json($materials);
    let rowIndex = 0;

    // --- Searchable Combobox logic untuk Target Produk Jadi ---
    function onProductInput(value) {
        const suggestionsBox = document.getElementById('productSuggestions');
        const query = value.trim().toLowerCase();

        const matches = query ? availableProducts.filter(p => 
            p.name.toLowerCase().includes(query) || 
            (p.sku && p.sku.toLowerCase().includes(query))
        ) : availableProducts;

        let html = '';

        // Rule 7.1: Opsi + Tambah Baru WAJIB selalu diposisikan di URUTAN PERTAMA / PALING ATAS
        const newLabel = value.trim() ? `"${value.trim()}"` : 'Produk Jadi';
        html += `
            <div 
                onclick="selectNewProduct('${escapeJs(value.trim())}')" 
                class="px-3.5 py-2.5 bg-slate-900 text-white hover:bg-slate-800 cursor-pointer border-b border-slate-800 transition flex items-center justify-between"
            >
                <span class="text-xs font-bold">+ Tambah ${newLabel} Baru</span>
                <span class="text-[10px] font-semibold opacity-80">Registrasi On-the-Fly</span>
            </div>
        `;

        matches.forEach(prod => {
            const skuLabel = prod.sku ? `[${prod.sku}] ` : '';
            const unitLabel = prod.unit ? prod.unit : 'unit';
            html += `
                <div 
                    onclick="selectProduct(${prod.id}, '${escapeJs(skuLabel + prod.name)}', '${escapeJs(unitLabel)}')" 
                    class="px-3.5 py-2.5 hover:bg-slate-100 cursor-pointer border-b border-slate-100 last:border-0 transition"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">${skuLabel}${prod.name}</span>
                        <span class="text-[11px] font-semibold text-slate-600">Satuan: ${unitLabel}</span>
                    </div>
                </div>
            `;
        });

        suggestionsBox.innerHTML = html;
        suggestionsBox.classList.remove('hidden');
    }

    function selectProduct(id, displayName, unit) {
        document.getElementById('product_id').value = id;
        document.getElementById('product_display').value = displayName;
        document.getElementById('productSuggestions').classList.add('hidden');
        document.getElementById('targetUnitBadge').innerText = unit || 'unit';
    }

    function selectNewProduct(name) {
        alert('Untuk menambah Produk Jadi baru, Anda dapat mendaftarkannya terlebih dahulu pada Master Produk atau menghubungi Admin.');
        document.getElementById('productSuggestions').classList.add('hidden');
    }

    // --- Searchable Combobox logic untuk Referensi Antrean Sales ---
    function onSalesRequestInput(value) {
        const suggestionsBox = document.getElementById('salesRequestSuggestions');
        const query = value.trim().toLowerCase();

        const matches = query ? availableRequests.filter(req => {
            const reqNum = req.request_number ? req.request_number.toLowerCase() : '';
            const orderNum = req.sales_order && req.sales_order.order_number ? req.sales_order.order_number.toLowerCase() : '';
            const custName = req.sales_order && req.sales_order.customer ? req.sales_order.customer.name.toLowerCase() : '';
            return reqNum.includes(query) || orderNum.includes(query) || custName.includes(query);
        }) : availableRequests;

        let html = '';

        // Opsi paling atas: Bukan dari Pesanan Sales
        html += `
            <div 
                onclick="selectSalesRequest('', '-- Bukan dari Pesanan Sales (Produksi Stok Pabrik) --')" 
                class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 cursor-pointer border-b border-slate-200 transition"
            >
                <span class="text-xs font-semibold text-slate-700">-- Bukan dari Pesanan Sales (Produksi Stok Pabrik) --</span>
            </div>
        `;

        matches.forEach(req => {
            const reqNum = req.request_number || '-';
            const orderNum = req.sales_order?.order_number || '-';
            const custName = req.sales_order?.customer?.name || 'Umum';
            const displayText = `Permintaan #${reqNum} (Order: ${orderNum} - ${custName})`;

            html += `
                <div 
                    onclick="selectSalesRequest(${req.id}, '${escapeJs(displayText)}')" 
                    class="px-3.5 py-2.5 hover:bg-slate-100 cursor-pointer border-b border-slate-100 last:border-0 transition"
                >
                    <span class="text-xs font-bold text-slate-900">${displayText}</span>
                </div>
            `;
        });

        suggestionsBox.innerHTML = html;
        suggestionsBox.classList.remove('hidden');
    }

    function selectSalesRequest(id, displayText) {
        document.getElementById('production_request_id').value = id;
        document.getElementById('sales_request_display').value = displayText;
        document.getElementById('salesRequestSuggestions').classList.add('hidden');

        const foundReq = availableRequests.find(r => r.id == id);
        renderSalesOrderPreview(foundReq);
    }

    function renderSalesOrderPreview(req) {
        const card = document.getElementById('salesOrderPreviewCard');
        if (!card) return;

        if (!req || !req.id) {
            card.classList.add('hidden');
            return;
        }

        const reqNum = req.request_number || '-';
        const orderNum = req.sales_order?.order_number || '-';
        const custName = req.sales_order?.customer?.name || 'Umum';
        const reqDate = req.requested_date ? formatDate(req.requested_date) : '-';
        const targetDate = req.target_date ? formatDate(req.target_date) : '-';

        document.getElementById('previewRequestBadge').innerText = `#${reqNum}`;
        document.getElementById('previewOrderNumber').innerText = `Order: ${orderNum}`;
        document.getElementById('previewCustomerName').innerText = custName;
        document.getElementById('previewRequestDate').innerText = reqDate;
        document.getElementById('previewTargetDate').innerText = targetDate;

        const itemsContainer = document.getElementById('previewItemsContainer');
        let itemsHtml = '';

        const items = req.sales_order?.items || [];
        if (items.length > 0) {
            items.forEach(item => {
                const prodName = item.product?.name || 'Produk Jadi';
                const prodSku = item.product?.sku ? `[${item.product.sku}] ` : '';
                const qty = item.quantity || 0;
                const unit = item.product?.unit || 'unit';

                itemsHtml += `
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200 text-xs">
                        <span class="font-bold text-slate-900">${prodSku}${prodName}</span>
                        <span class="font-mono font-bold text-slate-800">${parseFloat(qty).toFixed(2)} ${unit}</span>
                    </div>
                `;
            });
        } else {
            itemsHtml = `<span class="text-slate-500 text-xs italic">Tidak ada rincian item pesanan spesifik.</span>`;
        }

        itemsContainer.innerHTML = itemsHtml;
        card.classList.remove('hidden');

        // Otomatis isikan Target Produk dan Target Kuantitas jika item pesanan ada
        if (items.length > 0) {
            const firstItem = items[0];
            if (firstItem.product && firstItem.product.id) {
                const prod = availableProducts.find(p => p.id == firstItem.product.id);
                if (prod) {
                    const skuLabel = prod.sku ? `[${prod.sku}] ` : '';
                    selectProduct(prod.id, `${skuLabel}${prod.name}`, prod.unit);
                }
            }
            if (firstItem.quantity) {
                const targetQtyInput = document.getElementById('target_quantity');
                if (targetQtyInput) {
                    targetQtyInput.value = parseFloat(firstItem.quantity).toFixed(2);
                }
            }
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        try {
            const d = new Date(dateString);
            if (isNaN(d.getTime())) return dateString;
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        } catch (e) {
            return dateString;
        }
    }

    function escapeJs(str) {
        return (str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    // Modal / Outer Click listener
    document.addEventListener('click', (e) => {
        const prodInput = document.getElementById('product_display');
        const prodBox = document.getElementById('productSuggestions');
        if (prodInput && prodBox && !prodInput.contains(e.target) && !prodBox.contains(e.target)) {
            prodBox.classList.add('hidden');
        }

        const salesInput = document.getElementById('sales_request_display');
        const salesBox = document.getElementById('salesRequestSuggestions');
        if (salesInput && salesBox && !salesInput.contains(e.target) && !salesBox.contains(e.target)) {
            salesBox.classList.add('hidden');
        }
    });

    // --- BOM Material Rows Logic ---
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
        // Initialize Product Combobox value if old value exists
        const productIdVal = document.getElementById('product_id').value;
        if (productIdVal) {
            const foundProd = availableProducts.find(p => p.id == productIdVal);
            if (foundProd) {
                const skuLabel = foundProd.sku ? `[${foundProd.sku}] ` : '';
                selectProduct(foundProd.id, `${skuLabel}${foundProd.name}`, foundProd.unit);
            }
        }

        // Initialize Sales Request Combobox value if old value exists
        const reqIdVal = document.getElementById('production_request_id').value;
        if (reqIdVal) {
            const foundReq = availableRequests.find(r => r.id == reqIdVal);
            if (foundReq) {
                const reqNum = foundReq.request_number || '-';
                const orderNum = foundReq.sales_order?.order_number || '-';
                const custName = foundReq.sales_order?.customer?.name || 'Umum';
                selectSalesRequest(foundReq.id, `Permintaan #${reqNum} (Order: ${orderNum} - ${custName})`);
            }
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
