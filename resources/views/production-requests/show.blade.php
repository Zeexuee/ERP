@extends('layouts.app', ['title' => 'Detail Permintaan Produksi'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-extrabold font-mono text-slate-900">{{ $productionRequest->request_number }}</h2>
                <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                    STATUS: {{ strtoupper($productionRequest->status->value) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">Diminta: {{ $productionRequest->requested_date->format('d M Y, H:i') }}</p>
        </div>
        <a href="{{ route('production-requests.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
    </div>

    <!-- Details Card -->
    <div class="apple-glass-card rounded-2xl p-6 border border-white grid grid-cols-1 md:grid-cols-3 gap-5">
        <div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Sales Order Acuan</span>
            <a href="{{ route('sales-orders.show', $productionRequest->salesOrder) }}" class="text-base font-bold font-mono text-slate-900 hover:underline">
                {{ $productionRequest->salesOrder->order_number }}
            </a>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Pelanggan</span>
            <p class="text-base font-bold text-slate-900">{{ $productionRequest->salesOrder->customer->name }}</p>
        </div>
        <div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Tanggal Selesai</span>
            <p class="text-sm font-semibold text-slate-800">
                {{ $productionRequest->completed_date ? $productionRequest->completed_date->format('d M Y, H:i') : 'Belum Selesai' }}
            </p>
        </div>
    </div>

    <!-- Items to produce -->
    <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Barang yang Diproduksi</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-4 py-3">Produk</th>
                        <th class="px-4 py-3 text-center">Jumlah Diproduksi</th>
                        <th class="px-4 py-3 text-right">Stok Saat Ini</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @foreach($productionRequest->salesOrder->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-bold text-slate-900">
                                {{ $item->product->name }}
                                <span class="block text-xs font-mono text-slate-500 font-normal">{{ $item->product->sku }}</span>
                            </td>
                            <td class="px-4 py-3 text-center font-extrabold text-slate-900">{{ $item->quantity }} unit</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">{{ $item->product->stock_quantity }} unit</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
