@extends('layouts.app', ['title' => 'Produksi'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900">Produksi</h2>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Menunggu</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalPending }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dikerjakan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalInProduction }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Selesai</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalFinished }}
            </div>
        </div>
    </div>

    <!-- Antrean Kerja -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Antrean Produksi</h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                {{ $requests->total() }} Total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">No. Permintaan</th>
                        <th class="py-2.5 px-3">Ref. Order</th>
                        <th class="py-2.5 px-3">Pelanggan</th>
                        <th class="py-2.5 px-3">Barang & Jumlah</th>
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($requests as $item)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">
                                #{{ $item->request_number }}
                            </td>
                            <td class="py-3 px-3">
                                @if($item->salesOrder)
                                    <a href="{{ route('sales-orders.show', $item->salesOrder) }}" class="font-mono font-semibold text-slate-700 hover:underline">
                                        {{ $item->salesOrder->order_number }}
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                {{ $item->salesOrder->customer->name ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <ul class="space-y-0.5">
                                    @foreach($item->salesOrder->items ?? [] as $soItem)
                                        <li class="text-slate-700">
                                            {{ $soItem->product->name ?? 'Produk' }}
                                            <span class="font-semibold text-slate-900">({{ $soItem->quantity }} {{ $soItem->unit ?? 'unit' }})</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $item->requested_date ? \Illuminate\Support\Carbon::parse($item->requested_date)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="py-3 px-3">
                                @if($item->status->value === 'pending')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-700">
                                        Menunggu
                                    </span>
                                @elseif($item->status->value === 'in_production')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white">
                                        Dikerjakan
                                    </span>
                                @elseif($item->status->value === 'finished')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border border-slate-900 text-slate-900">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <form action="{{ route('production.update-status', $item) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    
                                    @if($item->status->value === 'pending')
                                        <input type="hidden" name="status" value="in_production">
                                        <button type="submit" class="px-3 py-1 rounded-xl btn-dark text-xs font-semibold">
                                            Proses
                                        </button>
                                    @elseif($item->status->value === 'in_production')
                                        <input type="hidden" name="status" value="finished">
                                        <button type="submit" class="px-3 py-1 rounded-xl bg-white border border-slate-900 text-slate-900 hover:bg-slate-900 hover:text-white transition text-xs font-semibold">
                                            Selesai
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">
                                            Selesai
                                        </span>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                Belum ada antrean.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-900/10">
                {{ $requests->links() }}
            </div>
        @endif
    </div>

    <!-- Stok Produk -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Stok Produk</h3>
            <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-700 hover:underline">
                Katalog
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($products as $prod)
                <div class="p-4 rounded-2xl bg-white/70 border border-slate-900/10 space-y-1.5">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-mono text-[10px] text-slate-400 uppercase">{{ $prod->sku }}</span>
                            <h4 class="font-bold text-sm text-slate-900">{{ $prod->name }}</h4>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-900 text-white">
                            {{ $prod->stock_quantity }} {{ $prod->unit ?? 'unit' }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-slate-900/5 text-[11px] text-slate-600">
                        @if($prod->branches->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-1">
                                @foreach($prod->branches as $b)
                                    <span class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-800 font-mono text-[9px] font-semibold border border-slate-200">
                                        {{ $b->branch_code }}: {{ $b->stock_quantity }} {{ $prod->unit ?? 'unit' }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
