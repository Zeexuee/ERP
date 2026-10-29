@extends('layouts.app', ['title' => 'Proses Pengembalian Barang #' . $loan->loan_number])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-semibold mb-1">
                <a href="{{ route('stin.loans.index') }}" class="hover:text-slate-900 transition">Barang Dipinjam</a>
                <span>/</span>
                <a href="{{ route('stin.loans.show', $loan) }}" class="font-mono hover:text-slate-900 transition">{{ $loan->loan_number }}</a>
                <span>/</span>
                <span class="text-slate-900">Pengembalian</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Proses Pengembalian Barang Fisik</h2>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Verifikasi dan kembalikan unit fisik ke rak gudang STIN (mendukung pengembalian penuh atau bertahap/parsial).
            </p>
        </div>

        <a href="{{ route('stin.loans.show', $loan) }}" class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
            Batal
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

    <!-- Kartu Referensi Peminjaman -->
    <div class="p-4 rounded-2xl apple-glass-card flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
        <div>
            <span class="text-slate-500 font-medium block">Transaksi Acuan:</span>
            <span class="font-mono font-bold text-slate-900 text-sm">{{ $loan->loan_number }}</span>
            <span class="text-slate-500 ml-2">({{ $loan->loan_date?->translatedFormat('d M Y') }})</span>
        </div>
        <div>
            <span class="text-slate-500 font-medium block">Peminjam / Lokasi:</span>
            <span class="font-bold text-slate-900">{{ $loan->borrower_name }}</span> — {{ $loan->purpose }}
        </div>
        <div>
            <span class="text-slate-500 font-medium block">Total Sisa di Luar:</span>
            <span class="font-extrabold text-amber-800 font-mono text-sm">{{ $loan->totalQuantityRemaining() }} Unit</span>
        </div>
    </div>

    <form method="POST" action="{{ route('stin.loans.return.process', $loan) }}" id="returnForm" class="space-y-6">
        @csrf

        <!-- Panel 1: Data Penerimaan Gudang -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest pb-2 border-b border-slate-900/10">
                Informasi Penerima Gudang
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="receiver_name" class="block text-xs font-semibold text-slate-800 mb-1">
                        Nama Petugas Penerima Gudang (PIC) <span class="text-rose-600">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="receiver_name" 
                        name="receiver_name" 
                        value="{{ old('receiver_name', auth()->user()->name ?? 'Petugas STIN') }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs apple-input font-bold text-slate-900 border-slate-300 focus:border-slate-900"
                    >
                </div>

                <div>
                    <label for="return_date" class="block text-xs font-semibold text-slate-800 mb-1">
                        Tanggal Kembali <span class="text-rose-600">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="return_date" 
                        name="return_date" 
                        value="{{ old('return_date', date('Y-m-d')) }}" 
                        required 
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs apple-input font-medium text-slate-900 border-slate-300 focus:border-slate-900"
                    >
                </div>
            </div>

            <div>
                <label for="notes" class="block text-xs font-semibold text-slate-800 mb-1">
                    Catatan Sesi Pengembalian (Opsional)
                </label>
                <textarea 
                    name="notes" 
                    id="notes" 
                    rows="2" 
                    placeholder="Keterangan pengembalian, pengecekan fisik, atau informasi lainnya..." 
                    class="w-full px-3.5 py-2.5 rounded-xl text-xs apple-input text-slate-900 border-slate-300 focus:border-slate-900"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Panel 2: Verifikasi Kuantitas Barang yang Dikembalikan -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-slate-900/10 gap-2">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                        Item yang Dikembalikan ke Gudang
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Isi jumlah yang dikembalikan pada sesi ini. Isi <span class="font-bold">0</span> jika item belum dikembalikan.
                    </p>
                </div>
                <button type="button" id="fillAllRemainingBtn" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-bold shadow-xs transition">
                    Kembalikan Semua (Lunas)
                </button>
            </div>

            <div class="space-y-3">
                @php $idx = 0; @endphp
                @foreach($loan->items as $loanItem)
                    @php
                        $remaining = $loanItem->remainingQuantity();
                    @endphp
                    <div class="p-4 rounded-2xl bg-white border border-slate-300 space-y-3">
                        <input type="hidden" name="returns[{{ $idx }}][warehouse_loan_item_id]" value="{{ $loanItem->id }}">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-200">
                            <div>
                                <div class="font-bold text-slate-900 text-sm">{{ $loanItem->item?->name }}</div>
                                <div class="font-mono text-xs text-slate-500">{{ $loanItem->item?->sku }}</div>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Dipinjam</span>
                                    <span class="font-bold text-slate-800 font-mono">{{ $loanItem->quantity_loaned }} {{ $loanItem->item?->unit ?? 'pcs' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px] uppercase font-bold">Sudah Kembali</span>
                                    <span class="font-bold text-emerald-700 font-mono">{{ $loanItem->quantity_returned }} {{ $loanItem->item?->unit ?? 'pcs' }}</span>
                                </div>
                                <div>
                                    <span class="text-amber-600 block text-[10px] uppercase font-bold">Sisa di Luar</span>
                                    <span class="font-extrabold text-amber-800 font-mono">{{ $remaining }} {{ $loanItem->item?->unit ?? 'pcs' }}</span>
                                </div>
                            </div>
                        </div>

                        @if($remaining > 0)
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                                <div class="md:col-span-4">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">
                                        Jumlah Kembali Sekarang
                                    </label>
                                    <div class="relative">
                                        <input 
                                            type="number" 
                                            min="0" 
                                            max="{{ $remaining }}" 
                                            name="returns[{{ $idx }}][quantity]" 
                                            value="{{ old('returns.' . $idx . '.quantity', $remaining) }}" 
                                            data-remaining="{{ $remaining }}" 
                                            class="return-qty-input w-full px-3 py-2 rounded-xl text-xs font-mono font-bold text-slate-900 border-slate-300"
                                            required
                                        >
                                        <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">
                                            / {{ $remaining }} {{ $loanItem->item?->unit ?? 'pcs' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="md:col-span-3">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">
                                        Kondisi Fisik
                                    </label>
                                    <select name="returns[{{ $idx }}][condition]" class="w-full px-3 py-2 rounded-xl text-xs font-semibold text-slate-900 border-slate-300">
                                        <option value="good" {{ old('returns.' . $idx . '.condition') === 'good' ? 'selected' : '' }}>Baik & Utuh</option>
                                        <option value="damaged" {{ old('returns.' . $idx . '.condition') === 'damaged' ? 'selected' : '' }}>Ada Cacat / Rusak</option>
                                        <option value="lost" {{ old('returns.' . $idx . '.condition') === 'lost' ? 'selected' : '' }}>Hilang</option>
                                    </select>
                                </div>

                                <div class="md:col-span-5">
                                    <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">
                                        Catatan Pemeriksaan
                                    </label>
                                    <input 
                                        type="text" 
                                        name="returns[{{ $idx }}][notes]" 
                                        value="{{ old('returns.' . $idx . '.notes') }}" 
                                        placeholder="Kondisi kemasan / botol..." 
                                        class="w-full px-3 py-2 rounded-xl text-xs text-slate-800 border-slate-300"
                                    >
                                </div>
                            </div>
                        @else
                            <div class="p-2 rounded-xl bg-slate-50 text-xs font-medium text-slate-500 italic">
                                Barang ini sudah dikembalikan lunas sebelumnya.
                                <input type="hidden" name="returns[{{ $idx }}][quantity]" value="0">
                            </div>
                        @endif
                    </div>
                    @php $idx++; @endphp
                @endforeach
            </div>
        </div>

        <!-- Panel 3: Tanda Tangan Penerima Gudang -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-900/10">
                <div>
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                        Tanda Tangan Digital Penerima Gudang
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Tanda tangan petugas sebagai bukti fisik barang telah diterima kembali di gudang STIN.
                    </p>
                </div>
                <button type="button" id="clearSigBtn" class="text-xs font-bold text-slate-700 hover:text-slate-900 underline">
                    Hapus Tanda Tangan
                </button>
            </div>

            <div class="border border-slate-300 rounded-2xl overflow-hidden bg-white relative">
                <canvas id="signatureCanvas" width="700" height="150" class="w-full h-[150px] cursor-crosshair block"></canvas>
                <div id="canvasPlaceholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-xs text-slate-400 select-none">
                    Gunakan mouse atau layar sentuh untuk tanda tangan
                </div>
            </div>
            <input type="hidden" name="signature_data" id="signatureData">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('stin.loans.show', $loan) }}" class="px-5 py-2.5 rounded-2xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-2xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-md transition">
                Konfirmasi Pengembalian Barang
            </button>
        </div>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const fillAllBtn = document.getElementById('fillAllRemainingBtn');
        const qtyInputs = document.querySelectorAll('.return-qty-input');

        if (fillAllBtn) {
            fillAllBtn.addEventListener('click', () => {
                qtyInputs.forEach(input => {
                    const rem = input.dataset.remaining;
                    if (rem) input.value = rem;
                });
            });
        }

        // Canvas Signature
        const canvas = document.getElementById('signatureCanvas');
        const clearBtn = document.getElementById('clearSigBtn');
        const sigInput = document.getElementById('signatureData');
        const placeholder = document.getElementById('canvasPlaceholder');
        const ctx = canvas.getContext('2d');
        const form = document.getElementById('returnForm');

        let isDrawing = false;
        let hasSignature = false;

        function resizeCanvas() {
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = 150;
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
