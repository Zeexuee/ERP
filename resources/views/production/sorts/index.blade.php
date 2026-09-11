@extends('layouts.app', ['title' => 'Proses Sortir Kayu Mandiri'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Proses Sortir Kayu Tembak</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white shadow-xs">
                    Mandiri / Gudang
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Pencatatan alur jemur bahan baku mentah dan pemisahan hasil sortir menjadi beberapa grade bahan kayu siap tembak.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.sorts.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition">
                + Catat Proses Sortir Baru
            </a>
        </div>
    </div>

    <!-- Overview Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Sesi Sortir</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalBatches) }}
            </div>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Sesi pengolahan mandiri</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Mentah Masuk</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalRawProcessed, 1) }} <span class="text-xs font-sans font-normal text-slate-500">kg</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Sebelum proses penjemuran</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Tembak Jadi</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalDriedProduced, 1) }} <span class="text-xs font-sans font-normal text-slate-500">kg</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Siap dialokasikan ke proses tembak</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Susut Jemur</span>
            <div class="text-2xl font-bold text-slate-900 mt-1 font-mono">
                {{ number_format($totalLoss, 1) }} <span class="text-xs font-sans font-normal text-slate-500">kg</span>
            </div>
            <span class="text-[10px] text-slate-400 mt-0.5 block">Pengurangan kadar air alami</span>
        </div>
    </div>

    <!-- Table Container -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-900/10 pb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900">Riwayat Sortir & Penjemuran</span>
                <span class="px-2 py-0.5 text-[11px] font-bold rounded-full badge-dark">
                    {{ $sortBatches->total() }} Sesi
                </span>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('production.sorts.index') }}" class="flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari kode sortir, PIC, bahan..." 
                    class="px-3 py-1.5 rounded-xl apple-input text-xs w-64"
                >
                @if(request('search'))
                    <a href="{{ route('production.sorts.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-400 font-extrabold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-3">Kode Sortir</th>
                        <th class="py-3 px-3">Tanggal</th>
                        <th class="py-3 px-3">Bahan Mentah Asal</th>
                        <th class="py-3 px-3">Hasil Jemur & Susut</th>
                        <th class="py-3 px-3">Alokasi Bahan Kayu Tembak</th>
                        <th class="py-3 px-3">PIC</th>
                        <th class="py-3 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sortBatches as $batch)
                        <tr class="hover:bg-white/50 transition">
                            <td class="py-3 px-3 font-bold font-mono text-slate-900">
                                {{ $batch->sort_code }}
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $batch->sort_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                <span class="text-[10px] text-slate-500 font-mono">Beli/Awal: {{ number_format($batch->initial_weight, 1) }} kg</span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900 font-mono">
                                    {{ number_format($batch->dried_weight, 1) }} kg
                                </div>
                                <span class="text-[10px] text-slate-400">
                                    Susut: {{ number_format($batch->drying_loss_weight, 1) }} kg 
                                    ({{ $batch->initial_weight > 0 ? round(($batch->drying_loss_weight / $batch->initial_weight) * 100, 1) : 0 }}%)
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <div class="flex flex-wrap gap-1.5 max-w-sm">
                                    @foreach($batch->items as $item)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            <span>{{ $item->targetMaterial->name ?? '-' }}:</span>
                                            <strong class="font-mono">{{ number_format($item->result_weight, 1) }} kg</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 px-3 text-slate-700">
                                {{ $batch->pic_name }}
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('production.sorts.show', $batch) }}" class="px-3 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 font-semibold text-xs shadow-xs transition">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada riwayat proses sortir kayu. Klik tombol "+ Catat Proses Sortir Baru" untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sortBatches->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $sortBatches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
