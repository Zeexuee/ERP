@extends('layouts.app', ['title' => 'Daftar Pelanggan'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pelanggan</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Kelola data pelanggan, pantau riwayat belanja, dan impor/ekspor data ke Excel.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 self-start">
            <a href="{{ route('customers.export.excel') }}" class="px-4 py-2 rounded-full btn-subtle text-xs font-bold transition">
                Export Excel
            </a>
            <button type="button" onclick="openImportCustomerModal()" class="px-4 py-2 rounded-full btn-subtle text-xs font-bold transition">
                Import Excel / CSV
            </button>
            <a href="{{ route('customers.create') }}" class="px-5 py-2 rounded-full btn-dark text-xs font-bold transition">
                + Tambah Pelanggan Baru
            </a>
        </div>
    </div>

    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">Nama Pelanggan</th>
                        <th class="px-6 py-4">Kontak</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Riwayat & Tagihan</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($customers as $c)
                        @php
                            $soCount = $c->salesOrders->count();
                            $invoices = $c->getAllInvoices();
                            $outstanding = $c->total_outstanding_balance;
                        @endphp
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-900">
                                    <a href="{{ route('customers.show', $c) }}" class="hover:underline">{{ $c->name }}</a>
                                </div>
                                <span class="text-xs text-slate-500 font-normal block">{{ $c->email }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold text-slate-700 block">{{ $c->phone }}</span>
                                <span class="text-[11px] text-slate-400 font-normal truncate block max-w-xs">{{ $c->address }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($c->is_active)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">Aktif</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-60">Non-aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <!-- Tombol Dropdown / Expand Riwayat & Tagihan (Icon hanya chevron toggle button) -->
                                <button type="button" 
                                        onclick="toggleCustomerDetails({{ $c->id }})"
                                        id="details-btn-{{ $c->id }}"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold btn-subtle cursor-pointer transition-all">
                                    <svg id="details-chevron-{{ $c->id }}" class="w-3.5 h-3.5 transition-transform duration-300 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                    </svg>
                                    <span>{{ $soCount }} Pesanan</span>
                                    
                                    @if($outstanding > 0)
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-900 text-white">
                                            Sisa: Rp {{ number_format($outstanding, 0, ',', '.') }}
                                        </span>
                                    @elseif($soCount > 0)
                                        <span class="ml-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold badge-dark">
                                            Lunas
                                        </span>
                                    @endif
                                </button>
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('customers.show', $c) }}" class="text-xs text-slate-900 font-bold hover:underline">Detail</a>
                                <a href="{{ route('customers.edit', $c) }}" class="text-xs text-slate-600 font-bold hover:text-slate-900">Edit</a>
                                <form action="{{ route('customers.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pelanggan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-slate-500 font-bold hover:text-slate-900">Hapus</button>
                                </form>
                            </td>
                        </tr>

                        <!-- Baris Dropdown Detail Riwayat Pembelian & Tagihan -->
                        <tr id="details-row-{{ $c->id }}" class="hidden bg-slate-900/[0.03]">
                            <td colspan="5" class="px-6 py-5">
                                <div class="apple-glass-panel rounded-2xl p-5 border border-slate-900/10 shadow-sm space-y-5">
                                    
                                    <!-- Ringkasan Finansial Header -->
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-900/10 gap-3">
                                        <div>
                                            <h4 class="font-extrabold text-sm text-slate-900">Ringkasan Transaksi: {{ $c->name }}</h4>
                                            <p class="text-xs text-slate-500 font-medium">ID Pelanggan: #{{ $c->id }} &bull; {{ $c->email }}</p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-3">
                                            <div class="px-3 py-1.5 rounded-xl bg-white/70 border border-slate-900/10 text-xs">
                                                <span class="text-slate-500 font-semibold">Total Pembelian:</span>
                                                <span class="font-extrabold text-slate-900 ml-1">Rp {{ number_format($c->total_purchases_amount, 0, ',', '.') }}</span>
                                            </div>
                                            
                                            <div class="px-3 py-1.5 rounded-xl bg-white/70 border border-slate-900/10 text-xs">
                                                <span class="text-slate-500 font-semibold">Sisa Tagihan Belum Lunas:</span>
                                                @if($outstanding > 0)
                                                    <span class="font-extrabold text-slate-900 ml-1 underline">Rp {{ number_format($outstanding, 0, ',', '.') }}</span>
                                                @else
                                                    <span class="font-extrabold text-slate-700 ml-1">Rp 0 (Lunas)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Grid Konten: 2 Kolom (Riwayat Pembelian & Data Tagihan) -->
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                                        
                                        <!-- Kolom 1: Riwayat Pembelian (Sales Orders) -->
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h5 class="text-xs font-black uppercase tracking-wider text-slate-700">Riwayat Pembelian ({{ $soCount }})</h5>
                                                </div>
                                                <a href="{{ route('sales-orders.create') }}?customer_id={{ $c->id }}" class="text-[11px] font-bold text-slate-900 hover:underline">+ Order Baru</a>
                                            </div>

                                            @forelse($c->salesOrders as $so)
                                                <div class="p-3.5 rounded-xl bg-white/80 border border-slate-900/10 hover:border-slate-900/30 transition space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <a href="{{ route('sales-orders.show', $so) }}" class="font-mono text-xs font-black text-slate-900 hover:underline">
                                                                {{ $so->order_number }}
                                                            </a>
                                                            <span class="text-[11px] text-slate-500 block">{{ $so->created_at->format('d M Y, H:i') }}</span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold badge-dark uppercase">
                                                                {{ $so->status->value }}
                                                            </span>
                                                            <span class="block text-xs font-black text-slate-900 mt-0.5">
                                                                Rp {{ number_format($so->total_amount, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Rincian Item Produk -->
                                                    @if($so->items->isNotEmpty())
                                                        <div class="pt-2 border-t border-slate-900/5 text-xs text-slate-600 space-y-1">
                                                            @foreach($so->items as $item)
                                                                <div class="flex items-center justify-between text-[11px]">
                                                                    <span class="truncate max-w-[200px] font-medium text-slate-700">
                                                                        {{ $item->quantity }}x {{ $item->product->name ?? 'Produk' }}
                                                                    </span>
                                                                    <span class="font-mono font-bold text-slate-800">
                                                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="p-4 rounded-xl bg-white/40 border border-dashed border-slate-900/20 text-center text-xs text-slate-400 font-medium">
                                                    Belum ada riwayat pesanan / pembelian.
                                                </div>
                                            @endforelse
                                        </div>

                                        <!-- Kolom 2: Tagihan & Faktur (Invoices & Detail Pembelian / Produk) -->
                                        <div class="space-y-3">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h5 class="text-xs font-black uppercase tracking-wider text-slate-700">Status Tagihan & Faktur ({{ $invoices->count() }})</h5>
                                                </div>
                                                <span class="text-[11px] font-bold text-slate-500">
                                                    {{ $c->getOutstandingInvoices()->count() }} Tagihan Aktif
                                                </span>
                                            </div>

                                            @forelse($invoices as $inv)
                                                <div class="p-3.5 rounded-xl bg-white/80 border border-slate-900/10 space-y-2.5">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <a href="{{ route('invoices.show', $inv) }}" class="font-mono text-xs font-black text-slate-900 hover:underline">
                                                                {{ $inv->invoice_number }}
                                                            </a>
                                                            <span class="text-[11px] text-slate-500 block mt-0.5">
                                                                Pesanan: <a href="{{ route('sales-orders.show', $inv->salesOrder) }}" class="font-mono font-bold text-slate-800 hover:underline">#{{ $inv->salesOrder->order_number ?? '-' }}</a> &bull; Tempo: {{ $inv->due_date ? $inv->due_date->format('d M Y') : '-' }}
                                                            </span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase badge-dark">
                                                                {{ $inv->status->value }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <!-- Rincian Produk Yang Ditagihkan pada Invoice ini -->
                                                    @if($inv->salesOrder && $inv->salesOrder->items->isNotEmpty())
                                                        <div class="p-2.5 rounded-xl bg-slate-900/[0.03] border border-slate-900/5 text-xs space-y-1.5">
                                                            <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider block">
                                                                Produk Yang Ditagihkan:
                                                            </span>
                                                            @foreach($inv->salesOrder->items as $item)
                                                                <div class="flex items-center justify-between text-[11px]">
                                                                    <span class="truncate max-w-[220px] font-medium text-slate-800">
                                                                        {{ $item->quantity }}x {{ $item->product->name ?? 'Produk' }}
                                                                    </span>
                                                                    <span class="font-mono font-semibold text-slate-700">
                                                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    <div class="pt-2 border-t border-slate-900/5 grid grid-cols-3 gap-2 text-[11px]">
                                                        <div>
                                                            <span class="text-slate-500 block">Total Faktur:</span>
                                                            <span class="font-extrabold text-slate-900">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div>
                                                            <span class="text-slate-500 block">Terbayar:</span>
                                                            <span class="font-extrabold text-slate-800">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</span>
                                                        </div>
                                                        <div class="text-right">
                                                            <span class="text-slate-500 block">Sisa Tagihan:</span>
                                                            @if($inv->remaining_amount > 0)
                                                                <span class="font-black text-slate-900 underline">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</span>
                                                            @else
                                                                <span class="font-bold text-slate-700">Rp 0</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="pt-1 text-right">
                                                        <a href="{{ route('invoices.show', $inv) }}" class="text-[11px] font-bold text-slate-900 hover:underline">
                                                            Detail & Catat Bayar →
                                                        </a>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="p-4 rounded-xl bg-white/40 border border-dashed border-slate-900/20 text-center text-xs text-slate-400 font-medium">
                                                    Belum ada faktur penagihan yang diterbitkan.
                                                </div>
                                            @endforelse
                                        </div>

                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-900/10">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleCustomerDetails(id) {
        const row = document.getElementById('details-row-' + id);
        const chevron = document.getElementById('details-chevron-' + id);
        const btn = document.getElementById('details-btn-' + id);

        if (!row) return;

        const isHidden = row.classList.contains('hidden');

        if (isHidden) {
            row.classList.remove('hidden');
            if (chevron) chevron.classList.add('rotate-180');
            if (btn) {
                btn.classList.add('bg-slate-900', 'text-white');
                btn.classList.remove('btn-subtle');
            }
        } else {
            row.classList.add('hidden');
            if (chevron) chevron.classList.remove('rotate-180');
            if (btn) {
                btn.classList.remove('bg-slate-900', 'text-white');
                btn.classList.add('btn-subtle');
            }
        }
    }

    function openImportCustomerModal() {
        document.getElementById('importCustomerModal').classList.remove('hidden');
    }

    function closeImportCustomerModal() {
        document.getElementById('importCustomerModal').classList.add('hidden');
    }
</script>
@endsection

@push('modals')
<!-- Import Customer Liquid Glass Modal (Bright & Luminous) -->
<div id="importCustomerModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-md hidden p-4 overflow-y-auto">
    <div class="bg-white/95 backdrop-blur-3xl max-w-lg w-full p-6 md:p-8 rounded-3xl border border-white/90 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.15)] ring-1 ring-slate-900/5 relative my-8">
        <!-- Header -->
        <div class="flex items-start justify-between pb-4 mb-5 border-b border-slate-100">
            <div>
              <span class="text-[10px] font-extrabold uppercase tracking-widest block">
                <span class="text-red-500">BETA Experimental</span>
                <span class="text-slate-400"> Data Integration</span>

                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    <span class="text-red-500">BETA Experimental</span> Import Pelanggan dari Excel / CSV
                </h3>

                <p class=" text-slate-500 font-medium mt-0.5">
                    Fitur ini masih dalam tahap uji coba, harap input data asli dengan manual terlebih dahulu.
                </p>
            </div>
            <button type="button" onclick="closeImportCustomerModal()" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center text-xs font-bold transition">
                ✕
            </button>
        </div>

        <form action="{{ route('customers.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilih File Spreadsheet (.csv, .xlsx, .xls) *</label>
                <div class="p-3 rounded-2xl bg-white border border-slate-200 shadow-sm">
                    <input type="file" name="file" required accept=".csv,.xlsx,.xls,.txt" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-900 file:text-white hover:file:bg-slate-800 cursor-pointer">
                </div>
                <span class="text-[10px] text-slate-400 mt-1 block">Mendukung format .csv, .xlsx, .xls hingga 10MB</span>
            </div>

            <!-- Bright Info & Template Card -->
            <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200/80 shadow-sm space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-900">Format Kolom Sesuai</span>
                    <span class="text-[10px] font-bold text-slate-500 font-mono">CSV / XLSX</span>
                </div>
                <p class="text-xs text-slate-600 font-medium leading-relaxed">
                    Struktur kolom header: <code class="text-[11px] font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200 text-slate-800">nama_pelanggan, email, telepon, alamat, status_aktif</code>
                </p>
                <div class="pt-1">
                    <a href="{{ route('customers.template.excel') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-900 bg-white hover:bg-slate-100 px-3.5 py-1.5 rounded-xl border border-slate-200 shadow-sm transition">
                        <span>↓ Unduh Template Excel / CSV</span>
                    </a>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeImportCustomerModal()" class="px-5 py-2.5 rounded-full btn-subtle text-xs font-bold transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-xs font-bold transition">Mulai Import</button>
            </div>
        </form>
    </div>
</div>
@endpush
