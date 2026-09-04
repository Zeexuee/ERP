@extends('layouts.app', ['title' => 'Buat Sales Order Baru'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Sales Order Baru</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Ketik nama pelanggan atau produk secara langsung untuk mencari atau menambahkan baru.</p>
        </div>
        <a href="{{ route('sales-orders.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
    </div>

    <form id="salesOrderForm" action="{{ route('sales-orders.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- 1. Customer Searchable Combobox Card (Liquid Glass Floating) -->
        <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4 relative z-30 overflow-visible">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. Informasi Pelanggan</h3>

            <div class="relative" id="customerComboboxWrapper">
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Cari atau Tambah Pelanggan *</label>
                
                <div class="relative">
                    <input type="text" 
                           id="customerSearchInput" 
                           placeholder="Ketik nama costumer" 
                           autocomplete="off"
                           class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium bg-white">
                    <input type="hidden" name="customer_id" id="customerIdInput" required value="{{ old('customer_id') }}">
                    
                    <button type="button" id="clearCustomerBtn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Ganti
                    </button>
                </div>

                <!-- Customer Liquid Glass Solid Dropdown Results List -->
                <div id="customerDropdownList" 
                     style="backdrop-filter: blur(32px) saturate(180%); -webkit-backdrop-filter: blur(32px) saturate(180%);"
                     class="hidden absolute left-0 right-0 top-full mt-2 z-50 bg-white/[0.97] rounded-2xl border border-white shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-slate-900/10 max-h-64 overflow-y-auto divide-y divide-slate-900/5">
                </div>

                @error('customer_id')
                    <span class="text-xs text-slate-900 mt-1.5 block font-bold">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- 2. Dynamic Items Table with Searchable Product Comboboxes -->
        <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4 relative z-20 overflow-visible">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. Item Produk Order</h3>
                </div>
                <button type="button" id="addItemBtn" class="px-4 py-1.5 rounded-full btn-dark text-xs font-bold">
                    Tambah Baris Item
                </button>
            </div>

            <div class="overflow-visible">
                <table class="w-full text-left text-sm text-slate-800 overflow-visible">
                    <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold">
                        <tr>
                            <th class="px-3 py-2.5 w-5/12 rounded-l-lg">Produk (Cari / Ketik Baru)</th>
                            <th class="px-3 py-2.5 w-28">Satuan</th>
                            <th class="px-3 py-2.5 w-24">Jumlah</th>
                            <th class="px-3 py-2.5 w-36">Harga Satuan (Rp)</th>
                            <th class="px-3 py-2.5 w-36">Subtotal (Rp)</th>
                            <th class="px-3 py-2.5 text-center w-12 rounded-r-lg">#</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10 overflow-visible" id="itemsTableBody">
                        <tr class="item-row relative" data-row="0">
                            <td class="px-3 py-2.5 relative">
                                <div class="relative product-combobox-wrapper">
                                    <input type="text" 
                                           placeholder="Ketik nama atau SKU produk..." 
                                           autocomplete="off"
                                           class="product-search-input w-full rounded-xl apple-input px-3 py-2 text-sm font-medium bg-white">
                                    <input type="hidden" name="items[0][product_id]" class="product-id-input" required>
                                    
                                    <!-- Product Liquid Glass Solid Dropdown List -->
                                    <div class="product-dropdown-list hidden absolute left-0 right-0 top-full mt-2 z-50 bg-white/[0.97] rounded-2xl border border-white shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-slate-900/10 max-h-60 overflow-y-auto divide-y divide-slate-900/5 min-w-[280px]"
                                         style="backdrop-filter: blur(32px) saturate(180%); -webkit-backdrop-filter: blur(32px) saturate(180%);">
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5">
                                <select name="items[0][unit]" class="unit-select w-full rounded-xl apple-input px-2.5 py-2 text-xs font-bold bg-white">
                                    <option value="kg" selected>kg</option>
                                    <option value="gram">gram</option>
                                    <option value="unit">unit</option>
                                    <option value="pcs">pcs</option>
                                    <option value="liter">liter</option>
                                    <option value="meter">meter</option>
                                    <option value="box">box</option>
                                    <option value="karung">karung</option>
                                </select>
                            </td>
                            <td class="px-3 py-2.5">
                                <input type="number" step="any" name="items[0][quantity]" value="1" min="0.01" required class="qty-input w-full rounded-xl apple-input px-3 py-2 text-sm text-center font-bold">
                            </td>
                            <td class="px-3 py-2.5">
                                <input type="number" name="items[0][unit_price]" value="0" min="0" step="100" class="price-input w-full rounded-xl apple-input px-3 py-2 text-sm font-medium">
                            </td>
                            <td class="px-3 py-2.5 font-extrabold text-slate-900 text-right subtotal-display">
                                Rp 0
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <button type="button" class="remove-row-btn text-slate-400 hover:text-slate-900 text-xs font-bold">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-900/10">
                <div class="text-right">
                    <span class="text-xs text-slate-500 block uppercase font-bold tracking-wider">Total Sales Order</span>
                    <span class="text-2xl font-extrabold text-slate-900" id="grandTotalDisplay">Rp 0</span>
                </div>
            </div>
        </div>

        <!-- 3. PIC & Digital Signature Card -->
        <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">3. Penanggung Jawab & Tanda Tangan</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Penerima Pemesanan *</label>
                    <input type="text" 
                           name="pic_name" 
                           id="pic_name" 
                           required
                           value="{{ old('pic_name') }}"
                           placeholder="Masukkan nama Anda" 
                           class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium bg-white">
                    @error('pic_name')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
                    
                    <p class="text-[11px] text-slate-400 mt-2 font-medium">Anda sebagai sales yang menerima pesanan ini.</p>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Tanda Tangan Penanggung Jawab</label>
                        <button type="button" id="clearSignatureBtn" class="text-xs font-bold text-slate-400 hover:text-slate-900 transition">
                            Hapus / Ulangi
                        </button>
                    </div>
                    
                    <div class="border border-slate-900/15 rounded-2xl bg-white p-1 relative overflow-hidden shadow-inner">
                        <canvas id="signatureCanvas" class="w-full h-36 rounded-xl cursor-crosshair touch-none bg-white"></canvas>
                    </div>
                    <input type="hidden" name="signature" id="signatureInput" value="{{ old('signature') }}">
                    <p class="text-[10px] text-slate-400 mt-1.5 font-medium">Gunakan mouse atau layar sentuh untuk membubuhkan tanda tangan.</p>
                    @error('signature')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sales-orders.index') }}" class="px-5 py-2.5 rounded-full btn-subtle text-sm">Batal</a>
            <button type="submit" id="submitSoBtn" class="px-6 py-2.5 rounded-full btn-dark text-sm">Simpan Sales Order</button>
        </div>
    </form>
</div>

<script>
let rowIndex = 1;
let customersData = @json($customers);
let productsData = @json($products);

// --- TOTALS CALCULATION ---
function calculateTotals() {
    let grandTotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        grandTotal += subtotal;
        row.querySelector('.subtotal-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
    });
    document.getElementById('grandTotalDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);
}

// --- SIGNATURE CANVAS INITIALIZATION ---
function initSignatureCanvas() {
    const canvas = document.getElementById('signatureCanvas');
    const signatureInput = document.getElementById('signatureInput');
    const clearBtn = document.getElementById('clearSignatureBtn');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let isDrawing = false;
    let hasSignature = false;

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        if (rect.width === 0) return;
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#0f172a'; // slate-900

        // If old signature exists, draw it
        if (signatureInput.value && !hasSignature) {
            const img = new Image();
            img.onload = () => {
                ctx.drawImage(img, 0, 0, rect.width, rect.height);
                hasSignature = true;
            };
            img.src = signatureInput.value;
        }
    }

    setTimeout(resizeCanvas, 150);
    window.addEventListener('resize', resizeCanvas);

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }

    function startDraw(e) {
        e.preventDefault();
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        hasSignature = true;
    }

    function stopDraw() {
        if (!isDrawing) return;
        isDrawing = false;
        if (hasSignature) {
            signatureInput.value = canvas.toDataURL('image/png');
        }
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDraw);

    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', stopDraw);

    clearBtn.addEventListener('click', function() {
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);
        signatureInput.value = '';
        hasSignature = false;
    });

    const form = document.getElementById('salesOrderForm');
    if (form) {
        form.addEventListener('submit', function() {
            if (hasSignature) {
                signatureInput.value = canvas.toDataURL('image/png');
            }
        });
    }
}

