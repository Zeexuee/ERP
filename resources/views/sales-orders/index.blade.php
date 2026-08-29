@extends('layouts.app', ['title' => 'Sales Orders'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Sales Orders</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Pantau pesanan penjualan aktif, pemicu produksi, dan penagihan.</p>
        </div>
        <a href="{{ route('sales-orders.create') }}" class="px-5 py-2.5 rounded-full btn-dark text-sm self-start">
            Buat Sales Order Baru
        </a>
    </div>

    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">No. Sales Order</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Total Nilai</th>
                        <th class="px-6 py-4">Status SO</th>
                        <th class="px-6 py-4">Status Produksi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($salesOrders as $so)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('sales-orders.show', $so) }}" class="hover:underline">{{ $so->order_number }}</a>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $so->customer->name }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                                    {{ strtoupper($so->status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($so->productionRequest)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                                        {{ strtoupper($so->productionRequest->status->value) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('sales-orders.show', $so) }}" class="px-4 py-1.5 rounded-full btn-subtle text-xs font-bold">Detail & Alur Status</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada Sales Order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salesOrders->hasPages())
            <div class="p-4 border-t border-slate-900/10">
                {{ $salesOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
