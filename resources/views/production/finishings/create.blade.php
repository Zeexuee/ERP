@extends('layouts.app', ['title' => 'Mulai Proses Finishing'])

@section('content')
@php
    $mappedMaterials = $materials->map(fn ($material) => [
        'id' => $material->id,
        'stock' => (float) $material->stock_quantity,
        'cost' => (float) $material->unit_cost,
        'branch' => $material->branch_label,
    ])->values();
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">Mulai Finishing</h2>
            <p class="mt-1 text-xs text-slate-500">Bahan hasil tembak keluar dari gudang dan identitas branch-nya terbawa ke sesi finishing.</p>
        </div>
        <a href="{{ route('production.finishings.index') }}" class="self-start rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 sm:self-auto uppercase tracking-widest">
            Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 shadow-xs">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 shadow-xs">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('production.finishings.store') }}" method="POST" id="finishingInitForm" class="space-y-6">
        @csrf

        <section class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
            <h3 class="border-b border-slate-900/10 pb-2 text-sm font-bold text-slate-900 uppercase tracking-widest">Bahan Yang Difinishing</h3>

            @if($materials->isEmpty())
                <p class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-xs font-bold text-amber-800">
                    Belum ada barang gudang bersatuan kg dengan stok tersedia. Selesaikan proses tembak dan jemur terlebih dahulu.
                </p>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="source_material_id" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Barang Gudang</label>
                        <select id="source_material_id" name="source_material_id" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                            <option value="">Pilih bahan hasil tembak</option>
                            @foreach($materials as $material)
                                <option value="{{ $material->id }}" @selected((string) old('source_material_id') === (string) $material->id)>
                                    [{{ $material->code }}] {{ $material->name }} — stok {{ number_format($material->stock_quantity, 2) }} kg — branch {{ $material->branch_label }}
                                </option>
                            @endforeach
                        </select>
                        <p id="branchPreview" class="mt-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest"></p>
                    </div>

                    <div>
                        <label for="initial_weight" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Berat Masuk Finishing</label>
                        <div class="relative">
                            <input id="initial_weight" type="number" name="initial_weight" value="{{ old('initial_weight') }}" min="0.01" step="0.01" class="apple-input w-full rounded-xl px-3 py-2 pr-10 font-mono text-sm font-bold" required>
                            <span class="absolute inset-y-0 right-3 flex items-center text-[10px] font-bold text-slate-500">kg</span>
                        </div>
                        <p id="stockHint" class="mt-1 text-[10px] font-bold text-slate-400"></p>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4">
                        <span class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Perkiraan Modal Bahan</span>
                        <div id="estimatedCost" class="font-mono text-xl font-bold text-slate-900">Rp 0</div>
                    </div>
                </div>
            @endif
        </section>

        <section class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
            <h3 class="border-b border-slate-900/10 pb-2 text-sm font-bold text-slate-900 uppercase tracking-widest">Detail Tanggal & Penanggung Jawab</h3>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <div>
                        <label for="finishing_date" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanggal Mulai</label>
                        <input id="finishing_date" type="date" name="finishing_date" value="{{ old('finishing_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                    </div>
                    <div>
                        <label for="pic_name" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Nama PIC</label>
                        <input id="pic_name" type="text" name="pic_name" value="{{ old('pic_name', auth()->user()->name ?? '') }}" maxlength="100" class="apple-input w-full rounded-xl px-3 py-2 text-xs font-bold" required>
                    </div>
                    <div>
                        <label for="notes" class="mb-1 block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Catatan</label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="apple-input w-full rounded-xl px-3 py-2 text-xs" placeholder="Rencana proses finishing">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between gap-3">
                        <label class="block text-[10px] font-bold text-slate-900 uppercase tracking-widest">Tanda Tangan PIC</label>
                        <button type="button" id="clearSignature" class="text-[10px] font-bold text-slate-500 transition hover:text-slate-900 uppercase tracking-widest">Hapus</button>
                    </div>
                    <div class="space-y-2 rounded-2xl border border-slate-300 bg-white p-3">
                        <div class="overflow-hidden rounded-xl border border-slate-300 bg-slate-50">
                            <canvas id="signatureCanvas" width="680" height="300" class="h-[150px] w-full cursor-crosshair touch-none"></canvas>
                        </div>
                        <p class="text-center text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tanda tangani menggunakan mouse atau sentuhan jari</p>
                    </div>
                    <input type="hidden" name="signature_data" id="signatureData">
                </div>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('production.finishings.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 uppercase tracking-widest">
                Batal
            </a>
            <button type="submit" class="btn-dark rounded-xl px-6 py-3 text-xs font-bold shadow-md transition uppercase tracking-widest" @disabled($materials->isEmpty())>
                Mulai Finishing
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const materials = @json($mappedMaterials);
        const materialSelect = document.getElementById('source_material_id');
        const weightInput = document.getElementById('initial_weight');
        const stockHint = document.getElementById('stockHint');
        const branchPreview = document.getElementById('branchPreview');
        const estimatedCost = document.getElementById('estimatedCost');

        const findMaterial = () => materials.find(
            (material) => String(material.id) === String(materialSelect?.value)
        );

        const refreshSummary = () => {
            const material = findMaterial();

            if (!material) {
                if (stockHint) stockHint.textContent = '';
                if (branchPreview) branchPreview.textContent = '';
                if (estimatedCost) estimatedCost.textContent = 'Rp 0';
                if (weightInput) weightInput.removeAttribute('max');

                return;
            }

            if (weightInput) {
                weightInput.max = material.stock;
            }

            if (stockHint) {
                stockHint.textContent = `Stok tersedia ${material.stock.toFixed(2)} kg`;
            }

            if (branchPreview) {
                branchPreview.textContent = `Identitas branch yang terbawa: ${material.branch}`;
            }

            if (estimatedCost) {
                const weight = parseFloat(weightInput?.value ?? '0') || 0;
                estimatedCost.textContent = 'Rp ' + Math.round(weight * material.cost).toLocaleString('id-ID');
            }
        };

        materialSelect?.addEventListener('change', refreshSummary);
        weightInput?.addEventListener('input', refreshSummary);
        refreshSummary();

        const form = document.getElementById('finishingInitForm');
        const canvas = document.getElementById('signatureCanvas');
        const signatureInput = document.getElementById('signatureData');
        const clearSignatureButton = document.getElementById('clearSignature');

        if (form && canvas && signatureInput) {
            const context = canvas.getContext('2d');
            let isDrawing = false;
            let hasSignature = false;
            let previousPoint = null;

            context.strokeStyle = '#0f172a';
            context.lineWidth = 5;
            context.lineCap = 'round';
            context.lineJoin = 'round';

            const getPoint = (event) => {
                const bounds = canvas.getBoundingClientRect();

                return {
                    x: (event.clientX - bounds.left) * (canvas.width / bounds.width),
                    y: (event.clientY - bounds.top) * (canvas.height / bounds.height),
                };
            };

            canvas.addEventListener('pointerdown', (event) => {
                isDrawing = true;
                previousPoint = getPoint(event);
                canvas.setPointerCapture(event.pointerId);
                event.preventDefault();
            });

            canvas.addEventListener('pointermove', (event) => {
                if (!isDrawing || !previousPoint) {
                    return;
                }

                const currentPoint = getPoint(event);
                context.beginPath();
                context.moveTo(previousPoint.x, previousPoint.y);
                context.lineTo(currentPoint.x, currentPoint.y);
                context.stroke();
                previousPoint = currentPoint;
                hasSignature = true;
                event.preventDefault();
            });

            const stopDrawing = (event) => {
                if (isDrawing && canvas.hasPointerCapture(event.pointerId)) {
                    canvas.releasePointerCapture(event.pointerId);
                }

                isDrawing = false;
                previousPoint = null;
            };

            canvas.addEventListener('pointerup', stopDrawing);
            canvas.addEventListener('pointercancel', stopDrawing);

            clearSignatureButton?.addEventListener('click', () => {
                context.clearRect(0, 0, canvas.width, canvas.height);
                signatureInput.value = '';
                hasSignature = false;
            });

            form.addEventListener('submit', () => {
                if (hasSignature) {
                    signatureInput.value = canvas.toDataURL('image/png');
                }
            });
        }
    });
</script>
@endsection
