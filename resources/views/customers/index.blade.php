@extends('layouts.app', ['title' => 'Daftar Pelanggan'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pelanggan</h2>
            <p class="text-xs text-slate-500 font-medium mt-1">Kelola data dan kontak pelanggan perusahaan.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="px-5 py-2.5 rounded-full btn-dark text-sm self-start">
            Tambah Pelanggan Baru
        </a>
    </div>

    <div class="apple-glass-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-white/50 text-xs uppercase text-slate-500 font-bold border-b border-slate-900/10">
                    <tr>
                        <th class="px-6 py-4">Nama Pelanggan</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Telepon</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-900/10">
                    @forelse($customers as $c)
                        <tr class="hover:bg-white/60 transition">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                <a href="{{ route('customers.show', $c) }}" class="hover:underline">{{ $c->name }}</a>
                            </td>
                            <td class="px-6 py-4 font-medium text-slate-700">{{ $c->email }}</td>
                            <td class="px-6 py-4 font-medium text-slate-700">{{ $c->phone }}</td>
                            <td class="px-6 py-4">
                                @if($c->is_active)
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark">Aktif</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold badge-dark opacity-60">Non-aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right space-x-3">
                                <a href="{{ route('customers.show', $c) }}" class="text-xs text-slate-900 font-bold hover:underline">Detail</a>
                                <a href="{{ route('customers.edit', $c) }}" class="text-xs text-slate-600 font-bold hover:text-slate-900">Edit</a>
                                <form action="{{ route('customers.destroy', $c) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pelanggan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-slate-500 font-bold hover:text-slate-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-slate-400 font-medium">Belum ada pelanggan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="p-4 border-t border-slate-900/10">
                {{ $customers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
