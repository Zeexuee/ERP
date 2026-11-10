@extends('layouts.app', ['title' => 'Warehouse - Jemur Bahan Baku'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Warehouse</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">
                    Jemur Bahan Baku
                </span>
            </div>
            <p class="text-xs text-slate-600 mt-1">Proses pengeringan (jemur) bahan baku gudang & pencatatan penyusutan berat.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('production.dryings.create') }}" class="px-4 py-2 rounded-xl btn-dark text-xs font-bold shadow-sm transition inline-block">
                + Inisiasi Jemur Bahan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-white border border-slate-300 text-slate-900 text-xs font-semibold shadow-xs">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-white border-2 border-slate-900 text-slate-900 text-xs font-semibold shadow-xs">
            <div class="font-bold mb-1 uppercase tracking-wider">Pemberitahuan:</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. Proses Jemur Berjalan -->
    <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Proses Jemur Berjalan</h3>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-900 text-white">
                    {{ $activeLogs->count() }} Sedang Dijemur
                </span>
            </div>
            <p class="text-xs text-slate-600">Daftar bahan baku yang saat ini aktif dalam proses penjemuran.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3.5">Tanggal Mulai</th>
                        <th class="py-3 px-3.5">Waktu Jemur</th>
                        <th class="py-3 px-3.5">Bahan Baku</th>
                        <th class="py-3 px-3.5 text-right">Berat Basah Awal</th>
                        <th class="py-3 px-3.5">Penanggung Jawab</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-3.5">Catatan</th>
                        <th class="py-3 px-3.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($activeLogs as $activeLog)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3.5 font-mono font-bold text-slate-900">
                                {{ $activeLog->start_date->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-3.5 font-mono text-slate-800 font-medium">
                                {{ $activeLog->start_time ?? '-' }} WIB
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="font-bold text-slate-900 block text-xs">{{ $activeLog->material->name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-600 font-mono">{{ $activeLog->material->code ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-mono font-bold text-slate-900">
                                {{ number_format($activeLog->previous_weight, 2) }} {{ $activeLog->material->unit ?? 'kg' }}
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $activeLog->pic_name }}
                            </td>
                            <td class="py-3.5 px-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                                    Menjemur
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-600 text-xs max-w-xs truncate" title="{{ $activeLog->notes }}">
                                {{ $activeLog->notes ?? '-' }}
                            </td>
                            <td class="py-3.5 px-3.5 text-center">
                                <a 
                                    href="{{ route('production.dryings.report.create', $activeLog->id) }}" 
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold btn-dark shadow-xs transition inline-block"
                                >
                                    Lapor Selesai
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-600 text-xs">
                                Tidak ada proses jemur bahan baku yang sedang berjalan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Riwayat Proses Jemur -->
    <div class="bg-white rounded-2xl border border-slate-300 p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Riwayat Proses Jemur</h3>
                <p class="text-xs text-slate-600 mt-0.5">Log pencatatan pengeringan bahan baku gudang yang telah selesai.</p>
            </div>

            <form action="{{ route('production.dryings.index') }}" method="GET" class="flex items-center gap-2">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari kode/nama bahan..." 
                    class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-300 text-xs font-semibold text-slate-900 placeholder:text-slate-500 w-52 md:w-64 focus:border-slate-900 focus:outline-none"
                >
                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('production.dryings.index') }}" class="px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900 underline">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-100 text-slate-800 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                        <th class="py-3 px-3.5">Tanggal</th>
                        <th class="py-3 px-3.5">Bahan Baku</th>
                        <th class="py-3 px-3.5 text-right">Berat Sebelum</th>
                        <th class="py-3 px-3.5 text-right">Berat Setelah</th>
                        <th class="py-3 px-3.5 text-center">Status</th>
                        <th class="py-3 px-3.5">Penanggung Jawab</th>
                        <th class="py-3 px-3.5">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-3.5">
                                <span class="font-mono text-slate-900 font-bold text-xs">{{ $log->start_date->format('d/m/Y') }}</span>
                                @if($log->end_date)
                                    <span class="block text-[11px] text-slate-600 font-mono mt-0.5">Selesai: {{ $log->end_date->format('d/m/Y') }}</span>
                                @endif
                                @if($log->drying_hours !== null)
                                    <span class="block text-[11px] text-slate-700 font-mono font-bold mt-0.5">
                                        {{ (float) $log->drying_hours }} Jam
                                        @if($log->start_time && $log->end_time)
                                            ({{ $log->start_time }} - {{ $log->end_time }})
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5">
                                <span class="font-bold text-slate-900 block text-xs">{{ $log->material->name ?? '-' }}</span>
                                <span class="text-[11px] text-slate-600 font-mono">{{ $log->material->code ?? '' }}</span>
                            </td>
                            <td class="py-3.5 px-3.5 text-right font-mono font-semibold text-slate-700">
                                {{ number_format($log->previous_weight, 2) }} {{ $log->material->unit ?? 'kg' }}
                            </td>
                            <td class="py-3.5 px-3.5 text-right">
                                @if($log->new_weight !== null)
                                    <span class="font-mono font-bold text-slate-900 text-xs">{{ number_format($log->new_weight, 2) }} {{ $log->material->unit ?? 'kg' }}</span>
                                    @if((float) $log->shrinkage_weight > 0)
                                        <span class="block text-[11px] text-slate-600 font-mono mt-0.5 font-semibold">Susut: {{ number_format($log->shrinkage_weight, 2) }} {{ $log->material->unit ?? 'kg' }}</span>
                                    @endif
                                @else
                                    <span class="font-mono text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-3.5 text-center">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900 text-white">
                                    Selesai
                                </span>
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-800 font-medium text-xs">
                                {{ $log->pic_name }}
                            </td>
                            <td class="py-3.5 px-3.5 text-slate-600 text-xs max-w-xs truncate" title="{{ $log->notes }}">
                                {{ $log->notes ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-600 text-xs">
                                Belum ada riwayat proses jemur bahan baku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="mt-4 pt-4 border-t border-slate-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
