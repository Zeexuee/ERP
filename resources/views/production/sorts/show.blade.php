@extends('layouts.app', ['title' => 'Rincian Sortir #' . $sortBatch->sort_code])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Rincian Sortir #{{ $sortBatch->sort_code }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Selesai & Masuk Stok Gudang
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">
                Tanggal Sortir: {{ $sortBatch->sort_date->format('d F Y') }} • PIC: <strong class="text-slate-800">{{ $sortBatch->pic_name }}</strong>
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.sorts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Kembali ke Riwayat
            </a>
            <a href="{{ route('production.sorts.create') }}" class="px-3.5 py-2 rounded-xl btn-dark text-xs font-semibold shadow-xs transition">
                + Sortir Baru
            </a>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Mentah Asal</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $sortBatch->sourceMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono text-slate-500 block mt-0.5">
                Berat Awal: <strong>{{ number_format($sortBatch->initial_weight, 1) }} kg</strong>
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Hasil Penjemuran</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                {{ number_format($sortBatch->dried_weight, 1) }} kg
            </div>
            <span class="text-xs text-slate-400 block mt-0.5">
                Susut Jemur: <strong>{{ number_format($sortBatch->drying_loss_weight, 1) }} kg</strong>
                ({{ $sortBatch->initial_weight > 0 ? round(($sortBatch->drying_loss_weight / $sortBatch->initial_weight) * 100, 1) : 0 }}%)
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Varian Bahan Dihasilkan</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                {{ $sortBatch->items->count() }} Jenis
            </div>
            <span class="text-xs text-slate-400 block mt-0.5">
                Siap digunakan di tahap tembak
            </span>
        </div>
    </div>

    <!-- Table Rincian Hasil Sortir -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-4">
        <h3 class="text-sm font-bold text-slate-900 border-b border-slate-900/10 pb-2">
            Rincian Pembagian Hasil Sortir ke Gudang
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-400 font-extrabold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-3">Kode Bahan</th>
                        <th class="py-3 px-3">Nama Bahan Kayu Tembak / Afkir</th>
                        <th class="py-3 px-3 text-right">Hasil Timbang</th>
                        <th class="py-3 px-3 text-right">Porsi (%)</th>
                        <th class="py-3 px-3">Catatan / Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @php $totalHasil = 0; @endphp
                    @foreach($sortBatch->items as $item)
                        @php 
                            $totalHasil += $item->result_weight;
                            $porsi = $sortBatch->dried_weight > 0 ? round(($item->result_weight / $sortBatch->dried_weight) * 100, 1) : 0;
                        @endphp
                        <tr class="hover:bg-white/50 transition">
                            <td class="py-3 px-3 font-mono font-bold text-slate-600">
                                {{ $item->targetMaterial->code ?? '-' }}
                            </td>
                            <td class="py-3 px-3">
                                <div class="font-bold text-slate-900">{{ $item->targetMaterial->name ?? '-' }}</div>
                                <span class="text-[10px] text-slate-400">Kategori: {{ $item->targetMaterial->category ?? 'Kayu Dasar' }}</span>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-bold text-slate-900 text-sm">
                                {{ number_format($item->result_weight, 1) }} kg
                            </td>
                            <td class="py-3 px-3 text-right font-mono text-slate-500">
                                {{ $porsi }}%
                            </td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $item->notes ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-900/20 font-bold bg-slate-50/50">
                        <td colspan="2" class="py-3 px-3 text-right uppercase text-[10px] tracking-wider text-slate-500">
                            Total Hasil Sortir Terbagi:
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-900 text-sm">
                            {{ number_format($totalHasil, 1) }} kg
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-900">
                            100%
                        </td>
                        <td class="py-3 px-3 text-emerald-700 text-[10px]">
                            ✓ Balance dengan berat jemur ({{ number_format($sortBatch->dried_weight, 1) }} kg)
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Catatan & Tanda Tangan PIC -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Catatan Sortir</span>
            <p class="text-xs text-slate-700 leading-relaxed pt-1">
                {{ $sortBatch->notes ?: 'Tidak ada catatan tambahan untuk proses sortir ini.' }}
            </p>
        </div>

        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tanda Tangan Validasi PIC</span>
            <div class="pt-2">
                @if($sortBatch->signature_path)
                    <div class="p-2 bg-white border border-slate-200 rounded-2xl shadow-xs inline-block">
                        @php
                            $sigSrc = \Illuminate\Support\Str::startsWith($sortBatch->signature_path, ['data:image', 'http://', 'https://']) 
                                ? $sortBatch->signature_path 
                                : (\Illuminate\Support\Str::startsWith($sortBatch->signature_path, 'storage/') 
                                    ? asset($sortBatch->signature_path) 
                                    : asset('storage/' . $sortBatch->signature_path));
                        @endphp
                        <img src="{{ $sigSrc }}" alt="Tanda Tangan PIC" class="h-16 w-auto object-contain" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\'text-[10px] text-slate-400 italic px-2\'>Tanda tangan tersimpan di sistem</span>';">
                    </div>
                @else
                    <div class="text-xs text-slate-400 italic">
                        Tanda tangan digital tidak disertakan.
                    </div>
                @endif
                <span class="text-[10px] text-slate-500 block mt-1 font-semibold">
                    Petugas: {{ $sortBatch->pic_name }}
                </span>
            </div>
        </div>
    </div>

</div>
@endsection
