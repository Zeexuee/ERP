@extends('layouts.app', ['title' => 'Mutasi Stok'])

@section('content')
<div class="space-y-6">

    <!-- Header & Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Mutasi Stok — {{ $item->name }}</h2>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stin.items.print-sku', $item) }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-sm transition">
                Print SKU
            </a>
            <a href="{{ route('stin.items.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition">
                Kembali ke Data Gudang
            </a>
        </div>
    </div>

    <!-- Ringkasan Info Barang -->
    <div class="apple-glass-card rounded-2xl p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div>
                <span class="block text-slate-500 font-medium mb-1">SKU</span>
                <span class="font-mono text-xs font-bold text-slate-900">{{ $item->sku }}</span>
            </div>
            <div>
                <span class="block text-slate-500 font-medium mb-1">Kategori</span>
                <span class="font-semibold text-slate-800">{{ $item->category ?? '-' }}</span>
            </div>
            <div>
                <span class="block text-slate-500 font-medium mb-1">Stok Saat Ini</span>
                <span class="font-bold text-slate-900 text-sm">{{ number_format($item->current_stock) }} {{ $item->unit ?? 'pcs' }}</span>
            </div>
            <div>
                <span class="block text-slate-500 font-medium mb-1">Harga HPP</span>
                <span class="font-semibold text-slate-800">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Mutasi Stok -->
    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
                        <th class="px-6 py-4">Jenis</th>
                        <th class="px-6 py-4">Jumlah</th>
                        <th class="px-6 py-4">Sebelum</th>
                        <th class="px-6 py-4">Sesudah</th>
                        <th class="px-6 py-4">PIC</th>
                        <th class="px-6 py-4">Catatan</th>
                        <th class="px-6 py-4 text-right">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($movements as $m)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 text-xs text-slate-700 whitespace-nowrap">
                                {{ ($m->movement_date ?? $m->created_at)?->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-900">
                                @if($m->type === 'in')
                                    Masuk
                                @elseif($m->type === 'out')
                                    Keluar
                                @elseif($m->type === 'transfer')
                                    Transfer
                                @elseif($m->type === 'adjustment')
                                    Penyesuaian
                                @else
                                    {{ ucfirst($m->type) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-xs text-slate-900">
                                @if($m->type === 'in')
                                    +{{ number_format($m->quantity) }} {{ $item->unit ?? 'pcs' }}
                                @elseif($m->type === 'out')
                                    -{{ number_format($m->quantity) }} {{ $item->unit ?? 'pcs' }}
                                @else
                                    {{ number_format($m->quantity) }} {{ $item->unit ?? 'pcs' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700">
                                {{ number_format($m->stock_before) }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                {{ number_format($m->stock_after) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-800">
                                {{ $m->pic_name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-600 max-w-xs truncate">
                                {{ $m->notes ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($m->signature_path)
                                    <img src="{{ asset('storage/' . ltrim(str_replace('/storage/', '', $m->signature_path), '/')) }}" alt="Tanda Tangan" class="h-8 inline-block border border-slate-300 rounded bg-white p-0.5 object-contain">
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 text-sm">
                                Belum ada riwayat mutasi stok untuk barang ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-slate-900/10 bg-white/30">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