// --- CUSTOMER AUTOCOMPLETE & QUICK ADD ---
function initCustomerCombobox() {
    const searchInput = document.getElementById('customerSearchInput');
    const idInput = document.getElementById('customerIdInput');
    const dropdown = document.getElementById('customerDropdownList');
    const clearBtn = document.getElementById('clearCustomerBtn');

    // Pre-populate if old value exists
    if (idInput.value) {
        const found = customersData.find(c => c.id == idInput.value);
        if (found) {
            searchInput.value = found.name;
            searchInput.readOnly = true;
            clearBtn.classList.remove('hidden');
        }
    }

    function renderCustomerResults(query) {
        dropdown.innerHTML = '';
        const q = (query || '').trim();
        const qLower = q.toLowerCase();

        // 1. POSITION "TAMBAHKAN SEBAGAI BARU" AT THE VERY TOP
        const createItem = document.createElement('div');
        createItem.className = 'px-4 py-3.5 bg-slate-900/[0.04] hover:bg-slate-900/[0.08] text-slate-900 text-sm font-bold cursor-pointer flex items-center justify-between border-b border-slate-900/10 transition';
        if (q.length > 0) {
            createItem.innerHTML = `
                <span>+ Tambahkan "<strong>${escapeHtml(q)}</strong>" sebagai Pelanggan Baru</span>
                <span class="text-[10px] uppercase font-mono px-2.5 py-0.5 rounded-full badge-dark">Buat Baru</span>
            `;
            createItem.addEventListener('click', () => {
                createNewCustomerOnTheFly(q);
            });
        } else {
            createItem.innerHTML = `
                <span>+ Tambah Pelanggan Baru...</span>
                <span class="text-[10px] uppercase font-mono px-2.5 py-0.5 rounded-full badge-dark">Ketik Nama</span>
            `;
            createItem.addEventListener('click', () => {
                searchInput.focus();
            });
        }
        dropdown.appendChild(createItem);

        // 2. BELOW TOP ITEM: RENDER MATCHING / EXISTING CUSTOMERS
        const matches = customersData.filter(c => 
            !qLower || c.name.toLowerCase().includes(qLower) || 
            (c.email && c.email.toLowerCase().includes(qLower))
        );

        if (matches.length > 0) {
            matches.forEach(c => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 hover:bg-slate-900/[0.04] cursor-pointer flex items-center justify-between text-sm transition';
                item.innerHTML = `
                    <div>
                        <span class="font-bold text-slate-900 block">${c.name}</span>
                        ${c.email ? `<span class="text-[11px] text-slate-500">${c.email}</span>` : ''}
                    </div>
                    <span class="text-[10px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Pilih</span>
                `;
                item.addEventListener('click', () => {
                    selectCustomer(c);
                });
                dropdown.appendChild(item);
            });
        } else if (qLower.length > 0) {
            const noMatch = document.createElement('div');
            noMatch.className = 'px-4 py-3 text-xs text-slate-400 font-medium italic';
            noMatch.textContent = 'Tidak ada pelanggan yang cocok dengan pencarian ini.';
            dropdown.appendChild(noMatch);
        }

        dropdown.classList.remove('hidden');
    }

    function selectCustomer(cust) {
        searchInput.value = cust.name;
        searchInput.readOnly = true;
        idInput.value = cust.id;
        dropdown.classList.add('hidden');
        clearBtn.classList.remove('hidden');
    }

    function createNewCustomerOnTheFly(name) {
        searchInput.value = 'Menyimpan pelanggan baru...';
        dropdown.classList.add('hidden');

        fetch("{{ route('customers.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ name: name, is_active: 1 })
        })
        .then(res => res.json())
        .then(data => {
            if (data.customer) {
                customersData.push(data.customer);
                selectCustomer(data.customer);
            } else {
                alert('Gagal menambahkan pelanggan baru.');
                searchInput.value = '';
                idInput.value = '';
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan koneksi.');
            searchInput.value = '';
            idInput.value = '';
        });
    }

    searchInput.addEventListener('focus', () => {
        if (!searchInput.readOnly) renderCustomerResults(searchInput.value);
    });

    searchInput.addEventListener('input', () => {
        renderCustomerResults(searchInput.value);
    });

    clearBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchInput.readOnly = false;
        idInput.value = '';
        clearBtn.classList.add('hidden');
        searchInput.focus();
        renderCustomerResults('');
    });

    document.addEventListener('click', (e) => {
        if (!document.getElementById('customerComboboxWrapper').contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

// --- PRODUCT AUTOCOMPLETE & QUICK ADD ---
function bindProductCombobox(row) {
    const wrapper = row.querySelector('.product-combobox-wrapper');
    const searchInput = row.querySelector('.product-search-input');
    const idInput = row.querySelector('.product-id-input');
    const dropdown = row.querySelector('.product-dropdown-list');
    const priceInput = row.querySelector('.price-input');
    const unitSelect = row.querySelector('.unit-select');

    function renderProductResults(query) {
        dropdown.innerHTML = '';
        const q = (query || '').trim();
        const qLower = q.toLowerCase();

        // 1. POSITION "TAMBAHKAN SEBAGAI PRODUK BARU" AT THE VERY TOP
        const createItem = document.createElement('div');
        createItem.className = 'px-3.5 py-3 bg-slate-900/[0.04] hover:bg-slate-900/[0.08] text-slate-900 text-xs font-bold cursor-pointer border-b border-slate-900/10 flex items-center justify-between transition';
        if (q.length > 0) {
            createItem.innerHTML = `
                <span>+ Tambahkan "<strong>${escapeHtml(q)}</strong>" sebagai Produk Baru</span>
                <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Buat Baru</span>
            `;
            createItem.addEventListener('click', () => {
                createNewProductOnTheFly(q);
            });
        } else {
            createItem.innerHTML = `
                <span>+ Tambah Produk Baru ke Katalog...</span>
                <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Ketik Nama</span>
            `;
            createItem.addEventListener('click', () => {
                searchInput.focus();
            });
        }
        dropdown.appendChild(createItem);

        // 2. BELOW TOP ITEM: RENDER MATCHING / EXISTING PRODUCTS
        const matches = productsData.filter(p => 
            !qLower || p.name.toLowerCase().includes(qLower) || 
            (p.sku && p.sku.toLowerCase().includes(qLower))
        );

        if (matches.length > 0) {
            matches.forEach(p => {
                const item = document.createElement('div');
                item.className = 'px-3.5 py-2.5 hover:bg-slate-900/[0.04] cursor-pointer flex items-center justify-between text-xs transition';
                item.innerHTML = `
                    <div>
                        <span class="font-bold text-slate-900 block">${p.name}</span>
                        <span class="text-[10px] text-slate-500 font-mono">${p.sku} • Rp ${new Intl.NumberFormat('id-ID').format(p.price)} / ${p.unit || 'kg'}</span>
                    </div>
                    <span class="text-[9px] uppercase font-mono px-2 py-0.5 rounded-full badge-dark">Pilih</span>
                `;
                item.addEventListener('click', () => {
                    selectProduct(p);
                });
                dropdown.appendChild(item);
            });
        } else if (qLower.length > 0) {
            const noMatch = document.createElement('div');
            noMatch.className = 'px-3.5 py-2.5 text-xs text-slate-400 font-medium italic';
            noMatch.textContent = 'Tidak ada produk yang cocok dengan pencarian ini.';
            dropdown.appendChild(noMatch);
        }

        row.style.zIndex = '40';
        dropdown.classList.remove('hidden');
    }

    function selectProduct(prod) {
        searchInput.value = prod.name;
        idInput.value = prod.id;
        priceInput.value = prod.price;
        if (unitSelect && prod.unit) {
            unitSelect.value = prod.unit;
        }
        dropdown.classList.add('hidden');
        row.style.zIndex = 'auto';
        calculateTotals();
    }

    function createNewProductOnTheFly(name) {
        searchInput.value = 'Menyimpan produk baru...';
        dropdown.classList.add('hidden');
        
        const currentPrice = parseFloat(priceInput.value) || 0;
        const currentUnit = unitSelect ? unitSelect.value : 'kg';

        fetch("{{ route('products.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                name: name,
                price: currentPrice,
                unit: currentUnit,
                stock_quantity: 0
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.product) {
                productsData.push(data.product);
                selectProduct(data.product);
            } else {
                alert('Gagal menambahkan produk baru.');
                searchInput.value = '';
                idInput.value = '';
                row.style.zIndex = 'auto';
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan koneksi.');
            searchInput.value = '';
            idInput.value = '';
            row.style.zIndex = 'auto';
        });
    }

    searchInput.addEventListener('focus', () => {
        renderProductResults(searchInput.value);
    });

    searchInput.addEventListener('input', () => {
        renderProductResults(searchInput.value);
    });

    document.addEventListener('click', (e) => {
        if (!wrapper.contains(e.target)) {
            dropdown.classList.add('hidden');
            row.style.zIndex = 'auto';
        }
    });

    const qtyInput = row.querySelector('.qty-input');
    const removeBtn = row.querySelector('.remove-row-btn');

    qtyInput.addEventListener('input', calculateTotals);
    priceInput.addEventListener('input', calculateTotals);

    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('Sales Order minimal harus memiliki 1 item produk.');
            }
        });
    }
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

