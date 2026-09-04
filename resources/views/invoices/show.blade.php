@extends('layouts.app', ['title' => 'Detail Faktur & Pembayaran'])

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-extrabold font-mono text-slate-900">{{ $invoice->invoice_number }}</h2>
                <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                    STATUS: {{ strtoupper($invoice->status->value) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">Diterbitkan: {{ $invoice->created_at->format('d M Y') }} • Jatuh Tempo: {{ $invoice->due_date->format('d M Y') }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if($invoice->status->value !== 'paid' && $invoice->status->value !== 'cancelled')
                <button type="button" onclick="openPaymentModal()" class="px-6 py-2.5 rounded-full btn-dark text-xs font-bold">
                    Catat Pembayaran
                </button>
            @endif

            <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
        </div>
    </div>

    <!-- Metrics -->
    @php
        $totalPaid = $invoice->payments->sum('amount');
        $remaining = max(0, $invoice->total_amount - $totalPaid);
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Tagihan</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
        </div>

        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Sudah Dibayar</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
        </div>

        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Sisa Tagihan</span>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">Rp {{ number_format($remaining, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Invoice Details & Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Item Tagihan</h3>
                    <span class="text-xs text-slate-500">Pelanggan: <strong class="text-slate-900">{{ $invoice->salesOrder->customer->name }}</strong></span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-800">
                        <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                            <tr>
                                <th class="px-4 py-3">Produk</th>
                                <th class="px-4 py-3 text-center">Jumlah</th>
                                <th class="px-4 py-3 text-right">Harga Satuan</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900/10">
                            @foreach($invoice->salesOrder->items as $item)
                                <tr>
                                    <td class="px-4 py-3 font-bold text-slate-900">{{ $item->product->name }}</td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-900">{{ $item->quantity }} {{ $item->unit ?? 'kg' }}</td>
                                    <td class="px-4 py-3 text-right text-slate-800 font-semibold">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-900/10 font-bold">
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right text-slate-500 uppercase text-xs">Total:</td>
                                <td class="px-4 py-4 text-right text-xl text-slate-900">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payment History Sidebar -->
        <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                Riwayat Pembayaran ({{ $invoice->payments->count() }})
            </h3>

            <div class="space-y-3">
                @forelse($invoice->payments as $p)
                    <div class="p-4 rounded-xl bg-white/70 border border-slate-900/10 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-slate-900">{{ $p->payment_number }}</span>
                            <span class="text-[10px] text-slate-500 font-bold">{{ $p->payment_date->format('d M Y') }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-600 font-medium">{{ $p->payment_method }}</span>
                            <span class="text-sm font-extrabold text-slate-900">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                        </div>

                        @if($p->pic_name)
                            <div class="pt-1.5 border-t border-slate-900/5 text-xs">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold">Penanggung Jawab:</span>
                                <span class="font-bold text-slate-800">{{ $p->pic_name }}</span>
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-1 text-[11px] gap-2">
                            @if($p->proof_file)
                                <a href="{{ asset('storage/' . $p->proof_file) }}" target="_blank" class="font-bold text-slate-900 hover:underline">
                                    Lihat Bukti Bayar ↗
                                </a>
                            @else
                                <span class="text-slate-400">Tanpa lampiran</span>
                            @endif

                            @if($p->signature)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold badge-dark">Bertanda tangan</span>
                            @endif
                        </div>

                        @if($p->signature)
                            <div class="pt-2 border-t border-slate-900/5">
                                <span class="text-[10px] text-slate-400 block uppercase font-bold mb-1">Tanda Tangan PIC:</span>
                                <div class="p-2 rounded-lg bg-white border border-slate-900/10 inline-block">
                                    <img src="{{ $p->signature }}" alt="Tanda Tangan" class="h-10 max-w-full object-contain">
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center font-medium">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<!-- Modal Glassmorphism Catat Pembayaran Baru -->
<div id="paymentModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-md hidden p-4 overflow-y-auto">
    <div class="apple-glass-panel max-w-lg w-full p-6 md:p-8 rounded-3xl border border-white shadow-2xl relative my-8">
        <h3 class="text-xl font-extrabold text-slate-900 mb-1">Catat Pembayaran Baru</h3>
        <p class="text-xs text-slate-500 mb-5 font-medium">Sisa Tagihan: <strong class="text-slate-900 font-bold">Rp {{ number_format($remaining, 0, ',', '.') }}</strong></p>

        <form id="paymentForm" action="{{ route('invoices.payments.store', $invoice) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Jumlah Pembayaran (Rp) *</label>
                    <input type="number" name="amount" value="{{ $remaining }}" max="{{ $remaining }}" min="1" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-bold text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Metode Pembayaran *</label>
                    <select name="payment_method" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium bg-white">
                        <option value="Bank Transfer (BCA)">Bank Transfer (BCA)</option>
                        <option value="Bank Transfer (Mandiri)">Bank Transfer (Mandiri)</option>
                        <option value="Cek / Giro">Cek / Giro</option>
                        <option value="Tunai (Cash)">Tunai (Cash)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tanggal Pembayaran *</label>
                    <input type="date" name="payment_date" value="{{ \Carbon\Carbon::now()->toDateString() }}" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Penerima *</label>
                    <input type="text" name="pic_name" required placeholder="Diproses Oleh " class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
                </div>
            </div>

            <!-- Upload Bukti Pembayaran -->
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Bukti Pembayaran (Struk / Slip / Transfer)</label>
                <input type="file" name="proof_file" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 apple-input p-2 rounded-xl bg-white/70">
                <span class="text-[10px] text-slate-400 mt-1 block">Maksimal 5MB (JPG, PNG, atau PDF)</span>
            </div>

            <!-- Block Tanda Tangan Penanggung Jawab -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Tanda Tangan Penanggung Jawab</label>
                    <button type="button" onclick="clearSignature()" class="text-[11px] font-bold text-slate-500 hover:text-slate-900">Hapus / Ulangi</button>
                </div>
                <div class="border border-slate-900/20 rounded-2xl bg-white overflow-hidden relative shadow-inner">
                    <canvas id="signatureCanvas" class="w-full h-32 touch-none cursor-crosshair block bg-white"></canvas>
                    <div id="signaturePlaceholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-300 text-xs font-medium">
                        Goreskan tanda tangan di area ini
                    </div>
                </div>
                <input type="hidden" name="signature" id="signatureInput">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-900/10">
                <button type="button" onclick="closePaymentModal()" class="px-5 py-2.5 rounded-full btn-subtle text-sm">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-sm">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    let isDrawing = false;
    let hasDrawn = false;
    let canvas, ctx, signatureInput, placeholder;

    function openPaymentModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
        setTimeout(initSignatureCanvas, 100);
    }

    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }

    function initSignatureCanvas() {
        canvas = document.getElementById('signatureCanvas');
        if (!canvas) return;

        ctx = canvas.getContext('2d');
        signatureInput = document.getElementById('signatureInput');
        placeholder = document.getElementById('signaturePlaceholder');

        // Set high-res canvas scaling
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        ctx.strokeStyle = '#0f172a';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        // Mouse events
        canvas.onmousedown = (e) => {
            isDrawing = true;
            hasDrawn = true;
            if (placeholder) placeholder.style.display = 'none';
            const { x, y } = getCanvasPos(e);
            ctx.beginPath();
            ctx.moveTo(x, y);
        };

        window.onmousemove = (e) => {
            if (!isDrawing) return;
            const { x, y } = getCanvasPos(e);
            ctx.lineTo(x, y);
            ctx.stroke();
        };

        window.onmouseup = () => {
            if (isDrawing) {
                isDrawing = false;
                syncSignatureData();
            }
        };

        // Touch events
        canvas.ontouchstart = (e) => {
            e.preventDefault();
            isDrawing = true;
            hasDrawn = true;
            if (placeholder) placeholder.style.display = 'none';
            const touch = e.touches[0];
            const { x, y } = getCanvasPos(touch);
            ctx.beginPath();
            ctx.moveTo(x, y);
        };

        canvas.ontouchmove = (e) => {
            e.preventDefault();
            if (!isDrawing) return;
            const touch = e.touches[0];
            const { x, y } = getCanvasPos(touch);
            ctx.lineTo(x, y);
            ctx.stroke();
        };

        canvas.ontouchend = (e) => {
            if (isDrawing) {
                isDrawing = false;
                syncSignatureData();
            }
        };
    }

    function getCanvasPos(e) {
        const rect = canvas.getBoundingClientRect();
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }

    function clearSignature() {
        if (!canvas || !ctx) return;
        const rect = canvas.getBoundingClientRect();
        ctx.clearRect(0, 0, rect.width, rect.height);
        hasDrawn = false;
        if (signatureInput) signatureInput.value = '';
        if (placeholder) placeholder.style.display = 'flex';
    }

    function syncSignatureData() {
        if (hasDrawn && canvas && signatureInput) {
            signatureInput.value = canvas.toDataURL('image/png');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('paymentForm');
        if (form) {
            form.addEventListener('submit', () => {
                syncSignatureData();
            });
        }
    });
</script>
@endpush
