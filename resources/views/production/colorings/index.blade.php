@extends('layouts.app', ['title' => 'Riwayat Celup & Warna'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">Celup & Warna</h2>
            <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-widest">Manufaktur</span>
        </div>

        <a href="{{ route('production.colorings.create') }}" class="btn-dark rounded-xl px-4 py-2 text-xs font-bold shadow-sm transition uppercase tracking-widest">
            Inisiasi Baru
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $totalBatches = $batches->count();
            $totalWeight = $batches->sum('total_weight');
            $totalCelup = $batches->where('process_type', 'celup')->count();
            $totalWarna = $batches->where('process_type', 'warna')->count();
        @endphp
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Proses</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalBatches) }}</div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Berat Olahan</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalWeight, 1) }} <span class="font-sans text-xs text-slate-500">kg</span></div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Proses Celup</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalCelup) }} <span class="font-sans text-xs text-slate-500">Lot</span></div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Proses Warna</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalWarna) }} <span class="font-sans text-xs text-slate-500">Lot</span></div>
        </div>
    </div>

    <div class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
        <div class="flex flex-col gap-3 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 uppercase tracking-widest">Riwayat Proses</span>
                <span class="badge-dark rounded-full px-2 py-0.5 text-[11px] font-bold">{{ $batches->count() }} Data</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-xs text-slate-900">
                <thead>
                    <tr class="border-b border-slate-900/10 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">
                        <th class="px-3 py-3">Kode & Tanggal</th>
                        <th class="px-3 py-3">Jenis & Status</th>
                        <th class="px-3 py-3">Bahan Diproses</th>
                        <th class="px-3 py-3">PIC</th>
                        <th class="px-3 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($batches as $batch)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-3 py-3">
                                <div class="font-mono font-bold text-slate-900">{{ $batch->batch_code }}</div>
                                <div class="mt-1 text-[10px] font-bold text-slate-500">{{ $batch->coloring_date->format('d/m/Y') }}</div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex flex-col items-start gap-1">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase tracking-widest {{ $batch->process_type == 'celup' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-fuchsia-100 text-fuchsia-800 border border-fuchsia-200' }}">
                                        {{ $batch->process_type }}
                                    </span>
                                    <span class="inline-block rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest border-slate-300 bg-slate-100 text-slate-900">
                                        {{ str_replace('_', ' ', $batch->status) }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900">{{ $batch->branch->material->name ?? '-' }}</div>
                                <div class="mt-1 font-mono text-[11px] font-bold text-slate-500">{{ number_format($batch->total_weight, 2) }} kg</div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900">{{ $batch->pic_name }}</div>
                            </td>

                            <td class="px-3 py-3 text-right">
                                <a href="{{ route('production.colorings.show', $batch) }}" class="inline-block rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 uppercase tracking-widest">
                                    {{ $batch->status === 'completed' ? 'Lihat Rincian' : 'Lapor Selesai' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada data proses celup & warna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
