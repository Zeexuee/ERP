@extends('layouts.app', ['title' => 'Papan Proses Produksi'])

@section('content')
@php
    // Kelas ditulis literal di sini supaya tetap terbaca oleh Tailwind.
    $accentStyles = [
        'slate' => ['bar' => 'bg-slate-400', 'dot' => 'bg-slate-500', 'count' => 'bg-slate-100 text-slate-800 border-slate-200'],
        'amber' => ['bar' => 'bg-amber-400', 'dot' => 'bg-amber-500', 'count' => 'bg-amber-50 text-amber-800 border-amber-200'],
        'sky' => ['bar' => 'bg-sky-400', 'dot' => 'bg-sky-500', 'count' => 'bg-sky-50 text-sky-800 border-sky-200'],
        'indigo' => ['bar' => 'bg-indigo-400', 'dot' => 'bg-indigo-500', 'count' => 'bg-indigo-50 text-indigo-800 border-indigo-200'],
        'orange' => ['bar' => 'bg-orange-400', 'dot' => 'bg-orange-500', 'count' => 'bg-orange-50 text-orange-800 border-orange-200'],
        'violet' => ['bar' => 'bg-violet-400', 'dot' => 'bg-violet-500', 'count' => 'bg-violet-50 text-violet-800 border-violet-200'],
        'emerald' => ['bar' => 'bg-emerald-400', 'dot' => 'bg-emerald-500', 'count' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900 uppercase tracking-widest">Papan Proses</h2>
                <span class="rounded-full bg-slate-900 px-2.5 py-0.5 text-[10px] font-bold text-white uppercase tracking-widest">Live</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Semua pekerjaan yang sedang berjalan di setiap tahap, dalam satu halaman.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.kanban') }}" class="rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 uppercase tracking-widest">
                Muat Ulang
            </a>
            <a href="{{ route('production.dashboard') }}" class="btn-dark rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm transition uppercase tracking-widest">
                Dashboard
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Total Pekerjaan Aktif</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ number_format($summary['total_active']) }}</div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tahap Yang Sedang Berjalan</span>
            <div class="mt-1 font-mono text-2xl font-bold text-slate-900">
                {{ $summary['active_stage_count'] }}<span class="text-sm text-slate-400">/{{ $summary['stage_count'] }}</span>
            </div>
        </div>
        <div class="apple-glass-card rounded-2xl p-5">
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Paling Lama Berjalan</span>
            @if($summary['longest_running'])
                <div class="mt-1 font-mono text-2xl font-bold text-slate-900">{{ $summary['longest_running']['age_days'] }} <span class="font-sans text-xs text-slate-500">hari</span></div>
                <span class="mt-0.5 block truncate font-mono text-[10px] font-bold text-slate-500">{{ $summary['longest_running']['code'] }}</span>
            @else
                <div class="mt-1 font-mono text-2xl font-bold text-slate-900">0 <span class="font-sans text-xs text-slate-500">hari</span></div>
                <span class="mt-0.5 block text-[10px] font-bold text-slate-500 uppercase tracking-widest">Tidak ada proses aktif</span>
            @endif
        </div>
    </div>

    @if($summary['total_active'] === 0)
        <div class="apple-glass-panel rounded-3xl p-10 text-center shadow-md">
            <p class="text-sm font-bold text-slate-900 uppercase tracking-widest">Tidak ada proses yang sedang berjalan</p>
            <p class="mt-2 text-xs text-slate-500">Mulai sesi baru dari salah satu modul produksi untuk melihatnya muncul di papan ini.</p>
        </div>
    @endif

    <div class="overflow-x-auto pb-3">
        <div class="flex items-start gap-4" style="min-width: min-content;">
            @foreach($columns as $column)
                @php $accent = $accentStyles[$column['accent']] ?? $accentStyles['slate']; @endphp
                <section class="apple-glass-panel flex w-[300px] shrink-0 flex-col overflow-hidden rounded-3xl shadow-md">
                    <div class="{{ $accent['bar'] }} h-1.5 w-full"></div>

                    <div class="space-y-3 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="{{ $accent['dot'] }} size-2 shrink-0 rounded-full"></span>
                                    <h3 class="truncate text-xs font-bold text-slate-900 uppercase tracking-widest">{{ $column['title'] }}</h3>
                                </div>
                                <p class="mt-1 text-[10px] leading-relaxed text-slate-500">{{ $column['subtitle'] }}</p>
                            </div>
                            <span class="{{ $accent['count'] }} shrink-0 rounded-full border px-2 py-0.5 font-mono text-[10px] font-bold">
                                {{ count($column['cards']) }}
                            </span>
                        </div>

                        <div class="space-y-2.5">
                            @forelse($column['cards'] as $card)
                                <a href="{{ $card['url'] }}" class="block rounded-2xl border border-slate-200 bg-white p-3 shadow-xs transition hover:border-slate-400 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-mono text-[11px] font-bold text-slate-900">{{ $card['code'] }}</span>
                                        <span class="shrink-0 rounded-full bg-slate-100 px-1.5 py-0.5 font-mono text-[9px] font-bold text-slate-600">
                                            {{ $card['age_days'] }}h
                                        </span>
                                    </div>

                                    <p class="mt-1 line-clamp-2 text-[11px] font-bold text-slate-700">{{ $card['title'] }}</p>

                                    <span class="mt-1.5 inline-block rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[9px] font-bold text-slate-700 uppercase tracking-widest">
                                        {{ $card['badge'] }}
                                    </span>

                                    @if($card['branch_code'])
                                        <span class="mt-1.5 block truncate font-mono text-[9px] font-bold text-indigo-700">
                                            {{ \Illuminate\Support\Str::startsWith($card['branch_code'], 'MSK-') ? 'Kode Masak' : 'Branch' }}
                                            {{ $card['branch_code'] }}
                                        </span>
                                    @endif

                                    @if($card['meta'] !== [])
                                        <dl class="mt-2 space-y-1 border-t border-slate-100 pt-2">
                                            @foreach($card['meta'] as $metaLabel => $metaValue)
                                                <div class="flex items-center justify-between gap-2">
                                                    <dt class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $metaLabel }}</dt>
                                                    <dd class="font-mono text-[10px] font-bold text-slate-900">{{ $metaValue }}</dd>
                                                </div>
                                            @endforeach
                                        </dl>
                                    @endif

                                    <div class="mt-2 flex items-center justify-between gap-2 border-t border-slate-100 pt-2">
                                        <span class="truncate text-[9px] font-bold text-slate-500 uppercase tracking-widest">
                                            {{ $card['pic_name'] ?? 'Tanpa PIC' }}
                                        </span>
                                        <span class="shrink-0 font-mono text-[9px] font-bold text-slate-400">
                                            {{ $card['started_at']?->format('d/m/Y') ?? '-' }}
                                        </span>
                                    </div>
                                </a>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 p-4 text-center">
                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Tidak ada proses</span>
                                </div>
                            @endforelse
                        </div>

                        <a href="{{ $column['index_url'] }}" class="block rounded-xl border border-slate-300 bg-white py-2 text-center text-[10px] font-bold text-slate-900 shadow-xs transition hover:bg-slate-100 uppercase tracking-widest">
                            Buka Modul
                        </a>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
@endsection
