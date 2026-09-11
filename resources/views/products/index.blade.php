@extends('layouts.app', ['title' => 'Katalog Produk & Stok'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Produk & Stok</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Manajemen katalog barang jadi, harga satuan, dan pemutakhiran stok fisik produk.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()?->hasRole('production'))
                <button type="button" onclick="openCreateProductModal()" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                    <span>+ Tambah Jenis Produk</span>
                </button>
            @else
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Mode Katalog (Lihat Saja)
                </span>
            @endif
        </div>
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
                        @if(auth()->user()?->hasRole('production'))
                            <th class="px-6 py-4 text-center">Aksi</th>
                        @endif
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
                            @if(auth()->user()?->hasRole('production'))
                                <td class="px-6 py-4 text-center">
                                    <button 
                                        type="button" 
                                        onclick="openEditProductModal({{ $p->id }}, '{{ addslashes($p->sku) }}', '{{ addslashes($p->name) }}', {{ (float)$p->price }}, '{{ addslashes($p->unit ?? 'kg') }}', {{ (float)$totalStock }})"
                                        title="Edit Katalog Produk & Stok"
                                        class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-white text-slate-800 border border-slate-300 hover:bg-slate-100 shadow-sm transition"
                                    >
                                        Edit Produk & Stok
                                    </button>
                                </td>
                            @endif
                        </tr>

                        <!-- Expandable Branch Breakdown & Production Notes Row -->
                        <tr id="branch-row-{{ $p->id }}" class="hidden bg-slate-900/[0.02] border-b border-slate-900/10">
                            <td colspan="{{ auth()->user()?->hasRole('production') ? 7 : 6 }}" class="px-6 py-5">
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
                        <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada data produk.</td></tr>
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

@if(auth()->user()?->hasRole('production'))
<!-- Modal Tambah Produk Baru -->
<div id="createProductModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <h3 class="text-sm font-bold text-slate-900">Tambah Jenis Produk Baru</h3>
            <button type="button" onclick="closeCreateProductModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form action="{{ route('products.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf

            <div>
                <label for="create_sku" class="block text-xs font-semibold text-slate-800 mb-1">SKU Produk (Opsional)</label>
                <input type="text" id="create_sku" name="sku" class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono">
            </div>

            <div>
                <label for="create_name" class="block text-xs font-semibold text-slate-800 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" id="create_name" name="name" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-medium">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="create_price" class="block text-xs font-semibold text-slate-800 mb-1">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" step="1" id="create_price" name="price" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono">
                </div>
                <div>
                    <label for="create_unit" class="block text-xs font-semibold text-slate-800 mb-1">Satuan Unit <span class="text-red-500">*</span></label>
                    <input type="text" id="create_unit" name="unit" value="kg" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300">
                </div>
            </div>

            <div>
                <label for="create_stock_quantity" class="block text-xs font-semibold text-slate-800 mb-1">Stok Awal Produk <span class="text-red-500">*</span></label>
                <input type="number" step="1" min="0" id="create_stock_quantity" name="stock_quantity" value="0" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button type="button" onclick="closeCreateProductModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Katalog Produk & Stok -->
<div id="editProductModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
    <div class="apple-glass-panel bg-white/95 backdrop-blur-2xl border border-slate-200 rounded-3xl p-6 max-w-md w-full shadow-2xl relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-900/10">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Edit Katalog Produk & Stok</h3>
                <p id="editProductSubTitle" class="text-[11px] text-slate-600 font-mono mt-0.5"></p>
            </div>
            <button type="button" onclick="closeEditProductModal()" class="text-slate-400 hover:text-slate-700 text-lg font-bold">
                ✕
            </button>
        </div>

        <form id="editProductForm" method="POST" class="mt-4 space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_product_sku" class="block text-xs font-semibold text-slate-800 mb-1">SKU Produk <span class="text-red-500">*</span></label>
                <input type="text" id="edit_product_sku" name="sku" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono font-bold uppercase">
            </div>

            <div>
                <label for="edit_product_name" class="block text-xs font-semibold text-slate-800 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" id="edit_product_name" name="name" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-medium">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_product_price" class="block text-xs font-semibold text-slate-800 mb-1">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" step="1" id="edit_product_price" name="price" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono">
                </div>
                <div>
                    <label for="edit_product_unit" class="block text-xs font-semibold text-slate-800 mb-1">Satuan Unit <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_product_unit" name="unit" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300">
                </div>
            </div>

            <div>
                <label for="edit_product_stock" class="block text-xs font-semibold text-slate-800 mb-1">Total Stok Fisik <span class="text-red-500">*</span></label>
                <input type="number" step="1" min="0" id="edit_product_stock" name="stock_quantity" required class="w-full px-3 py-2 rounded-xl text-xs apple-input text-slate-900 border-slate-300 font-mono font-bold">
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-900/10">
                <button type="button" onclick="closeEditProductModal()" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endif

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

    function openCreateProductModal() {
        document.getElementById('createProductModal').classList.remove('hidden');
    }

    function closeCreateProductModal() {
        document.getElementById('createProductModal').classList.add('hidden');
    }

    function openEditProductModal(id, sku, name, price, unit, stock) {
        const modal = document.getElementById('editProductModal');
        const form = document.getElementById('editProductForm');
        const subTitle = document.getElementById('editProductSubTitle');

        form.action = `/products/${id}`;
        subTitle.innerText = `[${sku}] ${name}`;

        document.getElementById('edit_product_sku').value = sku;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_product_price').value = price;
        document.getElementById('edit_product_unit').value = unit;
        document.getElementById('edit_product_stock').value = stock;

        modal.classList.remove('hidden');
    }

    function closeEditProductModal() {
        document.getElementById('editProductModal').classList.add('hidden');
    }
</script>
@endsection
