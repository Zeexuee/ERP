@extends('layouts.app', ['title' => 'STIN'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900">STIN</h2>
    </div>

    <!-- Security Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelanggan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $metrics['total_customers'] }}</div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pesanan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $metrics['total_sales_orders'] }}</div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Permintaan Produksi</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $metrics['total_production_requests'] }}</div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Produk</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $metrics['total_products'] }}</div>
        </div>
    </div>

    <!-- Audit Log -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Audit Transaksi</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">No. Order</th>
                        <th class="py-2.5 px-3">Pelanggan</th>
                        <th class="py-2.5 px-3">PIC</th>
                        <th class="py-2.5 px-3">Tanda Tangan</th>
                        <th class="py-2.5 px-3">Total</th>
                        <th class="py-2.5 px-3 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse($recentAudits as $audit)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">
                                {{ $audit->order_number }}
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                {{ $audit->customer->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3 text-slate-900">
                                {{ $audit->pic_name ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                @if($audit->pic_signature)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white">
                                        Ada
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-900">
                                Rp {{ number_format($audit->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800">
                                    Verified
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-slate-400">
                                Belum ada catatan audit.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
