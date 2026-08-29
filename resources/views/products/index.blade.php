@extends('layouts.app', ['title' => 'Katalog Produk & Stok'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Produk & Stok</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Akses Read-Only pemantauan ketersediaan barang secara real-time.</p>
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
                        <th class="px-6 py-4">SKU Produk</th>
                        <th class="px-6 py-4">Nama Produk</th>
                        <th class="px-6 py-4">Harga Satuan</th>
                        <th class="px-6 py-4">Stok Tersedia</th>
                        <th class="px-6 py-4">Status Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($products as $p)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">{{ $p->sku }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $p->name }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900">{{ $p->stock_quantity }} unit</td>
                            <td class="px-6 py-4">
                                @if($p->stock_quantity > 20)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">Stok Cukup</span>
                                @elseif($p->stock_quantity > 0)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-80">Stok Menipis</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-50">Stok Habis</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada data produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-900/10">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
