@extends('layouts.app', ['title' => 'Rincian & Laporan Proses ' . ucfirst($coloringBatch->process_type)])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('production.colorings.index') }}" class="text-slate-500 hover:text-slate-900 transition flex items-center gap-2 text-xs font-bold uppercase tracking-widest">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <span class="rounded-full px-3 py-1 text-[10px] font-extrabold uppercase tracking-widest 
            {{ $coloringBatch->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 
              ($coloringBatch->status === 'waiting_continuation' ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800') }}">
            {{ str_replace('_', ' ', $coloringBatch->status) }}
        </span>
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

    <!-- Informasi Batch -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md border-t-4 {{ $coloringBatch->process_type == 'celup' ? 'border-blue-500' : 'border-fuchsia-500' }}">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="px-2.5 py-1 rounded-md text-[10px] font-extrabold uppercase tracking-widest {{ $coloringBatch->process_type == 'celup' ? 'bg-blue-100 text-blue-800' : 'bg-fuchsia-100 text-fuchsia-800' }}">
                    {{ $coloringBatch->process_type }}
                </span>
                <h2 class="text-xl font-bold text-slate-900">{{ $coloringBatch->batch_code }}</h2>
            </div>
            
            @if($coloringBatch->signature_path)
                <div class="flex flex-col items-center">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tanda Tangan Inisiasi</span>
                    <img src="{{ Storage::url($coloringBatch->signature_path) }}" alt="Signature" class="h-12 border border-slate-200 bg-white rounded-lg px-2">
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Bahan Baku (Hasil Tembak)</span>
                <p class="font-bold text-slate-900">{{ $coloringBatch->branch->material->name ?? 'Bahan Tidak Diketahui' }}</p>
                <p class="text-xs text-slate-500 font-mono mt-1">{{ number_format($coloringBatch->total_weight, 2) }} kg</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">PIC Inisiasi</span>
                <p class="font-bold text-slate-900">{{ $coloringBatch->pic_name }}</p>
                <p class="text-xs text-slate-500 mt-1"><i class="fas fa-calendar-alt mr-1"></i> {{ $coloringBatch->coloring_date->format('d M Y') }}</p>
            </div>
        </div>

        <!-- Bahan Pendukung -->
        @if($coloringBatch->materials->isNotEmpty())
            <div class="mt-4 border-t pt-4">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Bahan Pendukung (Digunakan)</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($coloringBatch->materials as $mat)
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ $mat->material->name }}</p>
                            <p class="text-xs text-slate-500 font-mono mt-1">{{ number_format($mat->weight, 2) }} {{ $mat->material->unit }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($coloringBatch->notes)
            <div class="mt-4 p-3 bg-slate-50 rounded-xl text-xs text-slate-700 italic border border-slate-100">
                <span class="font-bold text-slate-500 block mb-1 uppercase tracking-widest text-[9px] not-italic">Catatan Inisiasi:</span>
                "{{ $coloringBatch->notes }}"
            </div>
        @endif
    </div>

    @if($coloringBatch->status === 'completed' || $coloringBatch->status === 'waiting_continuation')
        <!-- Informasi Laporan -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md border border-slate-100">
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest border-b pb-3 mb-4">Laporan Hasil Proses</h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Berat Hasil Siap</span>
                    <p class="font-mono text-xl font-bold text-emerald-600">{{ number_format($coloringBatch->result_weight, 2) }} <span class="text-xs text-emerald-800">kg</span></p>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Berat Susut/Hilang</span>
                    <p class="font-mono text-xl font-bold text-rose-600">{{ number_format($coloringBatch->loss_weight, 2) }} <span class="text-xs text-rose-800">kg</span></p>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">PIC Laporan</span>
                    <p class="font-bold text-slate-900">{{ $coloringBatch->report_pic_name }}</p>
                    <p class="text-xs text-slate-500 mt-1"><i class="fas fa-calendar-check mr-1"></i> {{ $coloringBatch->report_date->format('d M Y') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-6 border-t pt-4">
                @if($coloringBatch->report_signature_path)
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Tanda Tangan Laporan</span>
                        <img src="{{ Storage::url($coloringBatch->report_signature_path) }}" alt="Signature Laporan" class="h-16 border border-slate-200 bg-white rounded-lg px-2">
                    </div>
                @endif
                @if($coloringBatch->report_image_path)
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Foto Hasil / Laporan</span>
                        <a href="{{ Storage::url($coloringBatch->report_image_path) }}" target="_blank" class="inline-block border border-slate-200 rounded-lg overflow-hidden max-w-[200px]">
                            <img src="{{ Storage::url($coloringBatch->report_image_path) }}" alt="Report Image" class="w-full h-auto">
                        </a>
                    </div>
                @endif
            </div>

            @if($coloringBatch->report_notes)
                <div class="p-3 bg-slate-50 rounded-xl text-xs text-slate-700 italic border border-slate-100 mt-4">
                    <span class="font-bold text-slate-500 block mb-1 uppercase tracking-widest text-[9px] not-italic">Catatan Laporan:</span>
                    "{{ $coloringBatch->report_notes }}"
                </div>
            @endif
        </div>
    @else
        <!-- Form Laporan -->
        <form action="{{ route('production.colorings.store-report', $coloringBatch) }}" method="POST" id="coloringReportForm" enctype="multipart/form-data" class="apple-glass-panel rounded-3xl p-6 shadow-md border border-slate-100">
            @csrf
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-widest border-b pb-3 mb-4">Formulir Laporan Proses Selesai</h3>
            
            <div class="space-y-6 mb-6">
                <!-- Berat & PIC -->
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Berat Hasil Bersih (kg)</label>
                        <input type="number" step="0.01" min="0" name="result_weight" value="{{ old('result_weight') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Berat Susut (kg)</label>
                        <input type="number" step="0.01" min="0" name="loss_weight" value="{{ old('loss_weight') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Tanggal Laporan</label>
                        <input type="date" name="report_date" value="{{ old('report_date', date('Y-m-d')) }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Nama PIC Laporan</label>
                        <input type="text" name="report_pic_name" value="{{ old('report_pic_name') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required placeholder="Contoh: Budi">
                    </div>
                </div>

                <!-- Media & Aksi -->
                <div class="grid grid-cols-1 gap-4 border-t border-slate-100 pt-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Aksi Selanjutnya</label>
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="continuation_action" value="finish" class="action-radio text-emerald-600 focus:ring-emerald-600 w-4 h-4" checked required>
                                <span class="text-xs font-bold">Selesai Sepenuhnya (Masuk ke Bahan Finishing)</span>
                            </label>
                            @if($coloringBatch->process_type === 'celup')
                                <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-slate-50">
                                    <input type="radio" name="continuation_action" value="continue_warna" class="action-radio text-fuchsia-600 focus:ring-fuchsia-600 w-4 h-4" required>
                                    <span class="text-xs font-bold">Lanjut ke Warna (Langsung Inisiasi)</span>
                                </label>
                            @else
                                <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-slate-50">
                                    <input type="radio" name="continuation_action" value="continue_celup" class="action-radio text-blue-600 focus:ring-blue-600 w-4 h-4" required>
                                    <span class="text-xs font-bold">Lanjut ke Celup (Langsung Inisiasi)</span>
                                </label>
                            @endif
                            <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-slate-50">
                                <input type="radio" name="continuation_action" value="hold" class="action-radio text-indigo-600 focus:ring-indigo-600 w-4 h-4" required>
                                <span class="text-xs font-bold">Tahan & Lanjutkan Nanti (Status: Menunggu)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Div for Additional Materials (only shown if continuing) -->
                    <div id="materialsSection" class="hidden mt-4 p-4 rounded-xl border border-blue-200 bg-blue-50">
                        <div class="flex items-center justify-between mb-3 border-b border-blue-200 pb-2">
                            <h4 class="text-[10px] font-bold text-blue-800 uppercase tracking-widest">
                                Bahan Pendukung (Untuk Proses Berikutnya)
                            </h4>
                            <button type="button" id="addMaterialBtn" class="text-[10px] font-bold text-blue-700 hover:underline uppercase tracking-widest">
                                + Tambah Bahan
                            </button>
                        </div>
                        <div id="materialContainer" class="space-y-4">
                            <!-- JS populated -->
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Foto Bukti (Opsional)</label>
                        <input type="file" name="report_image" accept="image/*" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:outline-none">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan Laporan</label>
                            <button type="button" id="clearSignatureBtn" class="text-[11px] font-bold text-slate-900 hover:underline uppercase tracking-widest">HAPUS</button>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-300 space-y-2">
                            <div class="border border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative">
                                <canvas id="signatureCanvas" width="340" height="150" class="w-full h-[150px] cursor-crosshair"></canvas>
                            </div>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest text-center">Tanda tangan PIC</div>
                        </div>
                        <input type="hidden" name="report_signature_data" id="signatureData">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-slate-900 uppercase tracking-widest mb-1">Catatan Tambahan (Opsional)</label>
                    <textarea name="report_notes" rows="2" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold placeholder-slate-400" placeholder="Opsional..."></textarea>
                </div>
            </div>

            <div class="flex justify-end border-t pt-4 mt-4">
                <button type="submit" id="submitBtn" class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-md transition uppercase tracking-widest">
                    <i class="fas fa-check mr-1"></i> Simpan Laporan
                </button>
            </div>
        </form>

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
        
        <script>
            const materialsData = @json($mappedMaterials);

            document.addEventListener('DOMContentLoaded', () => {
                // Radio button logic for showing materials
                const radios = document.querySelectorAll('.action-radio');
                const materialsSection = document.getElementById('materialsSection');

                radios.forEach(radio => {
                    radio.addEventListener('change', (e) => {
                        if (e.target.value === 'continue_celup' || e.target.value === 'continue_warna') {
                            materialsSection.classList.remove('hidden');
                        } else {
                            materialsSection.classList.add('hidden');
                            // Optional: clear out the materials if hidden
                        }
                    });
                });

                // Dynamic Material Rows
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
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">PILIH BARANG PENDUKUNG</label>
                            <select name="materials[${index}][material_id]" class="material-select w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold bg-white focus:outline-none" required>
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
                                    <input type="number" step="0.01" name="materials[${index}][weight]" class="weight-input w-full px-3 py-2 pr-12 rounded-xl border border-slate-300 bg-white text-sm font-mono font-bold focus:outline-none" required>
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
                if(!canvas) return;
                
                const ctx = canvas.getContext('2d');
                const clearBtn = document.getElementById('clearSignatureBtn');
                const signatureInput = document.getElementById('signatureData');
                const form = document.getElementById('coloringReportForm');
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
    @endif
</div>
@endsection
