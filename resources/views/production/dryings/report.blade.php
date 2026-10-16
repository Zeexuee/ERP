@extends('layouts.app', ['title' => 'Laporan Jemur Bahan Baku'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Laporan Selesai Jemur</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                Warehouse
            </span>
        </div>
        <a href="{{ route('production.dryings.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-semibold shadow-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('production.dryings.report', $log->id) }}" method="POST" id="reportInitForm" class="space-y-6">
        @csrf

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Informasi Bahan Baku
            </h3>

            <div class="grid grid-cols-1 gap-4">
                <div class="p-4 rounded-2xl bg-slate-100 border border-slate-200">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Barang Terpilih</span>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-900 text-sm">[{{ $log->material->code }}] {{ $log->material->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-500 block mb-0.5">Berat Sebelum Dijemur</span>
                            <span class="font-mono font-bold text-slate-900">{{ number_format($log->previous_weight, 2) }} {{ $log->material->unit }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tanggal Selesai Jemur <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="date" 
                            name="end_date" 
                            value="{{ old('end_date', date('Y-m-d')) }}" 
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                            required
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Berat Setelah Dijemur ({{ $log->material->unit }}) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.01" 
                            name="new_weight" 
                            value="{{ old('new_weight') }}" 
                            placeholder="Contoh: 100.50"
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs font-mono font-bold" 
                            required
                        >
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Penanggung Jawab (PIC) <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="pic_name" 
                            value="{{ old('pic_name') }}" 
                            placeholder="Nama penanggung jawab pelapor..."
                            class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" 
                            required
                        >
                    </div>
                </div>

                <div class="pt-2">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">
                            Tanda Tangan Pelapor (Opsional)
                        </label>
                        <button type="button" id="clearSignatureBtn" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 underline">
                            Hapus Tanda Tangan
                        </button>
                    </div>
                    <div class="p-3 rounded-2xl bg-white border border-slate-300 space-y-2">
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50 relative">
                            <canvas id="reportSignatureCanvas" height="140" class="w-full h-[140px] cursor-crosshair block"></canvas>
                        </div>
                        <div class="text-[10px] text-slate-400 font-semibold text-center">Gunakan mouse atau sentuhan layar untuk tanda tangan</div>
                    </div>
                    <input type="hidden" name="signature_data" id="signatureData">
                </div>
            </div>
        </div>

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
                Catatan (Opsional)
            </h3>

            <div>
                <textarea 
                    name="notes" 
                    rows="3" 
                    placeholder="Tulis catatan jika ada..."
                    class="w-full px-3 py-2 rounded-xl apple-input text-xs"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('production.dryings.index') }}" class="px-5 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                Simpan Laporan
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const canvas = document.getElementById('reportSignatureCanvas');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const clearBtn = document.getElementById('clearSignatureBtn');
        const signatureInput = document.getElementById('signatureData');
        const form = document.getElementById('reportInitForm');
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
