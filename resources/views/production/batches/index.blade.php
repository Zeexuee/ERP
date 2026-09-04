@extends('layouts.app', ['title' => 'Proses Produksi'])

@section('content')
<div class="space-y-6">

    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Proses Produksi</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pemantauan SPK manufaktur, tahapan pengerjaan olahan gaharu, dan laporan harian pabrik.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('production.batches.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-semibold shadow-sm flex items-center gap-1.5 transition">
                <span>+ Buat Produksi Baru</span>
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Batch Berjalan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalActive }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Sedang dalam proses pengerjaan</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Target Output Aktif</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totalTargetQty, 1) }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Total kuantitas barang dalam olahan</span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Produksi Selesai</span>
            <div class="text-2xl font-bold text-emerald-700 mt-1">{{ $totalCompleted }}</div>
            <span class="text-[11px] text-slate-400 mt-1 block">Batch sukses lolos Quality Control</span>
        </div>
    </div>

    <!-- Main Table: Daftar Batch Produksi -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-5">
            <!-- Filter Status Tabs -->
            <div class="flex items-center gap-1.5 bg-slate-100/80 p-1 rounded-xl border border-slate-200/80">
                <a href="{{ route('production.batches.index', ['status' => 'all']) }}" class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ request('status', 'all') === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua
                </a>
                <a href="{{ route('production.batches.index', ['status' => 'in_progress']) }}" class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ request('status') === 'in_progress' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Berjalan
                </a>
                <a href="{{ route('production.batches.index', ['status' => 'completed']) }}" class="px-3 py-1 rounded-lg text-xs font-semibold transition {{ request('status') === 'completed' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Selesai
                </a>
            </div>

            <!-- Search Form -->
            <form action="{{ route('production.batches.index') }}" method="GET" class="flex items-center gap-2">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari no batch, produk, PIC..." 
                    class="text-xs px-3 py-1.5 rounded-xl border border-slate-300 bg-white text-slate-800 placeholder-slate-400 w-48 md:w-64 focus:outline-none"
                >
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-semibold hover:bg-slate-900 transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('production.batches.index', ['status' => request('status')]) }}" class="px-2 py-1.5 text-xs text-slate-500 hover:text-slate-800 transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">No. Batch</th>
                        <th class="py-2.5 px-3">Target Produk Jadi</th>
                        <th class="py-2.5 px-3 text-right">Target / Hasil</th>
                        <th class="py-2.5 px-3">Tahap Pengerjaan</th>
                        <th class="py-2.5 px-3">Tanggal Mulai</th>
                        <th class="py-2.5 px-3">PIC Pabrik</th>
                        <th class="py-2.5 px-3 text-center">Status</th>
                        <th class="py-2.5 px-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @forelse ($batches as $b)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-mono font-semibold text-slate-900">
                                <a href="{{ route('production.batches.show', $b) }}" class="hover:underline">
                                    {{ $b->batch_number }}
                                </a>
                                @if($b->productionRequest)
                                    <span class="block text-[9px] text-slate-400 font-sans">
                                        Ref: #{{ $b->productionRequest->request_number }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 font-semibold text-slate-800">
                                <span class="block font-bold text-slate-900">{{ $b->product->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $b->product->sku ?? '' }}</span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                                @if($b->status === 'completed' && $b->actual_quantity !== null)
                                    <span class="text-emerald-700">{{ number_format($b->actual_quantity, 1) }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">/ {{ number_format($b->target_quantity, 1) }} {{ $b->unit }}</span>
                                @else
                                    {{ number_format($b->target_quantity, 1) }} <span class="font-normal font-sans text-[10px] text-slate-500">{{ $b->unit }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-700">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200 inline-block">
                                    {{ $b->stage }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $b->start_date->format('d/m/Y') }}
                                @if($b->target_completion_date)
                                    <span class="block text-[9px] text-slate-400">Est: {{ $b->target_completion_date->format('d/m/Y') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-700">
                                {{ $b->pic_name }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($b->status === 'in_progress')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-900 text-white shadow-sm">
                                        Berjalan
                                    </span>
                                @elseif($b->status === 'completed')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Selesai
                                    </span>
                                @elseif($b->status === 'cancelled')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700 border border-red-200">
                                        Batal
                                    </span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-700">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-right">
                                <a href="{{ route('production.batches.show', $b) }}" class="px-3 py-1 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-sm transition inline-block">
                                    Detail & Log
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400">
                                Belum ada proses produksi yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($batches->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-900/10">
                {{ $batches->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