document.addEventListener('DOMContentLoaded', function() {
    initSignatureCanvas();
    initCustomerCombobox();
    bindProductCombobox(document.querySelector('.item-row'));

    const addItemBtn = document.getElementById('addItemBtn');
    const tableBody = document.getElementById('itemsTableBody');

    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row relative';
        newRow.setAttribute('data-row', rowIndex);

        newRow.innerHTML = `
            <td class="px-3 py-2.5 relative">
                <div class="relative product-combobox-wrapper">
                    <input type="text" 
                           placeholder="Ketik nama atau SKU produk..." 
                           autocomplete="off"
                           class="product-search-input w-full rounded-xl apple-input px-3 py-2 text-sm font-medium bg-white">
                    <input type="hidden" name="items[${rowIndex}][product_id]" class="product-id-input" required>
                    
                    <div class="product-dropdown-list hidden absolute left-0 right-0 top-full mt-2 z-50 bg-white/[0.97] rounded-2xl border border-white shadow-[0_20px_50px_rgba(0,0,0,0.15)] ring-1 ring-slate-900/10 max-h-60 overflow-y-auto divide-y divide-slate-900/5 min-w-[280px]"
                         style="backdrop-filter: blur(32px) saturate(180%); -webkit-backdrop-filter: blur(32px) saturate(180%);">
                    </div>
                </div>
            </td>
            <td class="px-3 py-2.5">
                <select name="items[${rowIndex}][unit]" class="unit-select w-full rounded-xl apple-input px-2.5 py-2 text-xs font-bold bg-white">
                    <option value="kg" selected>kg</option>
                    <option value="gram">gram</option>
                    <option value="unit">unit</option>
                    <option value="pcs">pcs</option>
                    <option value="liter">liter</option>
                    <option value="meter">meter</option>
                    <option value="box">box</option>
                    <option value="karung">karung</option>
                </select>
            </td>
            <td class="px-3 py-2.5">
                <input type="number" step="any" name="items[${rowIndex}][quantity]" value="1" min="0.01" required class="qty-input w-full rounded-xl apple-input px-3 py-2 text-sm text-center font-bold">
            </td>
            <td class="px-3 py-2.5">
                <input type="number" name="items[${rowIndex}][unit_price]" value="0" min="0" step="100" class="price-input w-full rounded-xl apple-input px-3 py-2 text-sm font-medium">
            </td>
            <td class="px-3 py-2.5 font-extrabold text-slate-900 text-right subtotal-display">
                Rp 0
            </td>
            <td class="px-3 py-2.5 text-center">
                <button type="button" class="remove-row-btn text-slate-400 hover:text-slate-900 text-xs font-bold">Hapus</button>
            </td>
        `;

        tableBody.appendChild(newRow);
        bindProductCombobox(newRow);
        rowIndex++;
    });
});
</script>
@endsection
