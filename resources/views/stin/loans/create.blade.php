@extends('layouts.app', ['title' => 'Catat Peminjaman Barang'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-semibold mb-1">
                <a href="{{ route('stin.loans.index') }}" class="hover:text-slate-900 transition">Barang Dipinjam</a>
                <span>/</span>
                <span class="text-slate-900">Catat Pinjaman Baru</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Form Peminjaman Barang</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Dokumentasikan barang keluar sementara untuk peminjaman internal, sample mitra, atau pameran.
            </p>
        </div>

        <a href="{{ route('stin.loans.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-semibold text-rose-800 space-y-1">
            <div class="font-bold">Terjadi kesalahan input:</div>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('stin.loans.store') }}" id="loanForm" class="space-y-6">
        @csrf

        <!-- Panel 1: Data Peminjam & Keperluan -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-5">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest pb-2 border-b border-slate-900/10">
                Informasi Peminjam & Keperluan
            </h3>

            <div class="space-y-4">
                <!-- Nama Peminjam / PIC -->
                <div>
                    <label for="borrower_name" class="block text-xs font-semibold text-slate-800 mb-1">
                        Nama Peminjam / PIC Penanggung Jawab <span class="text-rose-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="borrower_name" 
                        name="borrower_name" 
                        value="{{ old('borrower_name') }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs apple-input font-bold text-slate-900 border-slate-300 focus:border-slate-900"
                    >
                </div>

                <!-- Keperluan Peminjaman -->
                <div>
                    <label for="purpose" class="block text-xs font-semibold text-slate-800 mb-1">
                        Keperluan Peminjaman <span class="text-rose-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="purpose" 
                        name="purpose" 
                        value="{{ old('purpose') }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs apple-input font-semibold text-slate-900 border-slate-300 focus:border-slate-900"
                    >
                </div>

                <!-- Tanggal Pinjam & Estimasi Tanggal Kembali -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="loan_date" class="block text-xs font-semibold text-slate-800 mb-1">
                            Tanggal Pinjam <span class="text-rose-600">*</span>
                        </label>
                        <input 
                            type="date" 
                            id="loan_date" 
                            name="loan_date" 
                            value="{{ old('loan_date', date('Y-m-d')) }}" 
                            required 
                            class="w-full px-3 py-2.5 rounded-xl text-xs apple-input font-medium text-slate-900 border-slate-300 focus:border-slate-900"
                        >
                    </div>

                    <div>
                        <label for="expected_return_date" class="block text-xs font-semibold text-slate-800 mb-1">
                            Estimasi Tanggal Kembali
                        </label>
                        <input 
                            type="date" 
                            id="expected_return_date" 
                            name="expected_return_date" 
                            value="{{ old('expected_return_date') }}" 
                            class="w-full px-3 py-2.5 rounded-xl text-xs apple-input font-medium text-slate-900 border-slate-300 focus:border-slate-900"
                        >
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel 2: Daftar Barang yang Dipinjam -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-900/10">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                        Daftar Barang yang Dipinjam
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Pilih barang dan jumlah unit fisik yang dikeluarkan dari gudang.
                    </p>
                </div>
                <button type="button" id="addItemBtn" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    + Tambah Barang
                </button>
            </div>

            <div id="itemsContainer" class="space-y-3">
                <!-- Baris Item Template (Inject via JS) -->
            </div>

            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <span class="font-medium text-slate-600">Total Kuantitas yang Dikeluarkan:</span>
                <span id="totalQuantityDisplay" class="font-extrabold text-slate-900 text-sm">0 Unit</span>
            </div>
        </div>

        <!-- Panel 3: Tanda Tangan Digital Peminjam -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-900/10">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                        Tanda Tangan Digital Peminjam
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Bubuhkan tanda tangan peminjam langsung di area canvas di bawah.
                    </p>
                </div>
                <button type="button" id="clearSigBtn" class="text-xs font-bold text-slate-700 hover:text-slate-900 underline">
                    Hapus Tanda Tangan
                </button>
            </div>

            <div class="border border-slate-300 rounded-2xl overflow-hidden bg-white relative">
                <canvas id="signatureCanvas" width="700" height="160" class="w-full h-[160px] cursor-crosshair block"></canvas>
                <div id="canvasPlaceholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-xs text-slate-400 select-none">
                    Gunakan mouse atau layar sentuh untuk tanda tangan
                </div>
            </div>
            <input type="hidden" name="signature_data" id="signatureData">
        </div>

        <!-- Tombol Aksi Form -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('stin.loans.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-2xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-md transition">
                Simpan & Keluarkan Barang
            </button>
        </div>
    </form>

</div>

<!-- Data Master Barang untuk JavaScript -->
<script>
    const availableItems = @json($availableItems);

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('itemsContainer');
        const addBtn = document.getElementById('addItemBtn');
        const totalDisplay = document.getElementById('totalQuantityDisplay');
        const form = document.getElementById('loanForm');

        let rowIndex = 0;

        function renderRow(selectedItemId = '', qty = 1, itemNotes = '') {
            const rowId = `item-row-${rowIndex}`;
            const div = document.createElement('div');
            div.className = 'p-4 rounded-2xl bg-white border border-slate-300 space-y-3 relative';
            div.id = rowId;

            let optionsHtml = '<option value="">-- Pilih Barang Gudang --</option>';
            availableItems.forEach(item => {
                const selected = item.id == selectedItemId ? 'selected' : '';
                optionsHtml += `<option value="${item.id}" data-stock="${item.current_stock}" data-unit="${item.unit || 'pcs'}" ${selected}>
                    ${item.name} (${item.sku}) — Sisa Stok: ${item.current_stock} ${item.unit || 'pcs'}
                </option>`;
            });

            div.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                    <div class="md:col-span-6">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Barang <span class="text-rose-600">*</span></label>
                        <select name="items[${rowIndex}][warehouse_item_id]" class="item-select w-full px-3 py-2 rounded-xl text-xs apple-input font-bold text-slate-900 border-slate-300" required>
                            ${optionsHtml}
                        </select>
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Jumlah <span class="text-rose-600">*</span></label>
                        <div class="relative">
                            <input type="number" min="1" value="${qty}" name="items[${rowIndex}][quantity]" class="item-qty w-full px-3 py-2 rounded-xl text-xs font-mono font-bold text-slate-900 border-slate-300" required>
                            <span class="unit-label absolute right-3 top-2 text-xs font-bold text-slate-400">unit</span>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase">Catatan</label>
                            <button type="button" class="remove-row-btn text-[11px] font-bold text-rose-600 hover:text-rose-800">Hapus</button>
                        </div>
                        <input type="text" name="items[${rowIndex}][notes]" value="${itemNotes}" class="w-full px-3 py-2 rounded-xl text-xs text-slate-800 border-slate-300">
                    </div>
                </div>
            `;

            container.appendChild(div);

            const select = div.querySelector('.item-select');
            const qtyInput = div.querySelector('.item-qty');
            const unitLabel = div.querySelector('.unit-label');
            const removeBtn = div.querySelector('.remove-row-btn');

            function updateRowMeta() {
                const opt = select.options[select.selectedIndex];
                if (opt && opt.value) {
                    const maxStock = parseInt(opt.dataset.stock) || 1;
                    const unit = opt.dataset.unit || 'pcs';
                    qtyInput.max = maxStock;
                    unitLabel.textContent = unit;
                    if (parseInt(qtyInput.value) > maxStock) {
                        qtyInput.value = maxStock;
                    }
                } else {
                    unitLabel.textContent = 'unit';
                }
                recalcTotal();
            }

            select.addEventListener('change', updateRowMeta);
            qtyInput.addEventListener('input', recalcTotal);
            removeBtn.addEventListener('click', () => {
                if (container.children.length > 1) {
                    div.remove();
                    recalcTotal();
                } else {
                    alert('Minimal satu barang harus dipinjam.');
                }
            });

            updateRowMeta();
            rowIndex++;
        }

        function recalcTotal() {
            let total = 0;
            container.querySelectorAll('.item-qty').forEach(input => {
                const val = parseInt(input.value) || 0;
                total += val;
            });
            totalDisplay.textContent = total.toLocaleString('id-ID') + ' Unit';
        }

        addBtn.addEventListener('click', () => {
            renderRow();
        });

        // Inisialisasi baris pertama
        renderRow();

        // Canvas Tanda Tangan
        const canvas = document.getElementById('signatureCanvas');
        const clearBtn = document.getElementById('clearSigBtn');
        const sigInput = document.getElementById('signatureData');
        const placeholder = document.getElementById('canvasPlaceholder');
        const ctx = canvas.getContext('2d');

        let isDrawing = false;
        let hasSignature = false;

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 160;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';
        }

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            if (e.touches && e.touches[0]) {
                return {
                    x: e.touches[0].clientX - rect.left,
                    y: e.touches[0].clientY - rect.top
                };
            }
            return {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasSignature = true;
            placeholder.style.display = 'none';
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            if (e.type === 'touchstart') e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            if (e.type === 'touchmove') e.preventDefault();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                sigInput.value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        clearBtn.addEventListener('click', () => {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            sigInput.value = '';
            hasSignature = false;
            placeholder.style.display = 'flex';
        });

        form.addEventListener('submit', () => {
            if (hasSignature) {
                sigInput.value = canvas.toDataURL('image/png');
            }
        });
    });
</script>
@endsection
