@extends('layouts.app', ['title' => 'Print Barcode SKU'])

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar (No Print) -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Print Barcode SKU — {{ $item->name }}</h2>
            <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $item->sku }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stin.items.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
                Kembali ke Data Gudang
            </a>
            <button type="button" onclick="window.print()" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition">
                Cetak Barcode
            </button>
        </div>
    </div>

    <!-- Configuration Panel (No Print) -->
    <div class="no-print apple-glass-card rounded-2xl p-5 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            
            <!-- Mode Barcode -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Mode Barcode</label>
                <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 w-full">
                    <button type="button" onclick="setMode('both')" id="btn-mode-both" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition bg-slate-900 text-white text-center">
                        1D & 2D
                    </button>
                    <button type="button" onclick="setMode('1d')" id="btn-mode-1d" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center">
                        1D Saja
                    </button>
                    <button type="button" onclick="setMode('2d')" id="btn-mode-2d" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center">
                        2D (QR)
                    </button>
                </div>
            </div>

            <!-- Jumlah Salinan / Kopi -->
            <div>
                <label for="copiesInput" class="block font-semibold text-slate-700 mb-1.5">Jumlah Label</label>
                <div class="flex items-center gap-1.5">
                    <input 
                        type="number" 
                        id="copiesInput" 
                        min="1" 
                        max="100" 
                        value="{{ $copies ?? 1 }}" 
                        oninput="updateCopies(this.value)"
                        class="w-20 px-3 py-1.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-900 bg-white"
                    >
                    <div class="inline-flex items-center gap-1">
                        <button type="button" onclick="setCopies(1)" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700">
                            1
                        </button>
                        <button type="button" onclick="setCopies(5)" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700">
                            5
                        </button>
                        <button type="button" onclick="setCopies(10)" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700">
                            10
                        </button>
                        @if($item->current_stock > 0)
                            <button type="button" onclick="setCopies({{ $item->current_stock }})" class="px-2 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-[11px] font-semibold text-slate-700" title="Sesuai Stok">
                                Stok ({{ $item->current_stock }})
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Ukuran Label -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Ukuran Label</label>
                <div class="inline-flex rounded-xl border border-slate-300 bg-white p-1 w-full">
                    <button type="button" onclick="setSize('standard')" id="btn-size-standard" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition bg-slate-900 text-white text-center">
                        Standar (50×30)
                    </button>
                    <button type="button" onclick="setSize('compact')" id="btn-size-compact" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center">
                        Kompak (40×25)
                    </button>
                    <button type="button" onclick="setSize('large')" id="btn-size-large" class="flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center">
                        Besar (70×40)
                    </button>
                </div>
            </div>

            <!-- Opsi Informasi Tambahan -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Elemen Label</label>
                <div class="space-y-1.5 pt-0.5">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="toggleName" checked onchange="toggleField('name', this.checked)" class="rounded border-slate-300 text-slate-900 focus:ring-0">
                        <span class="text-xs text-slate-700 font-medium">Nama Barang</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="togglePrice" checked onchange="toggleField('price', this.checked)" class="rounded border-slate-300 text-slate-900 focus:ring-0">
                        <span class="text-xs text-slate-700 font-medium">Harga HPP</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="toggleCategory" checked onchange="toggleField('category', this.checked)" class="rounded border-slate-300 text-slate-900 focus:ring-0">
                        <span class="text-xs text-slate-700 font-medium">Kategori Barang</span>
                    </label>
                </div>
            </div>

        </div>
    </div>

    <!-- Pratinjau & Kontainer Cetak Label -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 min-h-[400px] overflow-hidden shadow-xs">
        <div class="no-print mb-4 pb-3 border-b border-slate-200 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pratinjau Lembar Cetak</span>
            <span id="labelCountBadge" class="text-xs font-semibold text-slate-700">1 Label Siap Dicetak</span>
        </div>

        <div id="labelsContainer" class="flex flex-wrap gap-4 items-start justify-start">
            <!-- Diisi secara dinamis melalui JavaScript -->
        </div>
    </div>

</div>

<!-- CSS Khusus Media Cetak -->
<style>
    @media print {
        /* Hilangkan elemen navigasi & antarmuka aplikasi */
        .no-print,
        header,
        nav,
        aside,
        footer,
        #sidebar,
        #navbar {
            display: none !important;
        }

        body, html {
            background: #ffffff !important;
            color: #000000 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        main {
            padding: 0 !important;
            margin: 0 !important;
        }

        .space-y-6 {
            margin: 0 !important;
            padding: 0 !important;
        }

        #labelsContainer {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 2mm !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .barcode-label {
            box-shadow: none !important;
            border: 1px dashed #cccccc !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
            margin: 0 !important;
            background: #ffffff !important;
        }

        @page {
            size: auto;
            margin: 4mm;
        }
    }

    /* Style SVG Barcode agar responsif & tajam */
    .barcode-svg-container svg {
        width: 100% !important;
        height: auto !important;
        max-height: 42px;
        display: block;
        margin: 0 auto;
    }

    .qrcode-svg-container svg {
        width: 100% !important;
        height: auto !important;
        display: block;
        margin: 0 auto;
    }
</style>

