@extends('layouts.app', ['title' => 'Riwayat Tembak Kayu'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">Tembak Kayu</h2>
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white uppercase tracking-widest">
                Manufaktur
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.tembaks.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-bold shadow-sm transition uppercase tracking-widest">
                Inisiasi Tembak Baru
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Total Sesi Tembak</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalBatches) }}
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Bahan Tembak Diolah</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalWoodProcessed, 1) }} <span class="text-xs font-sans font-bold text-slate-500">kg</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Getah Digunakan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalResinUsed, 1) }} <span class="text-xs font-sans font-bold text-slate-500">kg</span>
            </div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card bg-slate-900 text-white">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Hasil Tembak Kering</span>
            <div class="text-2xl font-bold mt-1 font-mono">
                {{ number_format($totalDriedProduced, 1) }} <span class="text-xs font-sans font-bold text-slate-400">kg</span>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 uppercase tracking-widest">Riwayat Proses Tembak</span>
                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full badge-dark">
                    {{ $tembakBatches->total() }} Data
                </span>
            </div>
            
            <form method="GET" action="{{ route('production.tembaks.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="px-3 py-1.5 rounded-xl apple-input text-xs font-bold w-64" placeholder="Cari sesi...">
                @if(request('search'))
                    <a href="{{ route('production.tembaks.index') }}" class="text-[10px] font-bold uppercase tracking-widest text-slate-500 hover:text-slate-900 underline">RESET</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-900">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-widest">
                        <th class="py-3 px-3">Kode Tembak & Status</th>
                        <th class="py-3 px-3">Bahan Dipakai & Modal</th>
                        <th class="py-3 px-3">Hasil Kering & Sisa</th>
                        <th class="py-3 px-3">PIC</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tembakBatches as $batch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-3">
                                <div class="font-bold font-mono text-slate-900">{{ $batch->tembak_code }}</div>
                                <div class="text-slate-500 font-bold text-[10px] mb-1.5">{{ $batch->tembak_date ? $batch->tembak_date->format('d/m/Y') : '-' }}</div>
                                @if($batch->isCompleted())
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-900 text-white uppercase tracking-widest">
                                        SELESAI
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-900 border border-slate-300 uppercase tracking-widest">
                                        SEDANG DITEMBAK
                                    </span>
                                @endif
                            </td>
                            
                            <td class="py-3 px-3">
                                @php 
                                    $woods = $batch->materials->where('type', 'wood');
                                    $resins = $batch->materials->where('type', 'resin');
                                    $totalCost = $batch->materials->sum(function($m) { return $m->weight * $m->unit_cost; });
                                @endphp
                                <div class="space-y-1">
                                    <div class="text-slate-900 font-bold text-xs">{{ $woods->count() }} Bahan Tembak, {{ $resins->count() }} Getah</div>
                                    <div class="text-slate-500 font-bold font-mono text-[11px]">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                                </div>
                            </td>
                            
                            <td class="py-3 px-3">
                                @if($batch->isCompleted())
                                    <div class="font-bold text-slate-900 font-mono text-xs">
                                        Kering: {{ number_format($batch->dried_result_weight, 1) }} kg
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono font-bold mt-1">
                                        Sisa Getah: {{ number_format($batch->residual_resin_weight, 1) }} kg
                                    </div>
                                @else
                                    <span class="text-[10px] font-bold text-slate-400 italic uppercase tracking-widest">Belum dilapor</span>
                                @endif
                            </td>
                            
                            <td class="py-3 px-3 text-slate-900">
                                <div class="font-bold">{{ $batch->pic_name }}</div>
                                @if($batch->report_pic_name && $batch->report_pic_name !== $batch->pic_name)
                                    <div class="text-[10px] font-bold text-slate-500 mt-1 uppercase tracking-widest">Lap: {{ $batch->report_pic_name }}</div>
                                @endif
                            </td>
                            
                            <td class="py-3 px-3 text-right">
                                @if(!$batch->isCompleted())
                                    <a href="{{ route('production.tembaks.show', $batch) }}" class="px-3 py-1.5 rounded-xl btn-dark font-bold text-[10px] shadow-xs transition inline-block mr-1 uppercase tracking-widest">
                                        LAPOR
                                    </a>
                                @endif
                                <a href="{{ route('production.tembaks.show', $batch) }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 font-bold text-[10px] shadow-xs transition inline-block uppercase tracking-widest">
                                    LIHAT
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs font-bold uppercase tracking-widest">
                                Belum ada data sesi tembak.
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
