@extends('layouts.app', ['title' => 'Detail Peminjaman #' . $loan->loan_number])

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 font-semibold mb-1">
                <a href="{{ route('stin.loans.index') }}" class="hover:text-slate-900 transition">Barang Dipinjam</a>
                <span>/</span>
                <span class="text-slate-900 font-mono">{{ $loan->loan_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900">{{ $loan->loan_number }}</h2>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $loan->status->badgeClass() }}">
                    {{ $loan->status->label() }}
                </span>
                @if($loan->isOverdue())
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                        Melewati Batas Waktu
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('stin.loans.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
                Kembali
            </a>
            @if($loan->status !== \App\Enums\WarehouseLoanStatus::RETURNED)
                <a href="{{ route('stin.loans.return', $loan) }}" class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition">
                    + Proses Pengembalian
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-3 rounded-xl border border-emerald-300 bg-emerald-50 text-xs font-semibold text-emerald-900">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="px-4 py-3 rounded-xl border border-sky-300 bg-sky-50 text-xs font-semibold text-sky-900">
            {{ session('info') }}
        </div>
    @endif

    <!-- Panel Informasi Utama & Progress Ringkasan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri (2 col): Detail Peminjam & Metadata -->
        <div class="lg:col-span-2 apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest pb-2 border-b border-slate-900/10">
                Informasi Peminjaman
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-500 font-medium block">Peminjam / PIC Penanggung Jawab</span>
                    <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $loan->borrower_name }}</span>
                    @if($loan->borrower_contact)
                        <span class="font-mono text-slate-500 text-[11px]">{{ $loan->borrower_contact }}</span>
                    @endif
                </div>

                <div>
                    <span class="text-slate-500 font-medium block">Keperluan Peminjaman</span>
                    <span class="font-bold text-slate-900 text-sm mt-0.5 block">{{ $loan->purpose }}</span>
                </div>

                <div>
                    <span class="text-slate-500 font-medium block">Tanggal Pinjam Keluar</span>
                    <span class="font-semibold text-slate-800 mt-0.5 block">
                        {{ $loan->loan_date?->translatedFormat('d F Y') ?? '-' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 font-medium block">Estimasi Tanggal Kembali</span>
                    <span class="font-semibold {{ $loan->isOverdue() ? 'text-rose-700 font-bold' : 'text-slate-800' }} mt-0.5 block">
                        {{ $loan->expected_return_date?->translatedFormat('d F Y') ?? 'Tidak ditentukan' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 font-medium block">Petugas Gudang (Pembuat)</span>
                    <span class="font-semibold text-slate-800 mt-0.5 block">
                        {{ $loan->user?->name ?? 'Petugas STIN' }}
                    </span>
                </div>

                <div>
                    <span class="text-slate-500 font-medium block">Waktu Selesai Kembali</span>
                    <span class="font-semibold text-slate-800 mt-0.5 block">
                        {{ $loan->returned_at?->translatedFormat('d F Y, H:i') ?? 'Belum selesai' }}
                    </span>
                </div>
            </div>

            @if($loan->notes)
                <div class="pt-3 border-t border-slate-900/10">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Catatan Tambahan</span>
                    <p class="text-xs text-slate-700 bg-white/70 p-3 rounded-xl border border-slate-200">
                        {{ $loan->notes }}
                    </p>
                </div>
            @endif
        </div>

        <!-- Kolom Kanan (1 col): Progress Barang & Tanda Tangan Peminjam -->
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest pb-2 border-b border-slate-900/10">
                    Status Pengembalian Fisik
                </h3>

                @php
                    $totalLoaned = $loan->totalQuantityLoaned();
                    $totalReturned = $loan->totalQuantityReturned();
                    $totalRemaining = $loan->totalQuantityRemaining();
                    $progressPct = $totalLoaned > 0 ? round(($totalReturned / $totalLoaned) * 100) : 0;
                @endphp

                <div class="space-y-3 mt-3">
                    <div class="flex items-center justify-between text-xs font-bold">
                        <span class="text-slate-600">Progress Kembali</span>
                        <span class="font-mono text-slate-900">{{ $progressPct }}%</span>
                    </div>

                    <div class="w-full bg-slate-200/80 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-500 {{ $loan->status === \App\Enums\WarehouseLoanStatus::RETURNED ? 'bg-emerald-600' : 'bg-slate-900' }}" 
                             style="width: {{ $progressPct }}%"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 pt-2 text-center">
                        <div class="p-2 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-slate-500 font-bold block uppercase">Dipinjam</span>
                            <span class="font-extrabold text-sm text-slate-900 font-mono">{{ $totalLoaned }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-emerald-700 font-bold block uppercase">Kembali</span>
                            <span class="font-extrabold text-sm text-emerald-700 font-mono">{{ $totalReturned }}</span>
                        </div>
                        <div class="p-2 rounded-xl bg-white border border-slate-200">
                            <span class="text-[10px] text-amber-700 font-bold block uppercase">Sisa</span>
                            <span class="font-extrabold text-sm text-amber-700 font-mono">{{ $totalRemaining }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tanda Tangan Peminjam -->
            <div class="pt-4 border-t border-slate-900/10">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Tanda Tangan Peminjam</span>
                @if($loan->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($loan->signature_path))
                    <div class="p-2 bg-white rounded-xl border border-slate-200 inline-block">
                        <img src="{{ asset('storage/' . $loan->signature_path) }}" alt="Tanda Tangan Peminjam" class="max-h-20 max-w-full object-contain">
                    </div>
                @else
                    <span class="text-xs text-slate-400 italic block">Tidak ada lampiran tanda tangan.</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Tabel Rincian Barang yang Dipinjam -->
    <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-2 border-b border-slate-900/10">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                    Rincian Barang yang Dipinjam
                </h3>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                    Daftar item fisik beserta kuantitas pinjam dan sisa pengembalian.
                </p>
            </div>
            @if($loan->status !== \App\Enums\WarehouseLoanStatus::RETURNED)
                <a href="{{ route('stin.loans.return', $loan) }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-xs transition">
                    Proses Pengembalian
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-4 py-3">Nama Barang & SKU</th>
                        <th class="px-4 py-3 text-center">Jumlah Pinjam</th>
                        <th class="px-4 py-3 text-center">Sudah Kembali</th>
                        <th class="px-4 py-3 text-center">Sisa di Luar</th>
                        <th class="px-4 py-3 text-center">Status Barang</th>
                        <th class="px-4 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @foreach($loan->items as $item)
                        @php
                            $isDone = $item->isFullyReturned();
                            $rem = $item->remainingQuantity();
                        @endphp
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-4 py-3">
                                @if($item->item)
                                    <div class="font-bold text-slate-900">{{ $item->item->name }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">{{ $item->item->sku }}</div>
                                @else
                                    <span class="text-slate-400 italic">Barang telah dihapus</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-slate-900">
                                {{ number_format($item->quantity_loaned) }} {{ $item->item?->unit ?? 'pcs' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-bold text-emerald-700">
                                {{ number_format($item->quantity_returned) }} {{ $item->item?->unit ?? 'pcs' }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono font-extrabold {{ $rem > 0 ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ number_format($rem) }} {{ $item->item?->unit ?? 'pcs' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($isDone)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas Kembali
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        Masih Dipinjam
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                {{ $item->notes ?? '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Panel Riwayat Sesi Pengembalian (Jika Ada) -->
    @if($loan->returns->isNotEmpty())
        <div class="apple-glass-card rounded-3xl p-6 shadow-xs space-y-4">
            <div class="pb-2 border-b border-slate-900/10">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">
                    Riwayat Sesi Pengembalian Barang
                </h3>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                    Log transaksi pengembalian fisik barang ke gudang (termasuk bertahap/parsial).
                </p>
            </div>

            <div class="space-y-4">
                @foreach($loan->returns as $ret)
                    <div class="p-4 rounded-2xl bg-white border border-slate-300 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-2 border-b border-slate-200 gap-2">
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900 text-xs">{{ $ret->return_number }}</span>
                                <span class="text-slate-400">•</span>
                                <span class="text-xs text-slate-600 font-medium">
                                    {{ $ret->return_date?->translatedFormat('d M Y, H:i') ?? '-' }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-700">
                                Diterima oleh: <span class="font-bold text-slate-900">{{ $ret->receiver_name }}</span>
                            </div>
                        </div>

                        <!-- Daftar Item yang Dikembalikan pada Sesi ini -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-500 uppercase font-bold border-b border-slate-200">
                                        <th class="py-1.5">Nama Barang</th>
                                        <th class="py-1.5 text-center">Jumlah Kembali</th>
                                        <th class="py-1.5 text-center">Kondisi</th>
                                        <th class="py-1.5">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($ret->items as $retItem)
                                        <tr>
                                            <td class="py-2 font-bold text-slate-900">
                                                {{ $retItem->item?->name ?? 'Barang' }}
                                            </td>
                                            <td class="py-2 text-center font-mono font-bold text-emerald-700">
                                                +{{ number_format($retItem->quantity_returned) }} {{ $retItem->item?->unit ?? 'pcs' }}
                                            </td>
                                            <td class="py-2 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $retItem->condition === 'good' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                                    {{ $retItem->condition === 'good' ? 'Baik / Utuh' : ucfirst($retItem->condition) }}
                                                </span>
                                            </td>
                                            <td class="py-2 text-slate-600">
                                                {{ $retItem->notes ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($ret->notes || $ret->signature_path)
                            <div class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                                <div class="text-slate-600 italic">
                                    {{ $ret->notes ? 'Catatan: "' . $ret->notes . '"' : '' }}
                                </div>
                                @if($ret->signature_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($ret->signature_path))
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-slate-500 uppercase">Tanda Tangan:</span>
                                        <img src="{{ asset('storage/' . $ret->signature_path) }}" alt="Tanda Tangan Penerima" class="h-10 max-w-[120px] object-contain border border-slate-200 rounded p-1 bg-white">
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