<!-- Script Kontrol & Render Label -->
<script>
    const itemData = {
        name: {!! json_encode($item->name) !!},
        sku: {!! json_encode($item->sku) !!},
        category: {!! json_encode($item->category ?? '-') !!},
        unit_price: {!! json_encode($item->unit_price ? 'Rp ' . number_format($item->unit_price, 0, ',', '.') : '-') !!},
        barcode1d: {!! json_encode($barcode1d) !!},
        barcode2d: {!! json_encode($barcode2d) !!}
    };

    let currentMode = {!! json_encode($layout ?? 'both') !!};
    let currentCopies = parseInt({!! json_encode($copies ?? 1) !!}, 10) || 1;
    let currentSize = {!! json_encode($size ?? 'standard') !!};
    let showFields = {
        name: true,
        price: true,
        category: true
    };

    function setMode(mode) {
        currentMode = mode;
        ['both', '1d', '2d'].forEach(m => {
            const btn = document.getElementById(`btn-mode-${m}`);
            if (btn) {
                if (m === mode) {
                    btn.className = 'flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition bg-slate-900 text-white text-center';
                } else {
                    btn.className = 'flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center';
                }
            }
        });
        renderLabels();
    }

    function setSize(size) {
        currentSize = size;
        ['standard', 'compact', 'large'].forEach(s => {
            const btn = document.getElementById(`btn-size-${s}`);
            if (btn) {
                if (s === size) {
                    btn.className = 'flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition bg-slate-900 text-white text-center';
                } else {
                    btn.className = 'flex-1 py-1 px-2 rounded-lg text-xs font-semibold transition text-slate-700 hover:bg-slate-100 text-center';
                }
            }
        });
        renderLabels();
    }

    function setCopies(num) {
        currentCopies = Math.max(1, Math.min(100, parseInt(num, 10) || 1));
        const input = document.getElementById('copiesInput');
        if (input) input.value = currentCopies;
        renderLabels();
    }

    function updateCopies(val) {
        let num = parseInt(val, 10);
        if (isNaN(num) || num < 1) num = 1;
        if (num > 100) num = 100;
        currentCopies = num;
        renderLabels();
    }

    function toggleField(field, isChecked) {
        showFields[field] = isChecked;
        renderLabels();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.innerText = text || '';
        return div.innerHTML;
    }

    function renderLabels() {
        const container = document.getElementById('labelsContainer');
        const countBadge = document.getElementById('labelCountBadge');
        if (!container) return;

        if (countBadge) {
            countBadge.innerText = `${currentCopies} Label Siap Dicetak`;
        }

        // Tentukan dimensi label berdasarkan ukuran
        let sizeClasses = 'w-[52mm] min-h-[32mm] p-2';
        let nameClasses = 'text-[11px] font-bold line-clamp-1';
        let subClasses = 'text-[9px] text-slate-600';
        let skuClasses = 'text-[11px] font-bold font-mono tracking-wider';
        let qrSize = 'w-12 h-12';

        if (currentSize === 'compact') {
            sizeClasses = 'w-[42mm] min-h-[26mm] p-1.5';
            nameClasses = 'text-[9px] font-bold line-clamp-1';
            subClasses = 'text-[8px] text-slate-600';
            skuClasses = 'text-[9px] font-bold font-mono tracking-wide';
            qrSize = 'w-9 h-9';
        } else if (currentSize === 'large') {
            sizeClasses = 'w-[72mm] min-h-[44mm] p-3';
            nameClasses = 'text-[13px] font-bold line-clamp-1';
            subClasses = 'text-[10px] text-slate-600';
            skuClasses = 'text-[13px] font-bold font-mono tracking-widest';
            qrSize = 'w-16 h-16';
        }

        let labelHtml = '';

        for (let i = 0; i < currentCopies; i++) {
            labelHtml += `
                <div class="barcode-label ${sizeClasses} bg-white border border-slate-300 rounded-lg flex flex-col justify-between text-slate-900 select-none overflow-hidden">
                    
                    <!-- Header Info -->
                    <div class="border-b border-slate-200 pb-1 mb-1">
                        ${showFields.name ? `<div class="${nameClasses} text-slate-900" title="${escapeHtml(itemData.name)}">${escapeHtml(itemData.name)}</div>` : ''}
                        ${(showFields.category || showFields.price) ? `
                            <div class="${subClasses} flex items-center justify-between gap-1">
                                ${showFields.category ? `<span>${escapeHtml(itemData.category)}</span>` : '<span></span>'}
                                ${showFields.price ? `<span class="font-semibold text-slate-900">${escapeHtml(itemData.unit_price)}</span>` : ''}
                            </div>
                        ` : ''}
                    </div>

                    <!-- Middle Content (1D & 2D) -->
                    <div class="my-auto py-0.5">
                        ${renderBarcodeBody(qrSize)}
                    </div>

                    <!-- Footer: SKU Text -->
                    <div class="text-center pt-1 border-t border-slate-100">
                        <div class="${skuClasses} text-slate-900">${escapeHtml(itemData.sku)}</div>
                    </div>

                </div>
            `;
        }

        container.innerHTML = labelHtml;
    }

    function renderBarcodeBody(qrSize) {
        if (currentMode === 'both') {
            return `
                <div class="flex items-center gap-2 justify-between">
                    <div class="flex-1 barcode-svg-container min-w-0">
                        ${itemData.barcode1d}
                    </div>
                    <div class="${qrSize} flex-shrink-0 qrcode-svg-container">
                        ${itemData.barcode2d}
                    </div>
                </div>
            `;
        } else if (currentMode === '1d') {
            return `
                <div class="w-full barcode-svg-container">
                    ${itemData.barcode1d}
                </div>
            `;
        } else if (currentMode === '2d') {
            return `
                <div class="mx-auto ${qrSize} qrcode-svg-container">
                    ${itemData.barcode2d}
                </div>
            `;
        }
        return '';
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderLabels();
    });
</script>
@endsection
