@extends('layouts.app', ['title' => 'Warehouse - Pencairan Getah'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Pencairan
                </span>
            </div>
            <p class="text-xs text-slate-600 mt-1">Pencairan getah padat menjadi cairan dan pemantauan penyusutan/penguapan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.melts.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-bold shadow-sm transition inline-block">
                + Inisiasi Pencairan Baru
            </a>
        </div>
    </div>

    <!-- 1. PROSES BERJALAN -->
    <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900">Proses Pencairan Berjalan</span>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-900 text-white">
                    {{ $ongoingBatches->total() }} Sedang Dikerjakan
                </span>
            </div>
            <p class="text-xs text-slate-600">Batch pencairan getah aktif yang menunggu pencatatan laporan hasil pencairan.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3.5">Kode Pencairan</th>
                        <th class="py-3 px-3.5">Tanggal Inisiasi</th>
                        <th class="py-3 px-3.5">Bahan Padat (Awal)</th>
                        <th class="py-3 px-3.5">Hasil Cairan (Akhir)</th>
                        <th class="py-3 px-3.5">Kondisi Terakhir</th>
                        <th class="py-3 px-3.5">PIC</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($ongoingBatches as $batch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3.5">
                                <span class="font-bold font-mono text-slate-900 text-xs block">{{ $batch->melt_code }}</span>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    Menunggu Laporan
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->melt_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                <span class="text-[11px] text-slate-700 font-mono font-semibold">{{ number_format($batch->initial_weight, 1) }} kg</span>
                            </td>
                            <td class="py-3.5 px-3.5">
                                @if($batch->target_material_id)
                                    <div class="font-bold text-slate-900 text-xs">{{ $batch->targetMaterial->name ?? '-' }}</div>
                                    <span class="text-[11px] text-slate-700 font-mono font-semibold">{{ number_format($batch->current_weight, 1) }} kg</span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5">
                                @if($batch->target_material_id)
                                    <div class="font-mono text-slate-900 font-bold text-xs">
                                        {{ number_format($batch->current_weight, 1) }} kg
                                    </div>
                                    <span class="text-[11px] font-mono font-semibold {{ $batch->evaporation_percentage > 0 ? 'text-slate-900' : 'text-slate-600' }}">
                                        Menguap: {{ number_format($batch->evaporation_percentage, 1) }}%
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->pic_name }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right">
                                <a href="{{ route('production.melts.show', $batch) }}" class="px-3.5 py-1.5 rounded-xl btn-dark font-bold text-xs shadow-xs transition inline-block whitespace-nowrap text-center">
                                    Laporan Pencairan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-600 text-xs">
                                Tidak ada proses pencairan yang sedang berjalan. Klik tombol <strong>+ Inisiasi Pencairan Baru</strong> untuk mulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ongoingBatches->hasPages())
            <div class="pt-4 border-t border-slate-200">
                {{ $ongoingBatches->links() }}
            </div>
        @endif
    </div>

    <!-- 2. RIWAYAT SELESAI -->
    <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900">Riwayat Pencairan Selesai</span>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-300">
                    {{ $completedBatches->total() }} Data
                </span>
            </div>

            <form method="GET" action="{{ route('production.melts.index') }}" class="flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-300 text-xs font-semibold text-slate-900 placeholder:text-slate-500 w-64 focus:border-slate-900 focus:outline-none"
                    placeholder="Cari kode, bahan, PIC..."
                >
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('production.melts.index') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 underline">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3.5">Kode Pencairan</th>
                        <th class="py-3 px-3.5">Tanggal</th>
                        <th class="py-3 px-3.5">Bahan Padat (Awal)</th>
                        <th class="py-3 px-3.5">Hasil Cairan (Akhir)</th>
                        <th class="py-3 px-3.5">Kondisi Terakhir</th>
                        <th class="py-3 px-3.5">PIC</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($completedBatches as $batch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3.5">
                                <span class="font-bold font-mono text-slate-900 text-xs block">{{ $batch->melt_code }}</span>
                                @if($batch->status === 'completed')
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900 text-white">
                                        Selesai
                                    </span>
                                @elseif($batch->status === 'monitoring')
                                    @if($batch->current_stock <= 0)
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900 text-white">
                                            Habis Terpakai
                                        </span>
                                    @else
                                        <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                            Dicairkan / Timbang
                                        </span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->melt_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                <span class="text-[11px] text-slate-700 font-mono font-semibold">{{ number_format($batch->initial_weight, 1) }} kg</span>
                            </td>
                            <td class="py-3.5 px-3.5">
                                @if($batch->target_material_id)
                                    <div class="font-bold text-slate-900 text-xs">{{ $batch->targetMaterial->name ?? '-' }}</div>
                                    <span class="text-[11px] text-slate-700 font-mono font-semibold">{{ number_format($batch->initial_liquid_weight, 1) }} kg</span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5">
                                @if($batch->target_material_id)
                                    <div class="font-mono text-slate-900 font-bold text-xs">
                                        {{ number_format($batch->current_stock, 1) }} kg
                                    </div>
                                    <span class="text-[11px] font-mono font-semibold {{ $batch->evaporation_percentage > 0 ? 'text-slate-900' : 'text-slate-600' }}">
                                        Menguap: {{ number_format($batch->evaporation_percentage, 1) }}%
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->report_pic_name ?? $batch->pic_name }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right">
                                @if($batch->status === 'monitoring' && $batch->current_stock > 0)
                                    <a href="{{ route('production.melts.show', $batch) }}" class="px-3.5 py-1.5 rounded-xl btn-dark font-bold text-xs shadow-xs transition inline-block whitespace-nowrap text-center">
                                        Timbang Ulang
                                    </a>
                                @else
                                    <a href="{{ route('production.melts.show', $batch) }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 font-bold text-xs shadow-xs transition inline-block whitespace-nowrap text-center">
                                        Rincian
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-600 text-xs">
                                Belum ada riwayat pencairan yang selesai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($completedBatches->hasPages())
            <div class="pt-4 border-t border-slate-200">
                {{ $completedBatches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
