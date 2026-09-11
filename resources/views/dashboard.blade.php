@extends('layouts.app', ['title' => 'Dashboard Sales Overview'])

@section('content')
<div class="space-y-6">
    <!-- Performance Metrics Cards (Dark Neutral Text & Liquid Glass) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Pelanggan</span>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ number_format($stats['total_customers']) }}</span>
                <span class="text-xs text-slate-500 block font-medium mt-0.5">Pelanggan Aktif</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Sales Order Berjalan</span>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ number_format($stats['processing_orders']) }}</span>
                <span class="text-xs text-slate-500 block font-medium mt-0.5">Confirmed & Processing</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Faktur Unpaid</span>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ number_format($stats['unpaid_invoices']) }}</span>
                <span class="text-xs text-slate-500 block font-medium mt-0.5">Unpaid / Partial</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Total Pembayaran</span>
            <div class="mt-2">
                <span class="text-xl font-extrabold text-slate-900">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</span>
                <span class="text-xs text-slate-500 block font-medium mt-0.5">Kas Pembayaran Masuk</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Produksi Pending</span>
            <div class="mt-2">
                <span class="text-2xl font-extrabold text-slate-900">{{ number_format($stats['pending_production']) }}</span>
                <span class="text-xs text-slate-500 block font-medium mt-0.5">Status Pending</span>
            </div>
        </div>
    </div>

    <!-- Grid Row 1: Sales Orders & Riwayat Pembayaran Masuk -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Sales Orders -->
        <div class="apple-glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
                <h3 class="text-base font-bold text-slate-900">Sales Orders Terbaru</h3>
                <a href="{{ route('sales-orders.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">Lihat Semua →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                        <tr>
                            <th class="px-4 py-2.5 rounded-l-lg">No. SO</th>
                            <th class="px-4 py-2.5">Pelanggan</th>
                            <th class="px-4 py-2.5">Total</th>
                            <th class="px-4 py-2.5 rounded-r-lg">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10">
                        @forelse($recentOrders as $so)
                            <tr class="hover:bg-white/60 transition">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-slate-900">
                                    <a href="{{ route('sales-orders.show', $so) }}" class="hover:underline">{{ $so->order_number }}</a>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $so->customer->name }}</td>
                                <td class="px-4 py-3 font-extrabold text-slate-900">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold badge-dark">
                                        {{ strtoupper($so->status->value) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-slate-400 font-medium">Belum ada Sales Order.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Payments History -->
        <div class="apple-glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
                <h3 class="text-base font-bold text-slate-900">Riwayat Pembayaran Terbaru</h3>
                <a href="{{ route('invoices.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">Lihat Faktur →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                        <tr>
                            <th class="px-4 py-2.5 rounded-l-lg">No. Pembayaran</th>
                            <th class="px-4 py-2.5">Faktur</th>
                            <th class="px-4 py-2.5">Metode</th>
                            <th class="px-4 py-2.5 rounded-r-lg text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10">
                        @forelse($recentPayments as $p)
                            <tr class="hover:bg-white/60 transition">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-slate-900">{{ $p->payment_number }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-800">
                                    <a href="{{ route('invoices.show', $p->invoice) }}" class="hover:underline">{{ $p->invoice->invoice_number }}</a>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-700">{{ $p->payment_method }}</td>
                                <td class="px-4 py-3 font-extrabold text-slate-900 text-right">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-4 text-center text-slate-400 font-medium">Belum ada transaksi pembayaran.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grid Row 2: Status Permintaan Produksi -->
    <div class="apple-glass-card rounded-2xl p-6">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-900/10">
            <div>
                <h3 class="text-base font-bold text-slate-900">Status Permintaan Produksi</h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Pantau antrean dan detail produk yang sedang diproduksi.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ url('/production/batches/1') }}" class="px-3 py-1.5 rounded-xl btn-dark text-xs font-semibold shadow-xs">
                    + Tambah Antrean
                </a>
                <a href="{{ route('production-requests.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-900">Lihat Semua →</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="text-xs uppercase bg-white/50 text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-4 py-2.5 rounded-l-lg">No. Produksi</th>
                        <th class="px-4 py-2.5">Sales Order</th>
                        <th class="px-4 py-2.5">Pemesan / Pelanggan</th>
                        <th class="px-4 py-2.5">Detail Produk</th>  
                        <th class="px-4 py-2.5 rounded-r-lg text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($recentProductionRequests as $pr)
                        <tr class="hover:bg-white/60 transition align-top">
                            <td class="px-4 py-3 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('production-requests.show', $pr) }}" class="hover:underline">{{ $pr->request_number }}</a>
                                <span class="block text-[10px] text-slate-400 font-normal font-sans mt-0.5">{{ $pr->created_at->format('d M Y, H:i') }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-800">
                                <a href="{{ route('sales-orders.show', $pr->salesOrder) }}" class="hover:underline">{{ $pr->salesOrder->order_number }}</a>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-bold text-slate-900 block text-xs">{{ $pr->salesOrder->customer->name }}</span>
                                @php
                                    $inv = $pr->salesOrder->invoices->first();
                                    $payStatus = $inv ? strtoupper($inv->status->value) : 'UNBILLED';
                                @endphp
                                <span class="inline-block mt-1 text-[10px] font-mono font-bold px-2 py-0.5 rounded-full badge-dark">
                                    Status Bayar: {{ $payStatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="space-y-1">
                                    @forelse($pr->salesOrder->items as $item)
                                        <div class="text-xs text-slate-800 flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900">• {{ $item->product->name }}</span>
                                            <span class="font-mono text-slate-600 font-medium">({{ $item->quantity }} {{ $item->unit ?? 'kg' }})</span>
                                        </div>
                                    @empty
                                        <span class="text-xs text-slate-400 italic">Tidak ada item produk.</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold badge-dark">
                                    {{ strtoupper($pr->status->value) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400 font-medium">Belum ada antrean permintaan produksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
