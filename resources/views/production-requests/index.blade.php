@extends('layouts.app', ['title' => 'Permintaan Produksi'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Permintaan Produksi</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Status antrean pemicu produksi dari Sales Order.</p>
        </div>
        <div>
            @if(auth()->user()?->hasRole('sales'))
                <a href="{{ url('/production/batches/1') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition inline-block">
                    + Tambah Antrean
                </a>
            @else
                <span class="px-4 py-1.5 rounded-full text-xs font-bold badge-dark inline-block">
                    Mode Lihat Saja
                </span>
            @endif
        </div>
    </div>

    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">No. Permintaan</th>
                        <th class="px-6 py-4">Sales Order</th>
                        <th class="px-6 py-4">Pemesan / Pelanggan</th>
                        <th class="px-6 py-4">Detail Produk</th>
                        <th class="px-6 py-4">Tanggal Permintaan</th>
                        <th class="px-6 py-4">Status Produksi</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($productionRequests as $pr)
                        <tr class="hover:bg-white/60 transition align-top">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('production-requests.show', $pr) }}" class="hover:underline">{{ $pr->request_number }}</a>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                <a href="{{ route('sales-orders.show', $pr->salesOrder) }}" class="hover:underline">{{ $pr->salesOrder->order_number }}</a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900 block">{{ $pr->salesOrder->customer->name }}</span>
                                @php
                                    $inv = $pr->salesOrder->invoices->first();
                                    $payStatus = $inv ? strtoupper($inv->status->value) : 'UNBILLED';
                                @endphp
                                <span class="inline-block mt-1 text-[10px] font-mono font-bold px-2 py-0.5 rounded-full badge-dark">
                                    Status Bayar: {{ $payStatus }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
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
                            <td class="px-6 py-4 text-slate-600 font-medium text-xs">{{ $pr->requested_date->format('d M Y, H:i') }}</td>
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
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada permintaan produksi.</td></tr>
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
