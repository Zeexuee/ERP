@extends('layouts.app', ['title' => 'Data Gudang'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Title & Count -->
        <div class="flex-shrink-0">
            <h2 class="text-xl font-bold text-slate-900">Data Gudang</h2>
            <div class="text-xs text-slate-500 font-medium mt-0.5">
                {{ number_format($items->total()) }} Barang Terdaftar
            </div>
        </div>

        <!-- Centered Search Bar with Icon Button -->
        <div class="flex-1 max-w-lg md:mx-4 w-full">
            <form method="GET" action="{{ route('stin.items.index') }}" class="flex items-center gap-2 w-full">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nama barang, SKU, barcode, atau kategori..." 
                    class="apple-input flex-1 rounded-2xl px-4 py-2.5 text-sm font-medium border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 shadow-xs focus:border-slate-900"
                >
                <button type="submit" title="Cari" aria-label="Cari" class="p-2.5 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white flex items-center justify-center shadow-sm transition aspect-square flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
                @if(request('search'))
                    <a href="{{ route('stin.items.index') }}" class="px-3.5 py-2.5 rounded-2xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold shadow-sm transition whitespace-nowrap flex-shrink-0">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Actions: Scan Barcode & Input Barang -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('stin.scanner.index') }}" class="px-4 py-2.5 rounded-2xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-sm transition whitespace-nowrap block text-center">
                Scan Barcode
            </a>
            <a href="{{ route('stin.items.create') }}" class="px-5 py-2.5 rounded-2xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-sm whitespace-nowrap block text-center">
                Input Barang
            </a>
        </div>
    </div>

    @if(request('search'))
        <div class="text-xs text-slate-600 font-medium">
            Hasil pencarian untuk <span class="font-bold text-slate-900">"{{ request('search') }}"</span> — ditemukan {{ $items->total() }} barang
        </div>
    @endif

    @if(session('success'))
        <div class="px-4 py-3 rounded-xl border border-slate-300 bg-white text-xs font-semibold text-slate-900">
            {{ session('success') }}
        </div>
    @endif

    <!-- Table Container -->
    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">Nama Barang</th>
                        <th class="px-6 py-4">Kategori Barang</th>
                        <th class="px-6 py-4">Jumlah Barang</th>
                        <th class="px-6 py-4">Harga HPP</th>
                        <th class="px-6 py-4">SKU</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($items as $item)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $item->name }}
                            </td>
                            <td class="px-6 py-4 text-slate-700">
                                {{ $item->category ?? '-' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                {{ number_format($item->current_stock) }} {{ $item->unit ?? 'pcs' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-800">
                                {{ $item->sku }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <a href="{{ route('stin.items.print-sku', $item) }}" class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-800 hover:bg-slate-100 transition shadow-sm">
                                        Print SKU
                                    </a>
                                    <a href="{{ route('stin.items.edit', $item) }}" class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-800 hover:bg-slate-100 transition shadow-sm">
                                        Edit
                                    </a>
                                    <a href="{{ route('stin.items.mutations', $item) }}" class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-800 hover:bg-slate-100 transition shadow-sm">
                                        Mutasi Stok
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                @if(request('search'))
                                    Tidak ada data barang yang sesuai dengan pencarian "{{ request('search') }}".
                                @else
                                    Tidak ada data barang
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-slate-900/10 bg-white/30">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
