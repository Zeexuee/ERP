@extends('layouts.app', ['title' => 'Riwayat Tembak Kayu'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">Tembak Kayu</h2>
            <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-widest">Manufaktur</span>
        </div>

        <a href="{{ route('production.tembaks.create') }}" class="btn-dark rounded-xl px-4 py-2 text-xs font-bold shadow-sm transition uppercase tracking-widest">
            Inisiasi Tembak Baru
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Sesi Tembak</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalBatches) }}</div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Bahan Tembak Diolah</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalWoodProcessed, 1) }} <span class="font-sans text-xs text-slate-500">kg</span></div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Getah Digunakan</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalResinUsed, 1) }} <span class="font-sans text-xs text-slate-500">kg</span></div>
        </div>
        <div class="apple-glass-card rounded-2xl bg-slate-900 p-5 text-white">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Hasil Jemur Selesai</span>
            <div class="mt-1 font-mono text-2xl font-bold">{{ number_format($totalDriedProduced, 1) }} <span class="font-sans text-xs text-slate-400">kg</span></div>
        </div>
    </div>

    <div class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
        <div class="flex flex-col gap-3 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 uppercase tracking-widest">Riwayat Tembak & Jemur</span>
                <span class="badge-dark rounded-full px-2 py-0.5 text-[11px] font-bold">{{ $tembakBatches->total() }} Data</span>
            </div>

            <form method="GET" action="{{ route('production.tembaks.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="apple-input w-64 rounded-xl px-3 py-1.5 text-xs font-bold" placeholder="Cari sesi, PIC, atau barang...">
                @if(request('search'))
                    <a href="{{ route('production.tembaks.index') }}" class="text-[10px] font-bold text-slate-500 underline hover:text-slate-900 uppercase tracking-widest">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-xs text-slate-900">
                <thead>
                    <tr class="border-b border-slate-900/10 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">
                        <th class="px-3 py-3">Kode & Status</th>
                        <th class="px-3 py-3">Bahan & Modal</th>
                        <th class="px-3 py-3">Progres Berat</th>
                        <th class="px-3 py-3">PIC</th>
                        <th class="px-3 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tembakBatches as $batch)
                        @php
                            $woods = $batch->materials->where('type', 'wood');
                            $resins = $batch->materials->where('type', 'resin');
                            $totalCost = $batch->materials->sum(
                                fn ($material) => (float) $material->weight * (float) $material->unit_cost
                            );
                        @endphp
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-3 py-3">
                                <div class="font-mono font-bold text-slate-900">{{ $batch->tembak_code }}</div>
                                <div class="mb-1.5 text-[10px] font-bold text-slate-500">{{ $batch->tembak_date?->format('d/m/Y') ?? '-' }}</div>
                                <span class="inline-block rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest
                                    {{ $batch->isCompleted()
                                        ? 'border-slate-900 bg-slate-900 text-white'
                                        : ($batch->isDrying()
                                            ? 'border-amber-300 bg-amber-50 text-amber-800'
                                            : 'border-slate-300 bg-slate-100 text-slate-900') }}">
                                    {{ $batch->status_label }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900">{{ $woods->count() }} Bahan Tembak, {{ $resins->count() }} Getah</div>
                                <div class="mt-1 font-mono text-[11px] font-bold text-slate-500">Rp {{ number_format($totalCost, 0, ',', '.') }}</div>
                            </td>

                            <td class="px-3 py-3">
                                @if($batch->isCompleted())
                                    <div class="font-mono text-xs font-bold text-slate-900">Final: {{ number_format($batch->dried_result_weight, 2) }} kg</div>
                                    <div class="mt-1 text-[10px] font-bold text-slate-500">Susut {{ number_format($batch->total_shrinkage_weight, 2) }} kg · {{ number_format($batch->shrinkage_percentage, 2) }}%</div>
                                @elseif($batch->isDrying())
                                    <div class="font-mono text-xs font-bold text-slate-900">{{ number_format($batch->wet_result_weight, 2) }} → {{ number_format($batch->current_drying_weight, 2) }} kg</div>
                                    <div class="mt-1 text-[10px] font-bold text-amber-700">Jemur {{ $batch->drying_duration_days }} hari</div>
                                @else
                                    <span class="text-[10px] font-bold italic text-slate-400 uppercase tracking-widest">Menunggu laporan tembak</span>
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900">{{ $batch->pic_name }}</div>
                                @if($batch->report_pic_name)
                                    <div class="mt-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Lap: {{ $batch->report_pic_name }}</div>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-right">
                                @if(! $batch->isCompleted())
                                    <a href="{{ route('production.tembaks.show', $batch) }}" class="btn-dark mr-1 inline-block rounded-xl px-3 py-1.5 text-[10px] font-bold shadow-xs transition uppercase tracking-widest">
                                        {{ $batch->isDrying() ? 'Lapor Jemur' : 'Lapor Tembak' }}
                                    </a>
                                @endif
                                <a href="{{ route('production.tembaks.show', $batch) }}" class="inline-block rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 uppercase tracking-widest">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada data sesi tembak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tembakBatches->hasPages())
            <div class="border-t border-slate-100 pt-4">{{ $tembakBatches->links() }}</div>
        @endif
    </div>
</div>
@endsection
