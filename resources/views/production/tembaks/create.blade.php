@extends('layouts.app', ['title' => 'Inisiasi Tembak Kayu'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Tembak Kayu</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-900 border border-slate-300 uppercase tracking-widest">
                Manufaktur
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white uppercase tracking-widest">
                Tahap 1
            </span>
        </div>
        <a href="{{ route('production.tembaks.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition uppercase tracking-widest">
            KEMBALI
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-bold shadow-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('production.tembaks.store') }}" method="POST" id="tembakInitForm" class="space-y-6">
        @csrf

        <!-- Alokasi Bahan Tembak -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">
                    Bahan Tembak
                </h3>
                <button type="button" id="addWoodBtn" class="text-xs font-bold text-slate-900 hover:underline uppercase tracking-widest">
                    + Tambah Bahan Tembak
                </button>
            </div>
            
            <div id="woodContainer" class="space-y-4">
                <!-- Baris kayu akan ditambah di sini via JS -->
            </div>
        </div>

        <!-- Alokasi Getah -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">
                    Getah
                </h3>
                <button type="button" id="addResinBtn" class="text-xs font-bold text-slate-900 hover:underline uppercase tracking-widest">
                    + Tambah Getah
                </button>
            </div>

            <div id="resinContainer" class="space-y-4">
                <!-- Baris resin akan ditambah di sini via JS -->
            </div>
        </div>

        <!-- Detail Tanggal & Penanggung Jawab -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2 uppercase tracking-widest">
                Detail Tanggal & Penanggung Jawab
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Tanggal Proses</label>
                        <input type="date" name="tembak_date" value="{{ old('tembak_date') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Penanggung Jawab (PIC)</label>
                        <input type="text" name="pic_name" value="{{ old('pic_name') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Catatan</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2 rounded-xl apple-input text-xs">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan Digital PIC</label>
                        <button type="button" id="clearSignatureBtn" class="text-[11px] font-bold text-slate-900 hover:underline uppercase tracking-widest">HAPUS</button>
                    </div>
                    <div class="p-3 rounded-2xl bg-white border border-slate-300 space-y-2">
                        <div class="border border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative">
                            <canvas id="signatureCanvas" width="340" height="150" class="w-full h-[150px] cursor-crosshair"></canvas>
                        </div>
                        <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest text-center">Gunakan mouse atau sentuhan jari</div>
                    </div>
                    <input type="hidden" name="signature_data" id="signatureData">
                </div>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md flex flex-col md:flex-row items-center justify-between">
            <div class="space-y-1">
                <p class="text-2xl font-bold text-slate-900 font-mono" id="totalCostDisplay">Rp 0</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ route('production.tembaks.index') }}" class="px-5 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition uppercase tracking-widest">
                    BATAL
                </a>
                <button type="submit" id="submitBtn" class="px-6 py-3 rounded-xl btn-dark text-xs font-bold shadow-md transition uppercase tracking-widest">
                    SIMPAN & INISIASI
                </button>
            </div>
        </div>
    </form>
</div>

@php
    $mappedMaterials = $materials->map(function($m) {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'code' => $m->code,
            'unit' => $m->unit,
            'stock_quantity' => (float) $m->stock_quantity,
            'unit_cost' => (float) $m->unit_cost,
        ];
    })->values()->toArray();
@endphp

