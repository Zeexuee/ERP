@extends('layouts.app', ['title' => 'Detail Pencairan & Pemantauan'])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-xl font-bold text-slate-900">Detail Pencairan #{{ $meltBatch->melt_code }}</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    Warehouse
                </span>
                @if($meltBatch->status === 'in_progress')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                        Dicairkan
                    </span>
                @elseif($meltBatch->status === 'monitoring')
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-300">
                        Pemantauan Aktif
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-600 mt-1">
                Inisiasi: {{ $meltBatch->melt_date->format('d/m/Y') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('production.melts.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-300 text-xs font-semibold shadow-xs transition">
                Kembali
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300">
            <div class="flex items-center gap-3">
                <p class="text-xs font-bold text-slate-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-slate-100 border border-slate-300">
            <div class="flex items-start gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Terdapat Kesalahan</h3>
                    <ul class="mt-1 text-xs text-slate-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @php
        $targetMaterial = $meltBatch->targetMaterial;
        $currentStock = $targetMaterial ? (float) $targetMaterial->stock_quantity : (float) ($meltBatch->current_weight ?? 0);
        $initialLiquid = (float) ($meltBatch->initial_liquid_weight ?? 0);
        $evaporated = $initialLiquid > 0 ? max(0, $initialLiquid - $currentStock) : 0;
        $evaporationPct = $initialLiquid > 0 ? ($evaporated / $initialLiquid * 100) : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Bahan Mentah Asal</span>
            <div class="text-sm font-bold text-slate-900 mt-1">
                {{ $meltBatch->sourceMaterial->name ?? '-' }}
            </div>
            <span class="text-xs font-mono text-slate-600 block mt-0.5">
                Berat Awal: {{ number_format($meltBatch->initial_weight, 1) }} kg
            </span>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Kondisi Penguapan</span>
            @if($initialLiquid > 0)
                <div class="text-xl font-bold font-mono text-slate-900 mt-1">{{ number_format($currentStock, 2) }} kg</div>
                <span class="text-xs font-mono text-slate-600 block mt-0.5">
                    Berat Cairan Awal: {{ number_format($initialLiquid, 2) }} kg
                </span>
                <span class="text-xs font-mono text-slate-600 block">
                    Susut: {{ number_format($evaporated, 2) }} kg ({{ number_format($evaporationPct, 1) }}%)
                </span>
            @else
                <div class="text-sm font-bold font-mono text-slate-400 mt-1 italic">Menunggu laporan pencairan...</div>
            @endif
        </div>
    </div>

    @if($meltBatch->status === 'in_progress')
        <!-- FORM LAPORAN PENCAIRAN AWAL -->
        <div class="apple-glass-panel rounded-3xl p-6 shadow-md space-y-6">
            <div class="border-b border-slate-900/10 pb-3 mb-4">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-900 text-white">Tahap 2</span>
                    <h3 class="text-sm font-bold text-slate-900">Laporan Hasil Pencairan (Awal)</h3>
                </div>
                <p class="text-[11px] text-slate-600 mt-1 font-medium leading-relaxed">
                    Catat cairan yang pertama kali dihasilkan dari proses ini sebelum mulai dipantau penyusutannya.
                </p>
            </div>
            
            <form action="{{ route('production.melts.store-report', $meltBatch) }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Nama Bahan Cairan (Hasil)</label>
                            <input type="text" name="target_material_name" placeholder="Misal: Cairan Getah Batch A" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                            <span class="text-[10px] text-slate-500 mt-1 block">Kode bahan otomatis mengikuti kode pencairan ({{ $meltBatch->melt_code }}).</span>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Berat Cairan Awal (kg)</label>
                            <input type="number" step="0.01" name="initial_liquid_weight" class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" required>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Tanggal Laporan</label>
                            <input type="date" name="report_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-wider">PIC Laporan</label>
                            <input type="text" name="pic_name" placeholder="Nama PIC..." class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-700 mb-1 uppercase tracking-wider">Catatan</label>
                            <input type="text" name="notes" placeholder="Opsional..." class="w-full px-3 py-2 rounded-xl apple-input text-xs">
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 pt-4 border-t border-slate-900/10 flex justify-end">
                    <button type="submit" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition w-full sm:w-auto">
                        Simpan Laporan & Mulai Pantau
                    </button>
                </div>
            </form>
        </div>

    @elseif($meltBatch->status === 'monitoring')
        {{-- Form Timbang Ulang berdasarkan cairan getah di gudang --}}
        @php $targetMaterial = $meltBatch->targetMaterial; @endphp
        @if($targetMaterial)
            <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
                <div class="border-b border-slate-900/10 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Timbang Ulang Getah Cair</h3>
                    <p class="text-[11px] text-slate-600 mt-1 font-medium">Mencatat penguapan dari cairan yang ada di gudang.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 p-4 rounded-2xl bg-slate-50/60 border border-slate-200">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Cairan di Gudang</span>
                        <div class="text-sm font-bold text-slate-900 mt-0.5">{{ $targetMaterial->name }}</div>
                        <div class="text-[10px] font-mono text-slate-500">Kode: {{ $targetMaterial->code }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Stok Saat Ini (Gudang)</span>
                        <div class="text-xl font-black font-mono text-slate-900 mt-0.5">{{ number_format($targetMaterial->stock_quantity, 2) }} kg</div>
                    </div>
                </div>

                <form action="{{ route('production.melts.store-log', $meltBatch) }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Timbang</label>
                            <input type="date" name="weighed_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">PIC Penimbang</label>
                            <input type="text" name="pic_name" placeholder="Nama PIC..." class="w-full px-3 py-2 rounded-xl apple-input text-xs font-semibold" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Berat Saat Ini (kg)</label>
                            <input type="number" step="0.01" name="new_weight" value="{{ old('new_weight') }}" max="{{ $currentStock }}" class="w-full px-3 py-2 rounded-xl apple-input text-sm font-mono font-bold" placeholder="Maks: {{ number_format($currentStock, 2) }}" required>
                            <span class="text-[11px] text-slate-500 font-medium italic block mt-1">
                                Berat sebelumnya (Gudang): {{ number_format($currentStock, 2) }} kg
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan</label>
                            <input type="text" name="notes" placeholder="Opsional..." class="w-full px-3 py-2 rounded-xl apple-input text-xs">
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-900/10 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl btn-dark text-xs font-bold shadow-md transition">
                            Simpan Data Timbang
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="p-6 rounded-3xl apple-glass-panel text-center">
                <p class="text-xs text-slate-500 italic">Tidak ada data cairan getah di gudang untuk batch ini.</p>
            </div>
        @endif
    @endif

    {{-- Riwayat Timbang dari MaterialLog warehouse (single source of truth) --}}
    <div class="apple-glass-panel rounded-3xl p-0 shadow-md overflow-hidden">
        <div class="p-5 border-b border-slate-900/10">
            <h3 class="text-sm font-bold text-slate-900">Riwayat Penimbangan & Penguapan</h3>
            @if($targetMaterial)
                <p class="text-[11px] text-slate-500 mt-0.5">Data dari riwayat pergerakan stok barang <strong>{{ $targetMaterial->name }}</strong> di Gudang.</p>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-800">
                <thead class="bg-slate-50/50">
                    <tr class="border-b border-slate-900/10 text-slate-500 font-extrabold uppercase text-[10px] tracking-wider">
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4 text-right">Jumlah</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4">PIC</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @if($targetMaterial)
                        @forelse($targetMaterial->logs()->latest()->get() as $log)
                            <tr class="hover:bg-white/50 transition">
                                <td class="py-3 px-4 font-semibold text-slate-900">
                                    {{ optional($log->movement_date)->format('d/m/Y') ?? '-' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold {{ $log->type === 'in' ? 'bg-slate-100 text-slate-700' : 'bg-slate-200 text-slate-800' }} border border-slate-300">
                                        {{ $log->type === 'in' ? 'Masuk' : 'Keluar (Susut)' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    {{ number_format($log->quantity, 2) }} {{ $log->unit }}
                                </td>
                                <td class="py-3 px-4 text-slate-600 text-[11px]">
                                    {{ $log->notes ?: '-' }}
                                </td>
                                <td class="py-3 px-4">{{ $log->actor_by ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-slate-500 text-xs italic">
                                    Belum ada riwayat pergerakan stok.
                                </td>
                            </tr>
                        @endforelse
                    @else
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500 text-xs italic">
                                Riwayat tersedia setelah laporan pencairan disimpan.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
