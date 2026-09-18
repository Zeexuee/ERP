@extends('layouts.app', ['title' => 'Proses Tembak Kayu'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900">Tembak Kayu</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                Manufaktur
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.tembaks.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition">
                Inisiasi Tembak Baru
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Sesi Tembak</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalBatches) }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kayu Diolah</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalWoodProcessed, 1) }} <span class="text-xs font-sans font-normal text-slate-500">kg</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Resin Digunakan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalResinUsed, 1) }} <span class="text-xs font-sans font-normal text-slate-500">kg</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Hasil Tembak Kering</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalDriedProduced, 1) }} <span class="text-xs font-sans font-normal text-slate-500">kg</span>
            </div>
        </div>
    </div>

    <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900">Riwayat Proses Tembak</span>
                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full badge-dark">
                    {{ $tembakBatches->total() }} Data
                </span>
            </div>

            <form method="GET" action="{{ route('production.tembaks.index') }}" class="flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    class="px-3 py-1.5 rounded-xl apple-input text-xs w-64"
                >
                @if(request('search'))
                    <a href="{{ route('production.tembaks.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-3">Kode Tembak</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Bahan Asal (Kayu & Resin)</th>
                        <th class="py-3 px-3">Hasil Basah & Sisa Getah</th>
                        <th class="py-3 px-3">Hasil Kering Masuk</th>
                        <th class="py-3 px-3">PIC</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tembakBatches as $batch)
                        <tr class="hover:bg-white/50 transition">
                            <td class="py-3 px-3">
                                <div class="font-bold font-mono text-slate-900">{{ $batch->tembak_code }}</div>
                                @if($batch->isCompleted())
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-slate-900 text-white">
                                        Selesai
                                    </span>
                                @else
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                        Sedang Ditembak
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $batch->tembak_date ? $batch->tembak_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="space-y-1">
                                    <div class="text-slate-900 font-semibold">
                                        {{ $batch->woodMaterial->name ?? '-' }}
                                        <span class="text-slate-500 font-mono font-normal">({{ number_format($batch->wood_weight, 1) }} {{ $batch->woodMaterial->unit ?? 'kg' }})</span>
                                    </div>
                                    <div class="text-slate-600 text-[11px]">
                                        {{ $batch->resinMaterial->name ?? '-' }}
                                        <span class="text-slate-500 font-mono">({{ number_format($batch->resin_weight, 1) }} {{ $batch->resinMaterial->unit ?? 'kg' }})</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                @if($batch->isCompleted() || $batch->wet_result_weight)
                                    <div class="font-bold text-slate-900 font-mono">
                                        Basah: {{ number_format($batch->wet_result_weight ?? 0, 1) }} kg
                                    </div>
                                    <div class="text-[11px] text-slate-600 font-mono">
                                        Sisa Getah: {{ number_format($batch->residual_resin_weight ?? 0, 1) }} kg
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                @if($batch->isCompleted())
                                    <div class="font-bold text-slate-900">
                                        {{ $batch->outputMaterial->name ?? '-' }}
                                    </div>
                                    <span class="text-[11px] font-mono font-bold text-slate-700">
                                        {{ number_format($batch->dried_result_weight ?? 0, 1) }} {{ $batch->outputMaterial->unit ?? 'kg' }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-700">
                                <div>{{ $batch->pic_name }}</div>
                                @if($batch->report_pic_name && $batch->report_pic_name !== $batch->pic_name)
                                    <div class="text-[10px] text-slate-400">Lap: {{ $batch->report_pic_name }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                @if(! $batch->isCompleted())
                                    <a href="{{ route('production.tembaks.show', $batch) }}" class="px-2.5 py-1 rounded-xl btn-dark font-semibold text-[11px] shadow-xs transition inline-block mr-1">
                                        Catat Laporan
                                    </a>
                                @endif
                                <a href="{{ route('production.tembaks.show', $batch) }}" class="px-3 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 font-semibold text-xs shadow-xs transition inline-block">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada data proses tembak.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tembakBatches->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $tembakBatches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
