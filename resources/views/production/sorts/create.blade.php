@extends('layouts.app', ['title' => 'Catat Proses Sortir Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Catat Proses Sortir Kayu Mandiri</h2>
            <p class="text-xs text-slate-500 mt-0.5">
                Alur: Bahan Mentah $\rightarrow$ Penjemuran $\rightarrow$ Sortir Multi-Grade $\rightarrow$ Masuk Stok Gudang Kayu Tembak.
            </p>
        </div>
        <a href="{{ route('production.sorts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
            Kembali ke Riwayat
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold shadow-xs">
            <div class="font-bold mb-1">Perhatian:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('production.sorts.store') }}" method="POST" id="sortForm" class="space-y-6">
        @csrf

        <!-- Card 1: Bahan Mentah & Penjemuran -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                1. Bahan Mentah & Penjemuran
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Bahan Baku Mentah Asal <span class="text-rose-500">*</span>
                    </label>
                    <select name="source_material_id" id="sourceMaterialSelect" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        <option value="">-- Pilih Bahan Baku Mentah --</option>
                        @foreach($materials as $mat)
                            <option 
                                value="{{ $mat->id }}" 
                                data-stock="{{ $mat->stock_quantity }}" 
                                data-unit="{{ $mat->unit }}"
                                {{ old('source_material_id') == $mat->id ? 'selected' : '' }}
                            >
                                [{{ $mat->code }}] {{ $mat->name }} (Stok Gudang: {{ number_format($mat->stock_quantity, 1) }} {{ $mat->unit }})
                            </option>
                        @endforeach
                    </select>
                    <div id="sourceStockHint" class="text-[10px] text-slate-500 mt-1 font-mono">
                        Pilih bahan mentah yang akan diproses sortir.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Tanggal Penjemuran / Sortir <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="sort_date" 
                        value="{{ old('sort_date', date('Y-m-d')) }}" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                        required
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Berat Bahan Awal / Beli (kg) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="initial_weight" 
                        id="initialWeightInput" 
                        value="{{ old('initial_weight') }}" 
                        placeholder="Contoh: 20.00" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-xs font-mono font-bold" 
                        required
                    >
                    <span class="text-[10px] text-slate-400 mt-1 block">Kuantitas sebelum dijemur</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Hasil Setelah Penjemuran (kg) <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="dried_weight" 
                        id="driedWeightInput" 
                        value="{{ old('dried_weight') }}" 
                        placeholder="Contoh: 18.00" 
                        class="w-full px-3 py-2 rounded-xl apple-input text-xs font-mono font-bold" 
                        required
                    >
                    <span class="text-[10px] text-slate-400 mt-1 block">Timbangan fisik setelah kering</span>
                </div>

                <div class="p-3 bg-white/70 border border-slate-200 rounded-2xl flex flex-col justify-center">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Susut Penjemuran</span>
                    <div class="text-lg font-bold font-mono text-slate-900 mt-0.5" id="lossDisplay">
                        0.00 kg
                    </div>
                    <span class="text-[10px] text-slate-400" id="lossPercentageDisplay">
                        Susut: 0.0%
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Pembagian Hasil Sortir Multi-Grade -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-900/10 pb-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">
                        2. Pembagian Hasil Sortir (Bahan Kayu Siap Tembak)
                    </h3>
                    <p class="text-[11px] text-slate-500">
                        Hasil jemur disortir menjadi beberapa jenis bahan baku (SP, Mini SP, Alami, Mini Alami, Afkir, dll) yang langsung masuk ke stok gudang.
                    </p>
                </div>

                <!-- Live Balance Badge -->
                <div id="balanceBadge" class="px-3 py-1 rounded-xl text-xs font-bold font-mono border transition inline-block">
                    Total Sortir: 0.00 / 0.00 kg
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="itemsTable">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 font-extrabold uppercase text-[10px]">
                            <th class="py-2 px-2 w-1/2">Jenis Bahan Kayu Tembak / Afkir</th>
                            <th class="py-2 px-2 w-1/4">Hasil Timbang (kg)</th>
                            <th class="py-2 px-2">Catatan / Grade</th>
                            <th class="py-2 px-2 text-center w-12">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sortItemsBody" class="divide-y divide-slate-100">
                        <!-- Baris Item Sortir Dinamis -->
                    </tbody>
                </table>
            </div>

            <div class="flex items-center justify-between pt-2">
                <button type="button" onclick="addRow()" class="px-3 py-1.5 rounded-xl btn-dark text-xs font-semibold shadow-xs">
                    + Tambah Baris Bahan Hasil
                </button>

                <div class="text-xs text-slate-500 font-mono">
                    Total Berat Terbagi: <strong id="totalAllocatedText" class="text-slate-900 font-bold">0.00 kg</strong>
                </div>
            </div>
        </div>

        <!-- Card 3: PIC & Tanda Tangan Digital -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                3. Petugas & Validasi Tanda Tangan PIC
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Nama Petugas / PIC Sortir <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="pic_name" 
                            value="{{ old('pic_name', auth()->user()->name ?? '') }}" 
                            placeholder="Contoh: Bambang Sudiro" 
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Catatan Sortir (Opsional)
                        </label>
                        <textarea 
                            name="notes" 
                            rows="4" 
                            placeholder="Catatan kondisi bahan kayu, tingkat kekeringan, atau keterangan afkir..." 
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                        >{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Tanda Tangan Digital PIC
                        </label>
                        <button type="button" onclick="clearSignature()" class="text-[10px] text-slate-500 hover:text-slate-900 underline">
                            Hapus Tanda Tangan
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-xs">
                        <canvas id="sortSignatureCanvas" class="w-full h-36 cursor-crosshair touch-none block bg-white"></canvas>
                    </div>
                    <input type="hidden" name="signature_data" id="sortSignatureInput">
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        Gunakan stylus, mouse, atau layar sentuh untuk menandatangani hasil penimbangan sortir.
                    </span>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.sorts.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                Simpan & Mutasikan ke Stok Gudang
            </button>
        </div>
    </form>

</div>

<script>
    const materialsList = @json($materials);

    const initialWeightInput = document.getElementById('initialWeightInput');
    const driedWeightInput = document.getElementById('driedWeightInput');
    const lossDisplay = document.getElementById('lossDisplay');
    const lossPercentageDisplay = document.getElementById('lossPercentageDisplay');
    const balanceBadge = document.getElementById('balanceBadge');
    const totalAllocatedText = document.getElementById('totalAllocatedText');
    const sortItemsBody = document.getElementById('sortItemsBody');

    let rowCount = 0;

    // Tambah baris target hasil sortir
    function addRow(selectedId = '', weight = '', note = '') {
        rowCount++;
        const rowId = `item_row_${rowCount}`;

        let optionsHtml = '<option value="">-- Pilih Jenis Bahan Kayu Tembak --</option>';
        materialsList.forEach(mat => {
            const isSel = (selectedId && selectedId == mat.id) ? 'selected' : '';
            optionsHtml += `<option value="${mat.id}" ${isSel}>[${mat.code}] ${mat.name} (${mat.unit})</option>`;
        });

        const tr = document.createElement('tr');
        tr.id = rowId;
        tr.className = 'hover:bg-white/40 transition';
        tr.innerHTML = `
            <td class="py-2 px-2">
                <select name="items[${rowCount}][target_material_id]" class="w-full px-2.5 py-1.5 rounded-lg apple-input text-xs font-semibold" required>
                    ${optionsHtml}
                </select>
            </td>
            <td class="py-2 px-2">
                <input 
                    type="number" 
                    step="0.01" 
                    name="items[${rowCount}][result_weight]" 
                    value="${weight}" 
                    placeholder="0.00" 
                    class="w-full px-2.5 py-1.5 rounded-lg apple-input text-xs font-mono font-bold item-weight-input" 
                    oninput="recalculateBalance()" 
                    required
                >
            </td>
            <td class="py-2 px-2">
                <input 
                    type="text" 
                    name="items[${rowCount}][notes]" 
                    value="${note}" 
                    placeholder="Misal: Kualitas Super, Potongan kecil..." 
                    class="w-full px-2.5 py-1.5 rounded-lg apple-input text-xs"
                >
            </td>
            <td class="py-2 px-2 text-center">
                <button type="button" onclick="removeRow('${rowId}')" class="text-rose-500 hover:text-rose-700 font-bold text-xs p-1" title="Hapus Baris">
                    ✕
                </button>
            </td>
        `;

        sortItemsBody.appendChild(tr);
        recalculateBalance();
    }

    function removeRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            recalculateBalance();
        }
    }

    // Hitung Susut & Sinkronisasi Balance
    function updateDryingLoss() {
        const initial = parseFloat(initialWeightInput.value) || 0;
        const dried = parseFloat(driedWeightInput.value) || 0;

        const loss = Math.max(0, initial - dried);
        const percent = initial > 0 ? ((loss / initial) * 100).toFixed(1) : '0.0';

        lossDisplay.textContent = `${loss.toFixed(2)} kg`;
        lossPercentageDisplay.textContent = `Susut: ${percent}%`;

        recalculateBalance();
    }

    function recalculateBalance() {
        const dried = parseFloat(driedWeightInput.value) || 0;
        let totalAllocated = 0;

        document.querySelectorAll('.item-weight-input').forEach(input => {
            const val = parseFloat(input.value) || 0;
            totalAllocated += val;
        });

        totalAllocatedText.textContent = `${totalAllocated.toFixed(2)} kg`;

        const diff = Math.abs(totalAllocated - dried);

        if (dried > 0 && diff <= 0.1) {
            balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-emerald-100 text-emerald-900 border-emerald-300';
            balanceBadge.innerHTML = `✓ Balance Pas: ${totalAllocated.toFixed(2)} / ${dried.toFixed(2)} kg`;
        } else if (dried > 0) {
            balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-amber-100 text-amber-900 border-amber-300';
            const diffText = (totalAllocated - dried).toFixed(2);
            balanceBadge.innerHTML = `Selisih: ${diffText > 0 ? '+' : ''}${diffText} kg (${totalAllocated.toFixed(2)} / ${dried.toFixed(2)} kg)`;
        } else {
            balanceBadge.className = 'px-3 py-1 rounded-xl text-xs font-bold font-mono border bg-slate-100 text-slate-700 border-slate-200';
            balanceBadge.innerHTML = `Total Sortir: ${totalAllocated.toFixed(2)} kg`;
        }
    }

    initialWeightInput.addEventListener('input', updateDryingLoss);
    driedWeightInput.addEventListener('input', updateDryingLoss);

    // Default 2 baris awal
    document.addEventListener('DOMContentLoaded', () => {
        addRow();
        addRow();
        initCanvas();
    });

    // --- CANVAS TANDA TANGAN ---
    let canvas, ctx, isDrawing = false;

    function initCanvas() {
        canvas = document.getElementById('sortSignatureCanvas');
        if (!canvas) return;

        ctx = canvas.getContext('2d');
        canvas.width = canvas.parentElement.clientWidth || 400;
        canvas.height = 144;

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function start(e) {
            isDrawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            e.preventDefault();
        }

        function end() {
            if (isDrawing) {
                isDrawing = false;
                document.getElementById('sortSignatureInput').value = canvas.toDataURL('image/png');
            }
        }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', end);

        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', end);
    }

    function clearSignature() {
        if (!ctx) return;
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('sortSignatureInput').value = '';
    }

    // Update canvas data sebelum form submit
    document.getElementById('sortForm').addEventListener('submit', function (e) {
        if (canvas) {
            const blank = document.createElement('canvas');
            blank.width = canvas.width;
            blank.height = canvas.height;
            if (canvas.toDataURL() !== blank.toDataURL()) {
                document.getElementById('sortSignatureInput').value = canvas.toDataURL('image/png');
            }
        }
    });
</script>
@endsection
