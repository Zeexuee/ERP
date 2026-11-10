@extends('layouts.app', ['title' => 'Warehouse - Potong Ukir'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Potong Ukir
                </span>
            </div>
            <p class="text-xs text-slate-600 mt-1">Pemotongan, pengukiran, dan pembagian kualitas kayu ke ukuran produksi.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.carves.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-bold shadow-sm transition inline-block">
                + Inisiasi Potong Ukir
            </a>
        </div>
    </div>

    <!-- 1. PROSES BERJALAN -->
    <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900">Proses Potong Ukir Berjalan</span>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-900 text-white">
                    {{ $ongoingBatches->total() }} Sedang Dikerjakan
                </span>
            </div>
            <p class="text-xs text-slate-600">Batch potong ukir kayu aktif yang menunggu pencatatan laporan hasil.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3.5">Kode Potong Ukir</th>
                        <th class="py-3 px-3.5">Tanggal Inisiasi</th>
                        <th class="py-3 px-3.5">Bahan Mentah Asal</th>
                        <th class="py-3 px-3.5">Berat Diambil</th>
                        <th class="py-3 px-3.5">PIC / Petugas</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($ongoingBatches as $batch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3.5">
                                <span class="font-bold font-mono text-slate-900 text-xs block">{{ $batch->carve_code }}</span>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    Sedang Dikerjakan
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->carve_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                <span class="text-[11px] text-slate-600 font-mono">Kode: {{ $batch->sourceMaterial->code ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="font-mono font-bold text-slate-900 text-xs bg-slate-100 border border-slate-300 px-2 py-0.5 rounded-md inline-block">
                                    {{ number_format($batch->initial_weight, 1) }} {{ $batch->sourceMaterial->unit ?? 'kg' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->pic_name }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right">
                                <a href="{{ route('production.carves.show', $batch) }}" class="px-3.5 py-1.5 rounded-xl btn-dark font-bold text-xs shadow-xs transition inline-block whitespace-nowrap text-center">
                                    Catat Laporan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-600 text-xs">
                                Tidak ada proses potong ukir yang sedang berjalan. Klik tombol <strong>+ Inisiasi Potong Ukir</strong> untuk mulai.
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
                <span class="text-sm font-bold text-slate-900">Riwayat Potong Ukir Selesai</span>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-800 border border-slate-300">
                    {{ $completedBatches->total() }} Data
                </span>
            </div>

            <form method="GET" action="{{ route('production.carves.index') }}" class="flex items-center gap-2">
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
                    <a href="{{ route('production.carves.index') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 underline">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3.5">Kode Potong Ukir</th>
                        <th class="py-3 px-3.5">Tanggal</th>
                        <th class="py-3 px-3.5">Bahan Mentah Asal</th>
                        <th class="py-3 px-3.5">Hasil & Susut</th>
                        <th class="py-3 px-3.5">Alokasi Bahan</th>
                        <th class="py-3 px-3.5">PIC</th>
                        <th class="py-3 px-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($completedBatches as $batch)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3.5">
                                <span class="font-bold font-mono text-slate-900 text-xs block">{{ $batch->carve_code }}</span>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900 text-white">
                                    Selesai
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                <div>Inisiasi: {{ $batch->carve_date->format('d/m/Y') }}</div>
                                @if($batch->report_date)
                                    <div class="text-[11px] text-slate-600 mt-0.5">Selesai: {{ $batch->report_date->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $batch->sourceMaterial->name ?? '-' }}</div>
                                <span class="text-[11px] text-slate-700 font-mono font-semibold">
                                    Diambil: {{ number_format($batch->initial_weight, 1) }} kg
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5">
                                <div class="font-bold text-slate-900 font-mono text-xs">
                                    Hasil: {{ number_format($batch->dried_weight, 1) }} kg
                                </div>
                                <div class="text-[11px] text-slate-700 font-mono mt-0.5">
                                    Susut: {{ number_format($batch->drying_loss_weight, 1) }} kg
                                </div>
                            </td>
                            <td class="py-3.5 px-3.5">
                                <div class="flex flex-wrap gap-1.5 max-w-sm">
                                    @foreach($batch->items as $item)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-300">
                                            <span>{{ $item->targetMaterial->name ?? '-' }}:</span>
                                            <strong class="font-mono text-slate-900">{{ number_format($item->result_weight, 1) }} kg</strong>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $batch->report_pic_name ?? $batch->pic_name }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right">
                                <a href="{{ route('production.carves.show', $batch) }}" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-900 border border-slate-300 font-bold text-xs shadow-xs transition inline-block whitespace-nowrap text-center">
                                    Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-600 text-xs">
                                Belum ada riwayat potong ukir yang selesai.
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
