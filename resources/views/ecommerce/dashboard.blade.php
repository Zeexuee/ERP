@extends('layouts.app', ['title' => 'E-Commerce'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900">E-Commerce</h2>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Transaksi</span>
            <div class="text-2xl font-bold font-mono text-slate-900 mt-1">
                Rp {{ number_format($totalSales, 0, ',', '.') }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pesanan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $orderCount }}</div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Gateway</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                Online
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Pesanan Masuk</h3>
            <a href="{{ route('sales-orders.index') }}" class="text-xs font-semibold text-slate-700 hover:underline">
                Semua
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">No. Order</th>
                        <th class="py-2.5 px-3">Pelanggan</th>
                        <th class="py-2.5 px-3">Item</th>
                        <th class="py-2.5 px-3">Total</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse($orders as $o)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">{{ $o->order_number }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-800">{{ $o->customer->name ?? '-' }}</td>
                            <td class="py-3 px-3">
                                @foreach($o->items as $item)
                                    <div class="text-slate-700">{{ $item->product->name ?? 'Barang' }} ({{ $item->quantity }} {{ $item->unit ?? 'unit' }})</div>
                                @endforeach
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-900">Rp {{ number_format($o->total_amount, 0, ',', '.') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800">
                                    {{ $o->status->value }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                Belum ada pesanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
