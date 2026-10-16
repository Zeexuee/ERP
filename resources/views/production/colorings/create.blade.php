@extends('layouts.app', ['title' => 'Inisiasi Celup & Warna'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Celup & Warna</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-900 border border-slate-300 uppercase tracking-widest">
                Manufaktur
            </span>
        </div>
        <a href="{{ route('production.colorings.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition uppercase tracking-widest">
            KEMBALI
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-900 text-xs font-bold shadow-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('production.colorings.store') }}" method="POST" id="coloringInitForm" class="space-y-6">
        @csrf

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-6">
            <!-- Informasi Proses -->
            <div>
                <h3 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest border-b pb-2 mb-4">Informasi Batch</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Jenis Proses</label>
                        <select name="process_type" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                            <option value="">-- Pilih Jenis Proses --</option>
                            <option value="celup" @selected(old('process_type') == 'celup')>Celup</option>
                            <option value="warna" @selected(old('process_type') == 'warna')>Warna</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pilih Bahan Baku -->
            <div>
                <h3 class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest border-b pb-2 mb-4">Pemilihan Bahan Baku (Hasil Tembak / Celup / Warna)</h3>
                @if($branches->isEmpty())
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-center">
                        <p class="text-sm font-bold text-amber-800">Tidak ada stok barang "Hasil Tembak" atau "Hasil Celup/Warna" di gudang.</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($branches->groupBy('material_id') as $materialId => $materialBranches)
                            @php $mat = $materialBranches->first()->material; @endphp
                            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50">
                                <h4 class="text-sm font-bold text-slate-800 mb-3">[{{ $mat->code }}] {{ $mat->name }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($materialBranches as $branch)
                                        <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-200 bg-white hover:border-slate-300 cursor-pointer transition">
                                            <div class="flex items-center h-5">
                                                <input type="radio" name="material_branch_id" value="{{ $branch->id }}" class="w-4 h-4 text-slate-900 border-slate-300 focus:ring-slate-900" @checked(old('material_branch_id') == $branch->id) required>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-xs font-bold text-slate-900">{{ $branch->branch_code }}</span>
                                                <span class="text-[10px] font-medium text-slate-500">Stok: {{ number_format($branch->quantity, 2) }} kg</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Alokasi Bahan Pendukung -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest">
                    Bahan Pendukung (Input Bahan)
                </h3>
                <button type="button" id="addMaterialBtn" class="text-xs font-bold text-slate-900 hover:underline uppercase tracking-widest">
                    + Tambah Bahan
                </button>
            </div>
            <p class="text-xs text-slate-500">Tambahkan bahan-bahan pendukung dari gudang jika diperlukan (contoh: pewarna, obat kimia, dll).</p>
            
            <div id="materialContainer" class="space-y-4">
                <!-- Baris bahan akan ditambah di sini via JS -->
            </div>
        </div>

        <!-- Detail Tanggal & Penanggung Jawab -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2 uppercase tracking-widest">
                Detail Penanggung Jawab & Tanda Tangan
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Tanggal Proses</label>
                        <input type="date" name="coloring_date" value="{{ old('coloring_date', date('Y-m-d')) }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Penanggung Jawab (PIC)</label>
                        <input type="text" name="pic_name" value="{{ old('pic_name') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Catatan Inisiasi</label>
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
        <div class="flex justify-end gap-3">
            <a href="{{ route('production.colorings.index') }}" class="px-5 py-3 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition uppercase tracking-widest">
                BATAL
            </a>
            <button type="submit" id="submitBtn" class="px-6 py-3 rounded-xl btn-dark text-xs font-bold shadow-md transition uppercase tracking-widest" {{ $branches->isEmpty() ? 'disabled' : '' }}>
                SIMPAN & INISIASI
            </button>
        </div>
    </form>
</div>

@php
    $mappedMaterials = $supportMaterials->map(function($m) {
        return [
            'id' => $m->id,
            'name' => $m->name,
            'code' => $m->code,
            'unit' => $m->unit,
            'stock_quantity' => (float) $m->stock_quantity,
        ];
    })->values()->toArray();
@endphp

<!-- We use a standard script for select2-like features or just simple native select -->
<script>
    const materialsData = @json($mappedMaterials);

    document.addEventListener('DOMContentLoaded', () => {
        const materialContainer = document.getElementById('materialContainer');
        let materialCount = 0;

        function createRow(index) {
            const row = document.createElement('div');
            row.className = 'flex flex-col gap-4 p-4 rounded-xl border border-slate-200 bg-white mb-3 shadow-sm';
            
            let optionsHtml = `<option value="">-- Cari dan Pilih Barang Gudang --</option>`;
            materialsData.forEach(m => {
                optionsHtml += `<option value="${m.id}" data-stock="${m.stock_quantity}" data-unit="${m.unit}">[${m.code}] ${m.name}</option>`;
            });

            row.innerHTML = `
                <div class="w-full">
                    <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">PILIH BARANG (Bisa ketik untuk mencari)</label>
                    <select name="materials[${index}][material_id]" class="material-select w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                        ${optionsHtml}
                    </select>
                    <div class="stock-info hidden mt-2 px-3 py-2 bg-slate-100 border border-slate-300 rounded-xl flex justify-between items-center">
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-widest">Stok Gudang:</span>
                        <span class="text-xs font-mono font-bold text-slate-900 stock-display">0</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 items-end">
                    <div class="w-full">
                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">KUANTITAS DIGUNAKAN</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="materials[${index}][weight]" class="weight-input w-full px-3 py-2 pr-12 rounded-xl apple-input text-sm font-mono font-bold" required>
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-slate-500 unit-label"></span>
                        </div>
                    </div>
                    <div class="w-full text-right">
                        <button type="button" class="remove-btn px-4 py-2 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 font-bold text-[10px] shadow-sm transition uppercase tracking-widest">
                            HAPUS BAHAN
                        </button>
                    </div>
                </div>
            `;

            const select = row.querySelector('.material-select');
            const stockInfo = row.querySelector('.stock-info');
            const stockDisplay = row.querySelector('.stock-display');
            const unitLabel = row.querySelector('.unit-label');
            const removeBtn = row.querySelector('.remove-btn');

            select.addEventListener('change', () => {
                const selected = select.options[select.selectedIndex];
                if (selected && selected.value) {
                    const unit = selected.dataset.unit;
                    const stock = parseFloat(selected.dataset.stock) || 0;
                    
                    unitLabel.textContent = unit;
                    stockDisplay.textContent = stock.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' ' + unit;
                    stockInfo.classList.remove('hidden');
                } else {
                    unitLabel.textContent = '';
                    stockInfo.classList.add('hidden');
                }
            });

            removeBtn.addEventListener('click', () => {
                row.remove();
            });

            return row;
        }

        document.getElementById('addMaterialBtn').addEventListener('click', () => {
            materialContainer.appendChild(createRow(materialCount++));
        });

        // Canvas Signature
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const signatureInput = document.getElementById('signatureData');
        const form = document.getElementById('coloringInitForm');
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

        form.addEventListener('submit', (e) => {
            if (!hasSignature) {
                e.preventDefault();
                alert('Tanda tangan digital wajib diisi!');
                return false;
            }
            signatureInput.value = canvas.toDataURL('image/png');
        });
    });
</script>
@endsection
