@extends('layouts.app', ['title' => 'Barang Dipinjam'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Title & Count -->
        <div class="flex-shrink-0">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-slate-900">Barang Dipinjam</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800">
                    {{ number_format($loans->total()) }} Transaksi
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Pemantauan sirkulasi peminjaman barang keluar sementara.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('stin.scanner.index') }}" class="px-4 py-2.5 rounded-2xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
                Scan Barcode
            </a>
            <a href="{{ route('stin.scanner.index', ['mode' => 'loan']) }}" class="px-5 py-2.5 rounded-2xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-xs">
                + Pinjam Barang
            </a>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Status Filter Pills -->
        <div class="flex flex-wrap items-center gap-1.5">
            @php $currentStatus = request('status', 'all'); @endphp
            <a href="{{ route('stin.loans.index', array_merge(request()->except('page'), ['status' => 'all'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100' }}">
                Semua
            </a>
            <a href="{{ route('stin.loans.index', array_merge(request()->except('page'), ['status' => 'borrowed'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'borrowed' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100' }}">
                Sedang Dipinjam
            </a>
            <a href="{{ route('stin.loans.index', array_merge(request()->except('page'), ['status' => 'overdue'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'overdue' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100' }}">
                Lewat Batas (Overdue)
            </a>
            <a href="{{ route('stin.loans.index', array_merge(request()->except('page'), ['status' => 'returned'])) }}" 
               class="px-3 py-1.5 rounded-xl text-xs font-bold transition {{ $currentStatus === 'returned' ? 'bg-slate-900 text-white shadow-xs' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100' }}">
                Sudah Kembali
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('stin.loans.index') }}" class="flex items-center gap-2 max-w-md w-full">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input 
                type="text" 
                name="search" 
                value="{{ request('search') }}" 
                placeholder="Cari no. pinjaman, peminjam, keperluan, barang..." 
                class="apple-input flex-1 rounded-2xl px-4 py-2 text-sm font-medium border border-slate-300 bg-white text-slate-900 placeholder:text-slate-400 shadow-xs focus:border-slate-900"
            >
            <button type="submit" class="px-4 py-2 rounded-2xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition">
                Cari
            </button>
            @if(request('search') || (request('status') && request('status') !== 'all'))
                <a href="{{ route('stin.loans.index') }}" class="px-3 py-2 rounded-2xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 text-xs font-semibold transition">
                    Reset
                </a>
            @endif
        </form>
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

    <!-- Loans Table -->
    <div class="apple-glass-card rounded-2xl overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-5 py-4">No. Transaksi & Tanggal</th>
                        <th class="px-5 py-4">Peminjam / PIC</th>
                        <th class="px-5 py-4">Keperluan / Lokasi</th>
                        <th class="px-5 py-4">Status & Progress Barang</th>
                        <th class="px-5 py-4">Batas Pengembalian</th>
                        <th class="px-5 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($loans as $loan)
                        @php
                            $totalLoaned = $loan->totalQuantityLoaned();
                            $totalReturned = $loan->totalQuantityReturned();
                            $totalRemaining = $loan->totalQuantityRemaining();
                            $progressPct = $totalLoaned > 0 ? round(($totalReturned / $totalLoaned) * 100) : 0;
                            $isOverdue = $loan->isOverdue();
                        @endphp
                        <tr class="hover:bg-white/60 transition">
                            <!-- No Transaksi & Tanggal -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('stin.loans.show', $loan) }}" class="font-mono font-bold text-slate-900 hover:underline">
                                    {{ $loan->loan_number }}
                                </a>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $loan->loan_date?->translatedFormat('d M Y') ?? '-' }}
                                </div>
                            </td>

                            <!-- Peminjam -->
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-900">{{ $loan->borrower_name }}</div>
                                @if($loan->borrower_contact)
                                    <div class="text-[11px] text-slate-500 font-mono">{{ $loan->borrower_contact }}</div>
                                @endif
                            </td>

                            <!-- Keperluan -->
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $loan->purpose }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $loan->items_count }} jenis barang
                                </div>
                            </td>

                            <!-- Status & Progress -->
                            <td class="px-5 py-4 min-w-[200px]">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold {{ $loan->status->badgeClass() }}">
                                        {{ $loan->status->label() }}
                                    </span>
                                    <span class="font-mono text-xs font-bold text-slate-700">
                                        {{ $totalReturned }} / {{ $totalLoaned }} unit
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200/80 rounded-full h-2 overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500 {{ $loan->status === \App\Enums\WarehouseLoanStatus::RETURNED ? 'bg-emerald-600' : 'bg-slate-900' }}" 
                                         style="width: {{ $progressPct }}%"></div>
                                </div>
                                @if($totalRemaining > 0)
                                    <div class="text-[10px] text-slate-500 mt-1 font-medium">
                                        Sisa {{ $totalRemaining }} unit di luar
                                    </div>
                                @endif
                            </td>

                            <!-- Batas Waktu -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($loan->expected_return_date)
                                    <div class="font-semibold {{ $isOverdue ? 'text-rose-700 font-bold' : 'text-slate-800' }}">
                                        {{ $loan->expected_return_date->translatedFormat('d M Y') }}
                                    </div>
                                    @if($isOverdue)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 mt-0.5">
                                            Lewat Batas
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 italic">Tidak ditentukan</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if($loan->status !== \App\Enums\WarehouseLoanStatus::RETURNED)
                                        <a href="{{ route('stin.loans.return', $loan) }}" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold shadow-xs transition">
                                            Kembalikan
                                        </a>
                                    @endif
                                    <a href="{{ route('stin.loans.show', $loan) }}" class="px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-800 text-xs font-semibold shadow-xs transition">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                @if(request('search') || request('status'))
                                    Tidak ada data peminjaman yang sesuai dengan filter pencarian.
                                @else
                                    Belum ada catatan peminjaman barang.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($loans->hasPages())
            <div class="px-6 py-4 border-t border-slate-900/10 bg-white/30">
                {{ $loans->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
