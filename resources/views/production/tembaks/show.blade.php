@extends('layouts.app', ['title' => 'Detail Sesi Tembak Kayu'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">
                Sesi Tembak <span class="text-slate-500 font-mono">{{ $tembakBatch->tembak_code }}</span>
            </h2>
        </div>
        <a href="{{ route('production.tembaks.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 text-xs font-bold shadow-xs transition uppercase tracking-widest">
            KEMBALI
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-bold shadow-xs">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300 text-slate-900 text-xs font-bold shadow-xs">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Informasi Sesi Tembak -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2 uppercase tracking-widest">Informasi Sesi</h3>
            
            <div class="space-y-3 text-sm text-slate-900">
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $tembakBatch->isCompleted() ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-900 border border-slate-300' }} uppercase tracking-widest">
                        {{ $tembakBatch->status_label }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanggal Inisiasi</span>
                    <span class="font-bold">{{ $tembakBatch->tembak_date->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">PIC Inisiasi</span>
                    <span class="font-bold">{{ $tembakBatch->pic_name }}</span>
                </div>
                
                @if($tembakBatch->notes)
                <div class="pt-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Catatan Inisiasi</span>
                    <p class="text-xs text-slate-900 bg-slate-50 rounded-xl p-3 border border-slate-200">{{ $tembakBatch->notes }}</p>
                </div>
                @endif
                
                @if($tembakBatch->signature_path)
                <div class="pt-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Tanda Tangan Inisiasi</span>
                    <div class="bg-white rounded-xl border border-slate-300 p-2 inline-block">
                        <img src="{{ Storage::url($tembakBatch->signature_path) }}" alt="TTD Inisiasi" class="h-16 grayscale">
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Rincian Bahan -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2 uppercase tracking-widest">Bahan Digunakan</h3>
            
            @php
                $totalModal = 0;
                $woods = $tembakBatch->materials->where('type', 'wood');
                $resins = $tembakBatch->materials->where('type', 'resin');
            @endphp
            
            <div class="space-y-5">
                <!-- Kayu -->
                <div>
                    <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Bahan Tembak</h4>
                    <div class="space-y-2">
                        @foreach($woods as $m)
                            @php $sub = $m->weight * $m->unit_cost; $totalModal += $sub; @endphp
                            <div class="flex justify-between text-xs items-center bg-slate-100 border border-slate-200 rounded-lg p-2.5">
                                <span class="text-slate-900 font-bold">{{ $m->material->name }} ({{ number_format($m->weight, 2) }} {{ $m->material->unit }})</span>
                                <span class="font-mono font-bold text-slate-900">Rp {{ number_format($sub, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Resin -->
                <div>
                    <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Getah</h4>
                    <div class="space-y-2">
                        @foreach($resins as $m)
                            @php $sub = $m->weight * $m->unit_cost; $totalModal += $sub; @endphp
                            <div class="flex justify-between text-xs items-center bg-slate-100 border border-slate-200 rounded-lg p-2.5">
                                <span class="text-slate-900 font-bold">{{ $m->material->name }} ({{ number_format($m->weight, 2) }} {{ $m->material->unit }})</span>
                                <span class="font-mono font-bold text-slate-900">Rp {{ number_format($sub, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end items-center">
                    <span class="text-xl font-bold font-mono">Rp {{ number_format($totalModal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Bagian Pelaporan -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md mt-6">
        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2 mb-4 uppercase tracking-widest">Laporan Hasil Tembak</h3>
        
        @if(!$tembakBatch->isCompleted())
            <form action="{{ route('production.tembaks.store-report', $tembakBatch) }}" method="POST" id="reportForm" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Timbang Basah -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-300">
                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">Berat Timbang Basah (kg)</label>
                        <input type="number" step="0.01" name="wet_result_weight" value="{{ old('wet_result_weight') }}" class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" required>
                    </div>

                    <!-- Getah Sisa -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-300">
                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">Getah Sisa (kg)</label>
                        <input type="number" step="0.01" name="residual_resin_weight" value="{{ old('residual_resin_weight') }}" class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold">
                        
                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mt-3 mb-1">Bahan Getah Sisa (Gudang)</label>
                        <select name="residual_resin_material_id" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold">
                            <option value="">(Opsional) Pilih tujuan getah sisa</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}" {{ old('residual_resin_material_id') == $mat->id ? 'selected' : '' }}>[{{ $mat->code }}] {{ $mat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Hasil Kering -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-300">
                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">Berat Hasil Kering (kg)</label>
                        <input type="number" step="0.01" name="dried_result_weight" value="{{ old('dried_result_weight') }}" class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" required>

                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mt-3 mb-1">Bahan Hasil Jadi (Gudang)</label>
                        <select name="output_material_id" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                            <option value="">Pilih bahan masuk gudang</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}" {{ old('output_material_id') == $mat->id ? 'selected' : '' }}>[{{ $mat->code }}] {{ $mat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200 pt-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">Tanggal Laporan</label>
                            <input type="date" name="report_date" value="{{ old('report_date') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">Pelapor (PIC)</label>
                            <input type="text" name="report_pic_name" value="{{ old('report_pic_name') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-bold" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest mb-1">Catatan Laporan</label>
                            <textarea name="report_notes" rows="2" class="w-full px-3 py-2 rounded-xl apple-input text-xs">{{ old('report_notes') }}</textarea>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan Pelapor</label>
                            <button type="button" id="clearRepSignatureBtn" class="text-[11px] font-bold text-slate-900 hover:underline uppercase tracking-widest">HAPUS</button>
                        </div>
                        <div class="p-3 rounded-2xl bg-white border border-slate-300 space-y-2">
                            <div class="border border-slate-300 rounded-xl overflow-hidden bg-slate-50 relative">
                                <canvas id="repSignatureCanvas" width="340" height="150" class="w-full h-[150px] cursor-crosshair"></canvas>
                            </div>
                            <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest text-center">Gunakan mouse atau sentuhan jari</div>
                        </div>
                        <input type="hidden" name="signature_data" id="repSignatureData">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-3 rounded-xl btn-dark text-xs font-bold shadow-md transition uppercase tracking-widest">
                        SIMPAN LAPORAN TEMBAK
                    </button>
                </div>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const canvas = document.getElementById('repSignatureCanvas');
                    const ctx = canvas.getContext('2d');
                    const clearBtn = document.getElementById('clearRepSignatureBtn');
                    const signatureInput = document.getElementById('repSignatureData');
                    const form = document.getElementById('reportForm');
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
                        return { x: (clientX - rect.left) * scaleX, y: (clientY - rect.top) * scaleY };
                    }

                    canvas.addEventListener('mousedown', (e) => {
                        isDrawing = true; hasSignature = true;
                        const coords = getCoordinates(e);
                        ctx.beginPath(); ctx.moveTo(coords.x, coords.y); e.preventDefault();
                    });

                    canvas.addEventListener('mousemove', (e) => {
                        if (!isDrawing) return;
                        const coords = getCoordinates(e);
                        ctx.lineTo(coords.x, coords.y); ctx.stroke(); e.preventDefault();
                    });

                    const stopDrawing = () => { isDrawing = false; };
                    canvas.addEventListener('mouseup', stopDrawing);
                    canvas.addEventListener('mouseleave', stopDrawing);

                    canvas.addEventListener('touchstart', (e) => {
                        isDrawing = true; hasSignature = true;
                        const coords = getCoordinates(e);
                        ctx.beginPath(); ctx.moveTo(coords.x, coords.y); e.preventDefault();
                    }, { passive: false });
                    canvas.addEventListener('touchmove', (e) => {
                        if (!isDrawing) return;
                        const coords = getCoordinates(e);
                        ctx.lineTo(coords.x, coords.y); ctx.stroke(); e.preventDefault();
                    }, { passive: false });
                    canvas.addEventListener('touchend', stopDrawing);

                    clearBtn.addEventListener('click', () => {
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        signatureInput.value = ''; hasSignature = false;
                    });

                    form.addEventListener('submit', () => {
                        if (hasSignature) { signatureInput.value = canvas.toDataURL('image/png'); }
                    });
                });
            </script>
        @else
            <!-- View Completed Report -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-slate-900">
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Timbang Basah</span>
                        <span class="font-mono font-bold text-slate-900">{{ number_format($tembakBatch->wet_result_weight, 2) }} kg</span>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Getah Sisa</span>
                        <div class="text-right">
                            <div class="font-mono font-bold text-slate-900">{{ number_format($tembakBatch->residual_resin_weight, 2) }} kg</div>
                            @if($tembakBatch->residualResinMaterial)
                                <div class="text-[10px] font-bold text-slate-500">{{ $tembakBatch->residualResinMaterial->name }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-slate-900 text-white shadow-sm">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-300">Hasil Kering</span>
                        <div class="text-right">
                            <div class="font-mono font-bold text-xl">{{ number_format($tembakBatch->dried_result_weight, 2) }} kg</div>
                            @if($tembakBatch->outputMaterial)
                                <div class="text-[10px] font-bold text-slate-300">{{ $tembakBatch->outputMaterial->name }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-4 md:border-l md:border-slate-200 md:pl-6">
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Tgl Laporan</span>
                        <span class="font-bold">{{ $tembakBatch->report_date ? $tembakBatch->report_date->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Pelapor</span>
                        <span class="font-bold">{{ $tembakBatch->report_pic_name }}</span>
                    </div>
                    
                    @if($tembakBatch->report_notes)
                    <div class="pt-2">
                        <span class="block text-[10px] font-bold uppercase tracking-widest text-slate-500 mb-1">Catatan Laporan</span>
                        <p class="text-xs text-slate-900 bg-slate-50 p-3 rounded-xl border border-slate-200">{{ $tembakBatch->report_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
