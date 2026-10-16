@extends('layouts.app', ['title' => 'Warehouse - Jemur Bahan Baku'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Jemur Bahan Baku
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Proses pengeringan (jemur) bahan baku gudang & pencatatan penyusutan berat.</p>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Proses Jemur</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalDryingLogs }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Riwayat pengeringan tercatat</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Penyusutan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalShrinkage, 2) }} kg</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Akumulasi berat menyusut</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Siap Jemur</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $materials->count() }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Material dengan stok > 0</span>
        </div>
    </div>

    <!-- Daftar Bahan Siap Jemur -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Bahan Baku Siap Jemur</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                    {{ $materials->count() }} Bahan
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Kode</th>
                        <th class="py-2.5 px-3">Nama Bahan Baku</th>
                        <th class="py-2.5 px-3">Kategori</th>
                        <th class="py-2.5 px-3 text-right">Stok Saat Ini</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($materials as $mat)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">{{ $mat->code }}</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900">{{ $mat->name }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $mat->category }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                {{ number_format($mat->stock_quantity, 2) }} <span class="font-sans text-[10px] text-slate-500 font-normal">{{ $mat->unit }}</span>
                            </td>
                            <td class="py-3 px-3 text-center">
                                <a 
                                    href="{{ route('production.dryings.create', ['material_id' => $mat->id]) }}" 
                                    class="inline-block px-3 py-1.5 rounded-xl text-[11px] font-bold bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition"
                                >
                                    Mulai Jemur
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-400">
                                Tidak ada bahan baku dengan stok tersedia untuk dijemur.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Riwayat Proses Jemur -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Proses Jemur</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Log pencatatan pengeringan bahan baku gudang.</p>
            </div>

            <form action="{{ route('production.dryings.index') }}" method="GET" class="flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari kode/nama bahan..." 
                    class="text-xs px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-800 placeholder-slate-400 w-44 md:w-56 focus:outline-none"
                >
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('production.dryings.index') }}" class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-slate-800 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Tanggal</th>
                        <th class="py-2.5 px-3">Bahan Baku</th>
                        <th class="py-2.5 px-3 text-right">Berat Sebelum</th>
                        <th class="py-2.5 px-3 text-right">Berat Setelah</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3">Catatan</th>
                        <th class="py-2.5 px-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-2.5 px-3">
                                <span class="font-mono text-slate-900 font-semibold">{{ $log->start_date->format('d/m/Y') }}</span>
                                @if($log->end_date)
                                    <span class="block text-[10px] text-slate-500 font-mono">Selesai: {{ $log->end_date->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="font-bold text-slate-900">{{ $log->material->name ?? '-' }}</span>
                                <span class="block text-[10px] text-slate-500 font-mono">{{ $log->material->code ?? '' }}</span>
                            </td>
                            <td class="py-2.5 px-3 text-right font-mono text-slate-700">
                                {{ number_format($log->previous_weight, 2) }} {{ $log->material->unit ?? 'kg' }}
                            </td>
                            <td class="py-2.5 px-3 text-right">
                                @if($log->new_weight !== null)
                                    <span class="font-mono font-bold text-slate-900">{{ number_format($log->new_weight, 2) }} {{ $log->material->unit ?? 'kg' }}</span>
                                    @if((float) $log->shrinkage_weight > 0)
                                        <span class="block text-[10px] text-slate-500 font-mono">-{{ number_format($log->shrinkage_weight, 2) }} (Susut)</span>
                                    @endif
                                @else
                                    <span class="font-mono text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($log->status === 'menunggu')
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        Menjemur
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Selesai
                                    </span>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 text-slate-500 text-[11px] max-w-xs truncate" title="{{ $log->notes }}">
                                {{ $log->notes ?? '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-center">
                                @if($log->status === 'menunggu')
                                    <a 
                                        href="{{ route('production.dryings.report.create', $log->id) }}" 
                                        class="px-2.5 py-1 rounded-xl text-[11px] font-semibold bg-slate-900 text-white hover:bg-slate-800 shadow-sm transition"
                                    >
                                        Lapor
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                Belum ada riwayat proses jemur bahan baku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-900/10">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

</div>

@endsection
