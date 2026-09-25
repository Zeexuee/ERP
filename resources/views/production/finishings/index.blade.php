@extends('layouts.app', ['title' => 'Riwayat Proses Finishing'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2">
            <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">Finishing</h2>
            <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-widest">Manufaktur</span>
        </div>

        <a href="{{ route('production.finishings.create') }}" class="btn-dark rounded-xl px-4 py-2 text-xs font-bold shadow-sm transition uppercase tracking-widest">
            Mulai Finishing Baru
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-xs font-bold text-emerald-800 shadow-xs">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-xs font-bold text-red-800 shadow-xs">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Sesi Finishing</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalSessions) }}</div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Sedang Berjalan</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($activeSessions) }}</div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Proses Tercatat</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($totalSteps) }}</div>
        </div>
        <div class="apple-glass-card rounded-2xl bg-slate-900 p-5 text-white">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Barang Jadi Dihasilkan</span>
            <div class="mt-1 font-mono text-2xl font-bold">{{ number_format($totalOutputWeight, 1) }} <span class="font-sans text-xs text-slate-400">kg</span></div>
        </div>
    </div>

    <div class="apple-glass-panel space-y-4 rounded-3xl p-6 shadow-md">
        <div class="flex flex-col gap-3 border-b border-slate-900/10 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-slate-900 uppercase tracking-widest">Riwayat Finishing</span>
                <span class="badge-dark rounded-full px-2 py-0.5 text-[11px] font-bold">{{ $finishingSessions->total() }} Data</span>
            </div>

            <form method="GET" action="{{ route('production.finishings.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" class="apple-input w-64 rounded-xl px-3 py-1.5 text-xs font-bold" placeholder="Cari sesi, branch, PIC, atau barang...">
                @if(request('search'))
                    <a href="{{ route('production.finishings.index') }}" class="text-[10px] font-bold text-slate-500 underline hover:text-slate-900 uppercase tracking-widest">Reset</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[980px] text-left text-xs text-slate-900">
                <thead>
                    <tr class="border-b border-slate-900/10 text-[10px] font-extrabold text-slate-500 uppercase tracking-widest">
                        <th class="px-3 py-3">Kode & Status</th>
                        <th class="px-3 py-3">Branch</th>
                        <th class="px-3 py-3">Bahan Asal</th>
                        <th class="px-3 py-3">Progres Berat</th>
                        <th class="px-3 py-3">Hasil / PIC</th>
                        <th class="px-3 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($finishingSessions as $session)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-3 py-3">
                                <div class="font-mono font-bold text-slate-900">{{ $session->finishing_code }}</div>
                                <div class="mb-1.5 text-[10px] font-bold text-slate-500">{{ $session->finishing_date?->format('d/m/Y') ?? '-' }}</div>
                                <span class="inline-block rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest
                                    {{ $session->isCompleted()
                                        ? 'border-slate-900 bg-slate-900 text-white'
                                        : 'border-amber-300 bg-amber-50 text-amber-800' }}">
                                    {{ $session->status_label }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-mono text-[11px] font-bold text-slate-900">{{ $session->branch_code ?? '-' }}</div>
                                @if($session->branch_is_merged)
                                    <span class="mt-1 inline-block rounded-full border border-indigo-300 bg-indigo-50 px-2 py-0.5 text-[9px] font-bold text-indigo-800 uppercase tracking-widest">Gabungan</span>
                                    <div class="mt-1 text-[9px] font-bold text-slate-500">{{ implode(' + ', $session->source_branch_codes ?? []) }}</div>
                                @endif
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-bold text-slate-900">{{ $session->sourceMaterial?->name ?? '-' }}</div>
                                <div class="mt-1 font-mono text-[11px] font-bold text-slate-500">{{ number_format($session->initial_weight, 2) }} kg masuk</div>
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-mono text-xs font-bold text-slate-900">{{ number_format($session->initial_weight, 2) }} → {{ number_format($session->current_weight, 2) }} kg</div>
                                <div class="mt-1 text-[10px] font-bold text-slate-500">
                                    Susut {{ number_format($session->total_weight_loss, 2) }} kg
                                    @if($session->latestStep)
                                        · Terakhir {{ $session->latestStep->process_type->label() }}
                                    @else
                                        · Belum ada proses
                                    @endif
                                </div>
                            </td>

                            <td class="px-3 py-3">
                                @if($session->isCompleted())
                                    <div class="font-bold text-slate-900">{{ $session->product?->name ?? 'Barang jadi tidak tersedia' }}</div>
                                    <div class="mt-1 font-mono text-[10px] font-bold text-slate-500">{{ number_format($session->output_quantity) }} {{ $session->product?->unit ?? 'unit' }}</div>
                                @else
                                    <div class="font-bold text-slate-900">{{ $session->pic_name }}</div>
                                    <span class="mt-1 block text-[10px] font-bold italic text-slate-400 uppercase tracking-widest">Menunggu pernyataan selesai</span>
                                @endif
                            </td>

                            <td class="px-3 py-3 text-right">
                                @if(! $session->isCompleted())
                                    <a href="{{ route('production.finishings.show', $session) }}" class="btn-dark mr-1 inline-block rounded-xl px-3 py-1.5 text-[10px] font-bold shadow-xs transition uppercase tracking-widest">
                                        Catat Proses
                                    </a>
                                @endif
                                <a href="{{ route('production.finishings.show', $session) }}" class="inline-block rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 uppercase tracking-widest">
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 text-center text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada sesi finishing.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($finishingSessions->hasPages())
            <div class="border-t border-slate-100 pt-4">{{ $finishingSessions->links() }}</div>
        @endif
    </div>
</div>
@endsection
