@extends('layouts.app', ['title' => 'Sales Orders'])

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Daftar Sales Orders</h2>
                <p class="text-xs text-slate-500 font-medium mt-1">Pantau pesanan penjualan aktif, pemicu produksi,
                    penagihan, serta ekspor/impor Excel.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 self-start">
                <a href="{{ route('sales-orders.export.excel') }}"
                    class="px-4 py-2 rounded-full btn-subtle text-xs font-bold transition">
                    Export Excel
                </a>
                <button type="button" onclick="openImportSoModal()"
                    class="px-4 py-2 rounded-full btn-subtle text-xs font-bold transition">
                    Import Excel / CSV
                </button>
                <a href="{{ route('sales-orders.create') }}"
                    class="px-5 py-2 rounded-full btn-dark text-xs font-bold transition">
                    + Buat Sales Order Baru
                </a>
            </div>
        </div>

        <div class="apple-glass-card rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                        <tr>
                            <th class="px-6 py-4">No. Sales Order</th>
                            <th class="px-6 py-4">Pelanggan</th>
                            <th class="px-6 py-4">Total Nilai</th>
                            <th class="px-6 py-4">Status SO</th>
                            <th class="px-6 py-4">Status Produksi</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-900/10">
                        @forelse($salesOrders as $so)
                            <tr class="hover:bg-white/60 transition">
                                <td class="px-6 py-4 font-mono text-xs font-bold text-slate-900">
                                    <a href="{{ route('sales-orders.show', $so) }}"
                                        class="hover:underline">{{ $so->order_number }}</a>
                                </td>
                                <td class="px-6 py-4 font-bold text-slate-900">{{ $so->customer->name }}</td>
                                <td class="px-6 py-4 font-extrabold text-slate-900">Rp
                                    {{ number_format($so->total_amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                                        {{ strtoupper($so->status->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($so->productionRequest)
                                        <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">
                                            {{ strtoupper($so->productionRequest->status->value) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('sales-orders.show', $so) }}"
                                        class="px-4 py-1.5 rounded-full btn-subtle text-xs font-bold">Detail & Alur Status</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada Sales Order.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($salesOrders->hasPages())
                <div class="p-4 border-t border-slate-900/10">
                    {{ $salesOrders->links() }}
                </div>
            @endif
        </div>
    </div>

    <script>
        function openImportSoModal() {
            document.getElementById('importSoModal').classList.remove('hidden');
        }

        function closeImportSoModal() {
            document.getElementById('importSoModal').classList.add('hidden');
        }
    </script>
@endsection

@push('modals')
    <!-- Import Sales Order Liquid Glass Modal (Bright & Luminous) -->
    <div id="importSoModal"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md hidden p-4 overflow-y-auto">
        <div
            class="bg-white/95 backdrop-blur-3xl max-w-lg w-full p-6 md:p-8 rounded-3xl border border-white/90 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] ring-1 ring-slate-900/5 relative my-8">
            <!-- Header -->
            <div class="flex items-start justify-between pb-4 mb-5 border-b border-slate-100">
                <div>
                    <!--<span class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 block">Automated Order Creation</span>
                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Import Sales Order dari Excel / CSV</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Unggah spreadsheet untuk membuat pesanan, pemicu produksi, & faktur otomatis.</p>-->

                    <span class="text-[10px] font-extrabold uppercase tracking-widest block">
                        <span class="text-red-500">BETA Experimental</span>
                        <span class="text-slate-400"> Data Integration</span>

                        <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
                            <span class="text-red-500">BETA Experimental</span> Import Faktur Invoice dari Excel / CSV
                        </h3>

                        <p class=" text-slate-500 font-medium mt-0.5">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest block">

                                <span class="text-slate-400"> Data Integration melalui manual terlebih dahulu</span>
                        </p>
                </div>
                <button type="button" onclick="closeImportSoModal()"
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs font-bold transition">
                    ✕
                </button>
            </div>

            <form action="{{ route('sales-orders.import.excel') }}" method="POST" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih File
                        Spreadsheet (.csv, .xlsx, .xls) *</label>
                    <div class="p-3 rounded-2xl bg-white border border-slate-200 shadow-sm">
                        <input type="file" name="file" required accept=".csv,.xlsx,.xls,.txt"
                            class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">Mendukung format .csv, .xlsx, .xls hingga
                        10MB</span>
                </div>

                <!-- Bright Info & Template Card -->
                <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900">Format Kolom Pesanan</span>
                        <span class="text-[10px] font-bold text-slate-500 font-mono">CSV / XLSX</span>
                    </div>
                    <p class="text-xs text-slate-600 font-medium leading-relaxed">
                        Struktur kolom: <code
                            class="text-[11px] font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-800">nama_pelanggan, nama_produk, jumlah, satuan, harga_satuan, penerima_pesanan_pic</code>
                    </p>
                    <div class="pt-1">
                        <a href="{{ route('sales-orders.template.excel') }}"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-900 bg-white hover:bg-slate-100 px-3.5 py-1.5 rounded-xl border border-slate-200 shadow-sm transition">
                            <span>↓ Unduh Template Excel / CSV</span>
                        </a>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeImportSoModal()"
                        class="px-5 py-2.5 rounded-full btn-subtle text-xs font-bold transition">Batal</button>
                    <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-xs font-bold transition">Mulai
                        Import</button>
                </div>
            </form>
        </div>
    </div>
@endpush