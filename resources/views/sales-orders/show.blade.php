@extends('layouts.app', ['title' => 'Detail Sales Order'])

@section('content')
<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-extrabold font-mono text-slate-900">{{ $salesOrder->order_number }}</h2>
                <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                    STATUS SO: {{ strtoupper($salesOrder->status->value) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">Dibuat: {{ $salesOrder->created_at->format('d M Y, H:i') }}</p>
        </div>

        <!-- Transitions & Actions Buttons (Dark Neutral Buttons) -->
        <div class="flex flex-wrap items-center gap-3">
            @if($salesOrder->status->value === 'draft')
                <form action="{{ route('sales-orders.update-status', $salesOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="px-5 py-2 rounded-full btn-dark text-xs">
                        Konfirmasi Order (CONFIRMED)
                    </button>
                </form>
            @elseif($salesOrder->status->value === 'confirmed')
                <form action="{{ route('sales-orders.update-status', $salesOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="processing">
                    <button type="submit" class="px-5 py-2 rounded-full btn-dark text-xs">
                        Proses Order (Micu Production Request)
                    </button>
                </form>
                <form action="{{ route('sales-orders.update-status', $salesOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="ready">
                    <button type="submit" class="px-5 py-2 rounded-full btn-subtle text-xs">
                        Stok Ready (READY)
                    </button>
                </form>
            @elseif($salesOrder->status->value === 'processing')
                <form action="{{ route('sales-orders.update-status', $salesOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="ready">
                    <button type="submit" class="px-5 py-2 rounded-full btn-dark text-xs">
                        Produksi Selesai (READY)
                    </button>
                </form>
            @elseif($salesOrder->status->value === 'ready')
                <form action="{{ route('sales-orders.update-status', $salesOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="px-5 py-2 rounded-full btn-dark text-xs">
                        Selesaikan Order (COMPLETED)
                    </button>
                </form>
            @endif

            @if(!in_array($salesOrder->status->value, ['draft', 'cancelled']))
                <button type="button" onclick="document.getElementById('invoiceModal').classList.remove('hidden')" class="px-6 py-2.5 rounded-full btn-dark text-xs font-bold">
                    Terbitkan Invoice
                </button>
            @endif

            <a href="{{ route('sales-orders.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Pelanggan</span>
            <p class="text-base font-bold text-slate-900">{{ $salesOrder->customer->name }}</p>
            <p class="text-xs text-slate-500 mt-0.5 font-medium">{{ $salesOrder->customer->email }}</p>
        </div>

        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Status Permintaan Produksi</span>
            @if($salesOrder->productionRequest)
                <a href="{{ route('production-requests.show', $salesOrder->productionRequest) }}" class="inline-block px-3 py-1 rounded-full badge-dark text-xs font-bold hover:underline mt-1">
                    {{ $salesOrder->productionRequest->request_number }} ({{ strtoupper($salesOrder->productionRequest->status->value) }})
                </a>
            @else
                <span class="text-xs text-slate-400 font-medium mt-1 block">Belum dipicu / stok cukup</span>
            @endif
        </div>

        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Faktur Terkait</span>
            @forelse($salesOrder->invoices as $inv)
                <a href="{{ route('invoices.show', $inv) }}" class="inline-block px-3 py-1 rounded-full badge-dark text-xs font-mono font-bold hover:underline mb-1 mr-1">
                    {{ $inv->invoice_number }} ({{ strtoupper($inv->status->value) }})
                </a>
            @empty
                <span class="text-xs text-slate-400 font-medium mt-1 block">Belum ada faktur.</span>
            @endforelse
        </div>
    </div>

    <!-- Items Table -->
    <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Detail Item Sales Order</h3>
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
                    @foreach($salesOrder->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $item->product->name }}
                                <span class="block text-xs font-mono text-slate-500 font-normal">{{ $item->product->sku }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-slate-900">{{ $item->quantity }} unit</td>
                            <td class="px-4 py-3 text-right text-slate-800 font-semibold">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-slate-900/10 font-bold">
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-right text-slate-500 uppercase text-xs">Total Sales Order:</td>
                        <td class="px-4 py-4 text-right text-xl text-slate-900">Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Glassmorphism Terbitkan Invoice (Liquid Glass Dark Neutral Buttons) -->
<div id="invoiceModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-md hidden">
    <div class="apple-glass-panel max-w-md w-full p-8 rounded-3xl border border-white shadow-2xl relative">
        <h3 class="text-xl font-extrabold text-slate-900 mb-1">Terbitkan Invoice Baru</h3>
        <p class="text-xs text-slate-500 mb-5 font-medium">Invoice dibuat berdasarkan total Sales Order {{ $salesOrder->order_number }}.</p>

        <form action="{{ route('sales-orders.generate-invoice', $salesOrder) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Tanggal Jatuh Tempo (Due Date) *</label>
                <input type="date" name="due_date" value="{{ \Carbon\Carbon::now()->addDays(30)->toDateString() }}" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-900/10">
                <button type="button" onclick="document.getElementById('invoiceModal').classList.add('hidden')" class="px-5 py-2.5 rounded-full btn-subtle text-sm">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-sm">Terbitkan Invoice</button>
            </div>
        </form>
    </div>
</div>
@endsection