<script>
    const materialsData = @json($mappedMaterials);

    document.addEventListener('DOMContentLoaded', () => {
        const woodContainer = document.getElementById('woodContainer');
        const resinContainer = document.getElementById('resinContainer');
        const totalCostDisplay = document.getElementById('totalCostDisplay');
        
        let woodCount = 0;
        let resinCount = 0;

        function formatCurrency(amount) {
            return 'Rp ' + amount.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        function calculateTotal() {
            let total = 0;
            
            document.querySelectorAll('.material-row').forEach(row => {
                const select = row.querySelector('select');
                const weightInput = row.querySelector('.weight-input');
                
                if (select && select.value && weightInput && weightInput.value) {
                    const materialId = parseInt(select.value);
                    const weight = parseFloat(weightInput.value) || 0;
                    
                    const material = materialsData.find(m => m.id === materialId);
                    if (material) {
                        total += material.unit_cost * weight;
                    }
                }
            });
            
            totalCostDisplay.textContent = formatCurrency(total);
        }

        function createRow(type, index) {
            const row = document.createElement('div');
            row.className = 'material-row flex flex-col md:flex-row items-start gap-4 pb-4 border-b border-slate-200';
            
            const namePrefix = type === 'wood' ? 'woods' : 'resins';
            
            let optionsHtml = `<option value="">Pilih Barang...</option>`;
            materialsData.forEach(m => {
                optionsHtml += `<option value="${m.id}" data-stock="${m.stock_quantity}" data-cost="${m.unit_cost}" data-unit="${m.unit}">[${m.code}] ${m.name}</option>`;
            });

            row.innerHTML = `
                <div class="w-full md:flex-1">
                    <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">PILIH BARANG</label>
                    <select name="${namePrefix}[${index}][material_id]" class="material-select w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                        ${optionsHtml}
                    </select>
                    <div class="stock-info hidden mt-2 px-3 py-2 bg-slate-100 border border-slate-300 rounded-xl flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Stok Gudang:</span>
                        <span class="text-xs font-mono font-bold text-slate-900 stock-display">0</span>
                    </div>
                </div>
                <div class="w-full md:w-1/4">
                    <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">KUANTITAS DIGUNAKAN</label>
                    <div class="relative">
                        <input type="number" step="0.01" name="${namePrefix}[${index}][weight]" class="weight-input w-full px-3 py-2 pr-12 rounded-xl apple-input text-sm font-mono font-bold" required>
                        <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-500 unit-label"></span>
                    </div>
                </div>
                <div class="w-full md:w-1/6 text-right pt-6">
                    <div class="text-sm font-bold text-slate-900 font-mono subtotal-display pb-2">Rp 0</div>
                </div>
                <div class="pt-5">
                    <button type="button" class="remove-btn px-4 py-2 rounded-xl border border-slate-300 bg-white text-slate-900 hover:bg-slate-100 font-bold text-[10px] shadow-sm transition uppercase tracking-widest">
                        HAPUS
                    </button>
                </div>
            `;

            const select = row.querySelector('.material-select');
            const stockInfo = row.querySelector('.stock-info');
            const stockDisplay = row.querySelector('.stock-display');
            const unitLabel = row.querySelector('.unit-label');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            const weightInput = row.querySelector('.weight-input');
            const removeBtn = row.querySelector('.remove-btn');

            select.addEventListener('change', () => {
                const selected = select.options[select.selectedIndex];
                if (selected && selected.value) {
                    const unit = selected.dataset.unit;
                    const stock = parseFloat(selected.dataset.stock) || 0;
                    
                    unitLabel.textContent = unit;
                    stockDisplay.textContent = stock.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + unit;
                    stockInfo.classList.remove('hidden');
                    updateSubtotal();
                } else {
                    unitLabel.textContent = '';
                    stockInfo.classList.add('hidden');
                    subtotalDisplay.textContent = 'Rp 0';
                }
                calculateTotal();
            });

            weightInput.addEventListener('input', () => {
                updateSubtotal();
                calculateTotal();
            });

            function updateSubtotal() {
                const selected = select.options[select.selectedIndex];
                if (selected && selected.value) {
                    const cost = parseFloat(selected.dataset.cost) || 0;
                    const weight = parseFloat(weightInput.value) || 0;
                    subtotalDisplay.textContent = formatCurrency(cost * weight);
                }
            }

            removeBtn.addEventListener('click', () => {
                row.remove();
                calculateTotal();
            });

            return row;
        }

        document.getElementById('addWoodBtn').addEventListener('click', () => {
            woodContainer.appendChild(createRow('wood', woodCount++));
        });

        document.getElementById('addResinBtn').addEventListener('click', () => {
            resinContainer.appendChild(createRow('resin', resinCount++));
        });

        // Initialize with 1 row each
        document.getElementById('addWoodBtn').click();
        document.getElementById('addResinBtn').click();

        // Canvas Signature
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const signatureInput = document.getElementById('signatureData');
        const form = document.getElementById('tembakInitForm');
        let isDrawing = false;
        let hasSignature = false;

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (clientX - rect.left) * scaleX,
                y: (clientY - rect.top) * scaleY
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
    });
</script>
@endsection
