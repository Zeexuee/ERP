@extends('layouts.app', ['title' => 'Permintaan Produksi'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Permintaan Produksi</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Status pemicu produksi dari Sales Order. Akses Read-Only.</p>
        </div>
        <span class="px-4 py-1.5 rounded-full text-xs font-bold badge-dark self-start">
            Read-Only Access (Sales Scope)
        </span>
    </div>

    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">No. Permintaan</th>
                        <th class="px-6 py-4">Sales Order</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4">Tanggal Permintaan</th>
                        <th class="px-6 py-4">Status Produksi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($productionRequests as $pr)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('production-requests.show', $pr) }}" class="hover:underline">{{ $pr->request_number }}</a>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('sales-orders.show', $pr->salesOrder) }}" class="hover:underline">{{ $pr->salesOrder->order_number }}</a>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $pr->salesOrder->customer->name }}</td>
                            <td class="px-6 py-4 text-slate-600 font-medium">{{ $pr->requested_date->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                                    {{ strtoupper($pr->status->value) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('production-requests.show', $pr) }}" class="px-4 py-1.5 rounded-full btn-subtle text-xs font-bold">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada permintaan produksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($productionRequests->hasPages())
            <div class="p-4 border-t border-slate-900/10">
                {{ $productionRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
