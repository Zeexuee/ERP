@extends('layouts.app', ['title' => 'Faktur (Invoices)'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Faktur (Invoices)</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Pantau penagihan dan pencatatan pembayaran faktur pelanggan.</p>
        </div>
    </div>

    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">No. Invoice</th>
                        <th class="px-6 py-4">Sales Order</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Jatuh Tempo</th>
                        <th class="px-6 py-4">Total Tagihan</th>
                        <th class="px-6 py-4">Status Bayar</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('invoices.show', $inv) }}" class="hover:underline">{{ $inv->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('sales-orders.show', $inv->salesOrder) }}" class="hover:underline">{{ $inv->salesOrder->order_number }}</a>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $inv->salesOrder->customer->name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium">{{ $inv->due_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                                    {{ strtoupper($inv->status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('invoices.show', $inv) }}" class="px-4 py-1.5 rounded-full btn-subtle text-xs font-bold">Detail & Bayar</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada faktur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($invoices->hasPages())
            <div class="p-4 border-t border-slate-900/10">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
