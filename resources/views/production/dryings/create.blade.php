@extends('layouts.app', ['title' => 'Inisiasi Jemur Bahan Baku'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Inisiasi Jemur Bahan Baku</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                Warehouse
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                Tahap 1
            </span>
        </div>
        <a href="{{ route('production.dryings.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-white border-2 border-slate-900 text-slate-900 text-xs font-semibold shadow-xs">
            <div class="font-bold mb-1 uppercase tracking-wider">Periksa Kembali Data Input:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($materials->isEmpty())
        <div class="p-4 rounded-2xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold shadow-xs">
            Tidak ada bahan baku berkategori KCB dengan stok tersedia yang siap dijemur. Pastikan bahan baku sudah selesai dari proses aktif lainnya (seperti sortir) atau input bahan baku KCB baru di gudang.
        </div>
    @endif

    <form action="{{ route('production.dryings.store') }}" method="POST" id="dryingInitForm" class="space-y-6">
        @csrf

        <!-- Panel Form Utama -->
        <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-5">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Pilih Bahan Baku Mentah
                </h3>
                <p class="text-xs text-slate-600 mt-0.5">Pilih bahan baku KCB dari gudang yang siap masuk proses penjemuran.</p>
            </div>

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label for="material_id" class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                        Pilih Bahan Baku (Khusus KCB) <span class="text-slate-900">*</span>
                    </label>
                    <select 
                        name="material_id" 
                        id="material_id" 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-slate-900 focus:outline-none" 
                        required
                        onchange="updateSelectedStock(this)"
                        {{ $materials->isEmpty() ? 'disabled' : '' }}
                    >
                        <option value="" disabled {{ !isset($material) && !old('material_id') ? 'selected' : '' }}>
                            {{ $materials->isEmpty() ? 'Tidak ada bahan KCB yang siap dijemur' : '-- Pilih Bahan Baku KCB Siap Jemur --' }}
                        </option>
                        @foreach($materials as $mat)
                            <option 
                                value="{{ $mat->id }}" 
                                data-code="{{ $mat->code }}"
                                data-name="{{ $mat->name }}"
                                data-stock="{{ number_format($mat->stock_quantity, 2) }}" 
                                data-unit="{{ $mat->unit }}"
                                {{ (old('material_id', $material->id ?? '') == $mat->id) ? 'selected' : '' }}
                            >
                                [{{ $mat->code }}] {{ $mat->name }} (Stok: {{ number_format($mat->stock_quantity, 2) }} {{ $mat->unit }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="selectedMaterialCard" class="p-3.5 rounded-xl border border-slate-300 bg-slate-50 {{ (!isset($material) && !old('material_id')) ? 'hidden' : '' }}">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-600 mb-1">Detail Bahan Terpilih</span>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span id="selectedMaterialName" class="font-bold text-slate-900 text-sm">
                                {{ isset($material) ? "[{$material->code}] {$material->name}" : '-' }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-900 text-white">KCB</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-600 block mb-0.5 font-medium">Stok Saat Ini</span>
                            <span id="selectedMaterialStock" class="font-mono font-bold text-slate-900 text-sm">
                                {{ isset($material) ? number_format($material->stock_quantity, 2) . ' ' . $material->unit : '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2 border-t border-slate-200">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Tanggal Mulai Jemur <span class="text-slate-900">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="start_date" 
                            value="{{ old('start_date', date('Y-m-d')) }}" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold focus:border-slate-900 focus:outline-none" 
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Waktu Jemur (Jam Mulai) <span class="text-slate-900">*</span>
                        </label>
                        <input 
                            type="time" 
                            name="start_time" 
                            value="{{ old('start_time', date('H:i')) }}" 
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-mono font-bold focus:border-slate-900 focus:outline-none" 
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-900 mb-1.5">
                            Penanggung Jawab (PIC) <span class="text-slate-900">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="pic_name" 
                            value="{{ old('pic_name') }}" 
                            placeholder="Nama penanggung jawab..."
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold placeholder:text-slate-500 focus:border-slate-900 focus:outline-none" 
                            required
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-900">
                            Tanda Tangan PIC (Opsional)
                        </label>
                        <button type="button" id="clearSignatureBtn" class="text-xs font-bold text-slate-600 hover:text-slate-900 underline">
                            Hapus Tanda Tangan
                        </button>
                    </div>
                    <div class="p-3 rounded-2xl bg-white border border-slate-300 space-y-2">
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 relative">
                            <canvas id="dryingSignatureCanvas" height="140" class="w-full h-[140px] cursor-crosshair block"></canvas>
                        </div>
                        <div class="text-[11px] text-slate-500 font-semibold text-center">Gunakan mouse atau sentuhan layar untuk tanda tangan</div>
                    </div>
                    <input type="hidden" name="signature_data" id="signatureData">
                </div>
            </div>
        </div>

        <!-- Panel Catatan -->
        <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-b border-slate-200 pb-2">
                Catatan Operasional (Opsional)
            </h3>

            <div>
                <textarea 
                    name="notes" 
                    rows="3" 
                    placeholder="Tulis catatan jika ada..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 text-xs font-medium placeholder:text-slate-500 focus:border-slate-900 focus:outline-none"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.dryings.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition">
                Batal
            </a>
            <button 
                type="submit" 
                class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed" 
                {{ $materials->isEmpty() ? 'disabled' : '' }}
            >
                Mulai Jemur
            </button>
        </div>
    </form>
</div>

<script>
    function updateSelectedStock(selectEl) {
        const selected = selectEl.options[selectEl.selectedIndex];
        const card = document.getElementById('selectedMaterialCard');
        const nameEl = document.getElementById('selectedMaterialName');
        const stockEl = document.getElementById('selectedMaterialStock');
        
        if (selected && selected.value) {
            card.classList.remove('hidden');
            nameEl.textContent = `[${selected.dataset.code}] ${selected.dataset.name}`;
            stockEl.textContent = `${selected.dataset.stock} ${selected.dataset.unit}`;
        } else {
            card.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const materialSelect = document.getElementById('material_id');
        if (materialSelect && materialSelect.value) {
            updateSelectedStock(materialSelect);
        }

        const canvas = document.getElementById('dryingSignatureCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const signatureInput = document.getElementById('signatureData');
        const form = document.getElementById('dryingInitForm');
        let isDrawing = false;
        let hasSignature = false;

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width || 350;
            canvas.height = 140;
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        resizeCanvas();
        window.addEventListener('resize', () => {
            if (!hasSignature) {
                resizeCanvas();
            }
        });

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

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
                hasSignature = false;
            });
        }

        if (form) {
            form.addEventListener('submit', () => {
                if (hasSignature) {
                    signatureInput.value = canvas.toDataURL('image/png');
                } else {
                    signatureInput.value = '';
                }
            });
        }
    });
</script>
@endsection
