@extends('layouts.app', ['title' => 'Detail Pelanggan'])

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $customer->name }}</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">ID Pelanggan: #{{ $customer->id }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customers.edit', $customer) }}" class="px-5 py-2 rounded-full btn-subtle text-xs font-bold">Edit Data</a>
            <a href="{{ route('customers.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
        </div>
    </div>

    <!-- Overview Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Email Kontak</span>
            <p class="text-base font-extrabold text-slate-900 mt-1">{{ $customer->email }}</p>
        </div>
        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Nomor Telepon</span>
            <p class="text-base font-extrabold text-slate-900 mt-1">{{ $customer->phone }}</p>
        </div>
        <div class="apple-glass-card rounded-2xl p-6">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Status Akun</span>
            <div class="mt-1">
                @if($customer->is_active)
                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">Aktif</span>
                @else
                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-60">Non-aktif</span>
                @endif
            </div>
        </div>
    </div>

    <div class="apple-glass-card rounded-2xl p-6">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Alamat Lengkap</span>
        <p class="text-sm text-slate-900 font-semibold leading-relaxed">{{ $customer->address }}</p>
    </div>

    <!-- History Table: Sales Orders -->
    <div class="apple-glass-card rounded-2xl p-6">
        <h3 class="text-base font-bold text-slate-900 mb-4">Riwayat Sales Order ({{ $customer->salesOrders->count() }})</h3>
        <div class="space-y-3">
            @forelse($customer->salesOrders as $so)
                <div class="p-4 rounded-xl bg-white/70 border border-slate-900/10 flex items-center justify-between">
                    <div>
                        <a href="{{ route('sales-orders.show', $so) }}" class="font-mono text-xs font-extrabold text-slate-900 hover:underline">{{ $so->order_number }}</a>
                        <span class="text-xs text-slate-500 block mt-0.5 font-medium">Dibuat: {{ $so->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-extrabold text-slate-900 block">Rp {{ number_format($so->total_amount, 0, ',', '.') }}</span>
                        <span class="text-[10px] uppercase font-bold text-slate-500">{{ $so->status->value }}</span>
                    </div>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-4 text-center font-medium">Belum ada Sales Order.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
