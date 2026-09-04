@extends('layouts.app', ['title' => 'CRM'])

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900">CRM</h2>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Pelanggan</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $totalCustomers }}</div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelanggan Aktif</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $activeCustomers }}</div>
        </div>

        <div class="p-5 rounded-2xl apple-glass-card">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Aktivitas</span>
            <div class="text-2xl font-bold text-slate-900 mt-1">
                {{ $totalCustomers > 0 ? round(($activeCustomers / $totalCustomers) * 100) : 0 }}%
            </div>
        </div>
    </div>

    <!-- Top Spenders -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-bold text-slate-900">Pelanggan Utama</h3>
            <a href="{{ route('customers.index') }}" class="text-xs font-semibold text-slate-700 hover:underline">
                Semua
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($topSpenders as $customer)
                @php
                    $totalSpent = $customer->salesOrders->sum('total_amount');
                @endphp
                <div class="p-4 rounded-2xl bg-white/70 border border-slate-900/10 flex flex-col justify-between">
                    <div>
                        <span class="font-bold text-sm text-slate-900">{{ $customer->name }}</span>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $customer->email ?? $customer->phone ?? '-' }}</p>
                    </div>

                    <div class="mt-3 pt-2 border-t border-slate-900/10 flex items-center justify-between">
                        <span class="text-[10px] text-slate-400">Total:</span>
                        <span class="text-xs font-mono font-bold text-slate-900">
                            Rp {{ number_format($totalSpent, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-4 text-slate-400 text-xs">
                    Belum ada data.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Customer Engagement List -->
    <div class="apple-glass-panel rounded-3xl p-6 shadow-md">
        <h3 class="text-sm font-bold text-slate-900 mb-4">Daftar Pelanggan</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-900/10 text-slate-500 font-bold uppercase tracking-wider">
                        <th class="py-2.5 px-3">Nama</th>
                        <th class="py-2.5 px-3">Kontak</th>
                        <th class="py-2.5 px-3">Pesanan</th>
                        <th class="py-2.5 px-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/5">
                    @foreach($customers as $c)
                        <tr class="hover:bg-white/40 transition">
                            <td class="py-3 px-3 font-semibold text-slate-900">{{ $c->name }}</td>
                            <td class="py-3 px-3 text-slate-600">{{ $c->email ?: $c->phone }}</td>
                            <td class="py-3 px-3 font-mono text-slate-800">{{ $c->sales_orders_count }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $c->is_active ? 'bg-slate-900 text-white' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $c->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-900/10">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
