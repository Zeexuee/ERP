@extends('layouts.app', ['title' => 'Buat Sales Order Baru'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Sales Order Baru</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Buat pesanan penjualan resmi untuk diproses.</p>
        </div>
        <a href="{{ route('sales-orders.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
    </div>

    <form action="{{ route('sales-orders.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">1. Informasi Utama</h3>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Pilih Pelanggan *</label>
                <select name="customer_id" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium bg-white">
                    <option value="">-- Pilih Pelanggan --</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
                @error('customer_id')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
            </div>
        </div>

        <!-- Dynamic Items Table -->
        <div class="apple-glass-card rounded-2xl p-6 border border-white space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">2. Item Produk Order</h3>
                <button type="button" id="addItemBtn" class="px-4 py-1.5 rounded-full btn-subtle text-xs font-bold">
                    Tambah Item
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold">
                        <tr>
                            <th class="px-3 py-2 w-1/2 rounded-l-lg">Produk</th>
                            <th class="px-3 py-2 w-24">Jumlah</th>
                            <th class="px-3 py-2 w-36">Harga Satuan (Rp)</th>
                            <th class="px-3 py-2 w-36">Subtotal (Rp)</th>
                            <th class="px-3 py-2 text-center w-12 rounded-r-lg">#</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10" id="itemsTableBody">
                        <tr class="item-row">
                            <td class="px-3 py-2.5">
                                <select name="items[0][product_id]" required class="product-select w-full rounded-xl apple-input px-3 py-2 text-sm font-medium bg-white">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }} (Stok: {{ $p->stock_quantity }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2.5">
                                <input type="number" name="items[0][quantity]" value="1" min="1" required class="qty-input w-full rounded-xl apple-input px-3 py-2 text-sm text-center font-bold">
                            </td>
                            <td class="px-3 py-2.5">
                                <input type="number" name="items[0][unit_price]" value="0" min="0" step="1000" class="price-input w-full rounded-xl apple-input px-3 py-2 text-sm font-medium">
                            </td>
                            <td class="px-3 py-2.5 font-extrabold text-slate-900 text-right subtotal-display">
                                Rp 0
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <button type="button" class="remove-row-btn text-slate-500 hover:text-slate-900 font-bold">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end pt-4 border-t border-slate-900/10">
                <div class="text-right">
                    <span class="text-xs text-slate-500 block uppercase font-bold tracking-wider">Total Sales Order</span>
                    <span class="text-2xl font-extrabold text-slate-900" id="grandTotalDisplay">Rp 0</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sales-orders.index') }}" class="px-5 py-2.5 rounded-full btn-subtle text-sm">Batal</a>
            <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-sm">Simpan Sales Order</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const productsData = @json($products);

    const tableBody = document.getElementById('itemsTableBody');
    const addItemBtn = document.getElementById('addItemBtn');

    function calculateTotals() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const subtotal = qty * price;
            grandTotal += subtotal;
            row.querySelector('.subtotal-display').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(subtotal);
        });
        document.getElementById('grandTotalDisplay').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(grandTotal);
    }

    function bindRowEvents(row) {
        const select = row.querySelector('.product-select');
        const priceInput = row.querySelector('.price-input');
        const qtyInput = row.querySelector('.qty-input');
        const removeBtn = row.querySelector('.remove-row-btn');

        select.addEventListener('change', function() {
            const selectedOpt = select.options[select.selectedIndex];
            const price = selectedOpt.getAttribute('data-price') || 0;
            priceInput.value = price;
            calculateTotals();
        });

        qtyInput.addEventListener('input', calculateTotals);
        priceInput.addEventListener('input', calculateTotals);

        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (document.querySelectorAll('.item-row').length > 1) {
                    row.remove();
                    calculateTotals();
                } else {
                    alert('Sales Order minimal harus memiliki 1 item produk.');
                }
            });
        }
    }

    bindRowEvents(document.querySelector('.item-row'));

    addItemBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.className = 'item-row';
        let optionsHtml = '<option value="">-- Pilih Produk --</option>';
        productsData.forEach(p => {
            optionsHtml += `<option value="${p.id}" data-price="${p.price}">${p.name} (Stok: ${p.stock_quantity})</option>`;
        });

        newRow.innerHTML = `
            <td class="px-3 py-2.5">
                <select name="items[${rowIndex}][product_id]" required class="product-select w-full rounded-xl apple-input px-3 py-2 text-sm font-medium bg-white">
                    ${optionsHtml}
                </select>
            </td>
            <td class="px-3 py-2.5">
                <input type="number" name="items[${rowIndex}][quantity]" value="1" min="1" required class="qty-input w-full rounded-xl apple-input px-3 py-2 text-sm text-center font-bold">
            </td>
            <td class="px-3 py-2.5">
                <input type="number" name="items[${rowIndex}][unit_price]" value="0" min="0" step="1000" class="price-input w-full rounded-xl apple-input px-3 py-2 text-sm font-medium">
            </td>
            <td class="px-3 py-2.5 font-extrabold text-slate-900 text-right subtotal-display">
                Rp 0
            </td>
            <td class="px-3 py-2.5 text-center">
                <button type="button" class="remove-row-btn text-slate-500 hover:text-slate-900 font-bold">Hapus</button>
            </td>
        `;

        tableBody.appendChild(newRow);
        bindRowEvents(newRow);
        rowIndex++;
    });
});
</script>
@endsection
