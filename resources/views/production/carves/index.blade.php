@extends('layouts.app', ['title' => 'Warehouse - Potong Ukir'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Potong Ukir
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">Pemotongan, pengukiran, dan pembagian kualitas kayu.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.carves.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm transition">
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
                    <span class="text-sm font-bold text-slate-900">Proses Potong Ukir Berjalan</span>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                        {{ $ongoingBatches->total() }} Data
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="border-b border-blue-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-3">Kode Potong Ukir</th>
                            <th class="py-3 px-3">Tanggal Inisiasi</th>
                            <th class="py-3 px-3">Bahan Mentah Asal</th>
                            <th class="py-3 px-3">PIC</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-900/5">
                        @forelse($ongoingBatches as $batch)
                            <tr class="hover:bg-white/60 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold font-mono text-slate-900">{{ $batch->carve_code }}</div>
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Sedang Dikerjakan
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $batch->carve_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ number_format($batch->initial_weight, 1) }} {{ $batch->sourceMaterial->unit ?? 'kg' }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    {{ $batch->pic_name }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('production.carves.show', $batch) }}" class="px-3 py-1.5 rounded-xl btn-dark font-semibold text-[11px] shadow-xs transition inline-block whitespace-nowrap text-center">
                                        Catat Laporan
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                    Tidak ada proses potong ukir yang sedang berjalan.
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
                    <span class="text-sm font-bold text-slate-900">Riwayat Potong Ukir Selesai</span>
                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-full badge-dark">
                        {{ $completedBatches->total() }} Data
                    </span>
                </div>

                <form method="GET" action="{{ route('production.carves.index') }}" class="flex items-center gap-2">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        class="px-3 py-1.5 rounded-xl apple-input text-xs w-64"
                        placeholder="Cari Riwayat..."
                    >
                    @if(request('search'))
                        <a href="{{ route('production.carves.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline">Reset</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-800">
                    <thead>
                        <tr class="border-b border-slate-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                            <th class="py-3 px-3">Kode Potong Ukir</th>
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Bahan Mentah Asal</th>
                            <th class="py-3 px-3">Hasil & Susut</th>
                            <th class="py-3 px-3">Alokasi Bahan</th>
                            <th class="py-3 px-3">PIC</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($completedBatches as $batch)
                            <tr class="hover:bg-white/50 transition">
                                <td class="py-3 px-3">
                                    <div class="font-bold font-mono text-slate-900">{{ $batch->carve_code }}</div>
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[9px] font-bold bg-slate-900 text-white">
                                        Selesai
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600">
                                    {{ $batch->carve_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ number_format($batch->initial_weight, 1) }} kg</span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 font-mono">
                                        {{ number_format($batch->dried_weight, 1) }} kg
                                    </div>
                                    <span class="text-[10px] text-slate-500 font-mono">
                                        Susut: {{ number_format($batch->drying_loss_weight, 1) }} kg
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="flex flex-wrap gap-1.5 max-w-sm">
                                        @foreach($batch->items as $item)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-300">
                                                <span>{{ $item->targetMaterial->name ?? '-' }}:</span>
                                                <strong class="font-mono">{{ number_format($item->result_weight, 1) }} kg</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    {{ $batch->report_pic_name ?? $batch->pic_name }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('production.carves.show', $batch) }}" class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 font-semibold text-[11px] shadow-xs transition inline-block whitespace-nowrap text-center">
                                        Rincian
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                    Belum ada riwayat potong ukir yang selesai.
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
