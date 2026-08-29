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
                <button type="button" onclick="document.getElementById('paymentModal').classList.remove('hidden')" class="px-6 py-2.5 rounded-full btn-dark text-xs font-bold">
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
                                    <td class="px-4 py-3 text-center font-bold text-slate-900">{{ $item->quantity }} unit</td>
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
                    <div class="p-4 rounded-xl bg-white/70 border border-slate-900/10 space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-slate-900">{{ $p->payment_number }}</span>
                            <span class="text-[10px] text-slate-500 font-bold">{{ $p->payment_date->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-xs text-slate-600 font-medium">{{ $p->payment_method }}</span>
                            <span class="text-sm font-extrabold text-slate-900">Rp {{ number_format($p->amount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 py-4 text-center font-medium">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Modal Glassmorphism Catat Pembayaran (Dark Neutral Buttons) -->
<div id="paymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-md hidden">
    <div class="apple-glass-panel max-w-md w-full p-8 rounded-3xl border border-white shadow-2xl relative">
        <h3 class="text-xl font-extrabold text-slate-900 mb-1">Catat Pembayaran Baru</h3>
        <p class="text-xs text-slate-500 mb-5 font-medium">Sisa Tagihan: <strong class="text-slate-900 font-bold">Rp {{ number_format($remaining, 0, ',', '.') }}</strong></p>

        <form action="{{ route('invoices.payments.store', $invoice) }}" method="POST" class="space-y-4">
            @csrf
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

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tanggal Pembayaran *</label>
                <input type="date" name="payment_date" value="{{ \Carbon\Carbon::now()->toDateString() }}" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-900/10">
                <button type="button" onclick="document.getElementById('paymentModal').classList.add('hidden')" class="px-5 py-2.5 rounded-full btn-subtle text-sm">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-sm">Simpan Pembayaran</button>
            </div>
        </form>
    </div>
</div>
@endsection
