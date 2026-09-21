@extends('layouts.app', ['title' => 'Warehouse - Pencairan Getah'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Pencairan
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pencairan getah padat menjadi cairan dan pemantauan penguapan.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.melts.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition">
                Inisiasi Baru
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        <!-- Proses Berjalan -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4 border border-blue-900/10 bg-gradient-to-br from-blue-50/50 to-white/50 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-32 bg-blue-100/30 rounded-full blur-3xl -z-10 pointer-events-none"></div>
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-blue-900/10 pb-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900">Proses Pencairan Berjalan</span>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                        {{ $ongoingBatches->total() }} Data
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="border-b border-blue-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-3">Kode Pencairan</th>
                            <th class="py-3 px-3">Tanggal Inisiasi</th>
                            <th class="py-3 px-3">Bahan Padat (Awal)</th>
                            <th class="py-3 px-3">Hasil Cairan (Akhir)</th>
                            <th class="py-3 px-3">Kondisi Terakhir</th>
                            <th class="py-3 px-3">PIC</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-900/5">
                        @forelse($ongoingBatches as $batch)
                            <tr class="hover:bg-white/60 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold font-mono text-slate-900">{{ $batch->melt_code }}</div>
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Menunggu Laporan
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $batch->melt_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ number_format($batch->initial_weight, 1) }} kg</span>
                                </td>
                                <td class="py-3 px-3">
                                    @if($batch->target_material_id)
                                        <div class="font-bold text-slate-900">{{ $batch->targetMaterial->name ?? '-' }}</div>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ number_format($batch->current_weight, 1) }} kg</span>
                                    @else
                                        <span class="text-xs text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if($batch->target_material_id)
                                        <div class="font-mono text-slate-900 font-bold">
                                            {{ number_format($batch->current_weight, 1) }} kg
                                        </div>
                                        <span class="text-[10px] font-bold {{ $batch->evaporation_percentage > 0 ? 'text-red-500' : 'text-slate-500' }}">
                                            Menguap: {{ number_format($batch->evaporation_percentage, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    {{ $batch->pic_name }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('production.melts.show', $batch) }}" class="px-3 py-1.5 rounded-xl btn-dark font-semibold text-[11px] shadow-xs transition inline-block whitespace-nowrap text-center">
                                        Laporan Pencairan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada proses pencairan yang sedang berjalan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ongoingBatches->hasPages())
                <div class="pt-4 border-t border-blue-900/10">
                    {{ $ongoingBatches->links() }}
                </div>
            @endif
        </div>

        <!-- Riwayat Selesai -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900">Riwayat Pencairan Selesai</span>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-full badge-dark">
                        {{ $completedBatches->total() }} Data
                    </span>
                </div>

                <form method="GET" action="{{ route('production.melts.index') }}" class="flex items-center gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        class="px-3 py-1.5 rounded-xl apple-input text-xs w-64"
                        placeholder="Cari Riwayat..."
                    >
                    @if(request('search'))
                        <a href="{{ route('production.melts.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="border-b border-slate-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-3">Kode Pencairan</th>
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Bahan Padat (Awal)</th>
                            <th class="py-3 px-3">Hasil Cairan (Akhir)</th>
                            <th class="py-3 px-3">Kondisi Terakhir</th>
                            <th class="py-3 px-3">PIC</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($completedBatches as $batch)
                            <tr class="hover:bg-white/50 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold font-mono text-slate-900">{{ $batch->melt_code }}</div>
                                    @if($batch->status === 'completed')
                                        <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-slate-900 text-white">
                                            Selesai
                                        </span>
                                    @elseif($batch->status === 'monitoring')
                                        @if($batch->current_stock <= 0)
                                            <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-slate-900 text-white">
                                                Habis Terpakai
                                            </span>
                                        @else
                                            <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                                Dicairkan / Timbang
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $batch->melt_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ number_format($batch->initial_weight, 1) }} kg</span>
                                </td>
                                <td class="py-3 px-3">
                                    @if($batch->target_material_id)
                                        <div class="font-bold text-slate-900">{{ $batch->targetMaterial->name ?? '-' }}</div>
                                        <span class="text-[10px] text-slate-500 font-mono">{{ number_format($batch->initial_liquid_weight, 1) }} kg</span>
                                    @else
                                        <span class="text-xs text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if($batch->target_material_id)
                                        <div class="font-mono text-slate-900 font-bold">
                                            {{ number_format($batch->current_stock, 1) }} kg
                                        </div>
                                        <span class="text-[10px] font-bold {{ $batch->evaporation_percentage > 0 ? 'text-red-500' : 'text-slate-500' }}">
                                            Menguap: {{ number_format($batch->evaporation_percentage, 1) }}%
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    {{ $batch->report_pic_name ?? $batch->pic_name }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if($batch->status === 'monitoring' && $batch->current_stock > 0)
                                        <a href="{{ route('production.melts.show', $batch) }}" class="px-3 py-1.5 rounded-xl btn-dark font-semibold text-[11px] shadow-xs transition inline-block whitespace-nowrap text-center">
                                            Timbang Ulang
                                        </a>
                                    @else
                                        <a href="{{ route('production.melts.show', $batch) }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 font-semibold text-[11px] shadow-xs transition inline-block whitespace-nowrap text-center">
                                            Rincian
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada riwayat pencairan yang selesai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($completedBatches->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $completedBatches->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
