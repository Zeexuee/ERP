@extends('layouts.app', ['title' => 'Edit Pelanggan'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Edit Pelanggan: {{ $customer->name }}</h2>
        <a href="{{ route('customers.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-900">Kembali</a>
    </div>

    <div class="apple-glass-card rounded-2xl p-8 border border-white">
        <form action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nama Perusahaan / Klien *</label>
                <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
                @error('name')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
                    @error('email')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nomor Telepon *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">
                    @error('phone')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Alamat Lengkap *</label>
                <textarea name="address" rows="3" required class="w-full rounded-xl apple-input px-4 py-2.5 text-sm font-medium">{{ old('address', $customer->address) }}</textarea>
                @error('address')<span class="text-xs text-slate-900 mt-1 block font-bold">{{ $message }}</span>@enderror
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ $customer->is_active ? 'checked' : '' }} class="rounded border-slate-400 text-slate-900 focus:ring-slate-900">
                <label for="is_active" class="text-sm font-bold text-slate-800">Setel Pelanggan Aktif</label>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('customers.index') }}" class="px-5 py-2.5 rounded-full btn-subtle text-sm">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-full btn-dark text-sm">Perbarui Pelanggan</button>
            </div>
        </form>
    </div>
</div>
@endsection
