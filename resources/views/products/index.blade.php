@extends('layouts.app', ['title' => 'Katalog Produk & Stok'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Produk & Stok</h2>
            
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
                        <th class="px-6 py-4">Branch Produksi</th>
                        <th class="px-6 py-4">Harga Satuan</th>
                        <th class="px-6 py-4">Total Stok</th>
                        <th class="px-6 py-4">Status Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($products as $p)
                        @php
                            $totalStock = $p->total_stock;
                        @endphp
                        <tr class="hover:bg-white/60 transition group">
                            <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">{{ $p->sku }}</td>
                            <td class="px-6 py-4 font-bold text-slate-900">{{ $p->name }}</td>
                            <td class="px-6 py-4">
                                <button type="button" 
                                        onclick="toggleProductBranchRow({{ $p->id }})" 
                                        class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full btn-subtle text-xs font-bold transition">
                                    <span>Lihat Branch ({{ $p->branches->count() }})</span>
                                    <svg id="chevron-prd-{{ $p->id }}" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </td>
                            <td class="px-6 py-4 font-bold text-slate-900">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-900">
                                {{ $totalStock }} {{ $p->unit ?? 'unit' }}
                            </td>
                            <td class="px-6 py-4">
                                @if($totalStock > 20)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">Stok Cukup</span>
                                @elseif($totalStock > 0)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-80">Stok Menipis</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-50">Stok Habis</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Expandable Branch Breakdown & Production Notes Row -->
                        <tr id="branch-row-{{ $p->id }}" class="hidden bg-slate-900/[0.02] border-b border-slate-900/10">
                            <td colspan="6" class="px-6 py-5">
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between border-b border-slate-900/10 pb-2">
                                        <div>
                                            <span class="text-xs font-extrabold uppercase tracking-wider text-slate-900">Rincian Stok & Catatan Tiap Branch Produksi</span>
                                            <span class="text-xs text-slate-500 ml-2 font-medium">• Total: {{ $totalStock }} {{ $p->unit ?? 'unit' }}</span>
                                        </div>
                                        <span class="text-[11px] font-mono font-bold text-slate-600">SKU: {{ $p->sku }}</span>
                                    </div>

                                    @if($p->branches->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach($p->branches as $branch)
                                                <div class="p-4 rounded-2xl bg-white/80 border border-slate-900/10 shadow-sm space-y-2">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div>
                                                            <span class="font-mono font-extrabold text-sm text-slate-900 block tracking-wider">{{ $branch->branch_code ?: $branch->branch_name }}</span>
                                                        </div>
                                                        <span class="px-2.5 py-1 rounded-full text-xs font-mono font-extrabold badge-dark shrink-0">
                                                            {{ $branch->quantity }} {{ $p->unit ?? 'unit' }}
                                                        </span>
                                                    </div>

                                                    <div class="pt-2 border-t border-slate-900/5">
                                                        <span class="text-[10px] font-bold uppercase text-slate-400 block mb-0.5">Catatan Produksi:</span>
                                                        <p class="text-xs text-slate-700 font-medium leading-relaxed">
                                                            {{ $branch->specification_notes ?? 'Tidak ada catatan khusus dari tim produksi.' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="p-4 rounded-2xl bg-white/80 border border-slate-900/10 text-xs text-slate-600">
                                            <span class="font-bold text-slate-900">Gudang Distribusi Utama:</span>
                                            <span class="ml-1">{{ $p->stock_quantity }} {{ $p->unit ?? 'unit' }}.</span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada data produk.</td></tr>
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

<script>
    function toggleProductBranchRow(productId) {
        const row = document.getElementById(`branch-row-${productId}`);
        const chevron = document.getElementById(`chevron-prd-${productId}`);
        
        if (row) {
            const isHidden = row.classList.contains('hidden');
            row.classList.toggle('hidden', !isHidden);
            
            if (chevron) {
                chevron.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
    }
</script>
@endsection
