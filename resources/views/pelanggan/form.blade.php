@extends('layouts.app')

@section('title', $pelanggan->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('page-title', $pelanggan->exists ? 'Edit Pelanggan' : 'Tambah Pelanggan')
@section('page-subtitle', 'Master Data Pelanggan')

@section('content')
<div class="max-w-xl">
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <form action="{{ $pelanggan->exists ? route('pelanggan.update', $pelanggan) : route('pelanggan.store') }}" method="POST">
            @csrf
            @if($pelanggan->exists)
                @method('PUT')
            @endif

            <div class="space-y-5">
                {{-- Nama --}}
                <div>
                    <label for="nama" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nama Pelanggan <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="nama" name="nama"
                           value="{{ old('nama', $pelanggan->nama) }}"
                           placeholder="Nama lengkap pelanggan"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                  @error('nama') border-red-500 @enderror">
                    @error('nama') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- No. HP --}}
                <div>
                    <label for="no_hp" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nomor HP / WA <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="no_hp" name="no_hp"
                           value="{{ old('no_hp', $pelanggan->no_hp) }}"
                           placeholder="08xxxxxxxxxx"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                  @error('no_hp') border-red-500 @enderror">
                    @error('no_hp') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal Pembayaran --}}
                <div>
                    <label for="tanggal_pembayaran" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tanggal Pembayaran (Hari ke, 1-31) <span class="text-red-400">*</span>
                    </label>
                    <input type="number" id="tanggal_pembayaran" name="tanggal_pembayaran" min="1" max="31"
                           value="{{ old('tanggal_pembayaran', $pelanggan->tanggal_pembayaran) }}"
                           placeholder="Contoh: 10 (Setiap tanggal 10)"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                  @error('tanggal_pembayaran') border-red-500 @enderror">
                    @error('tanggal_pembayaran') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label for="alamat" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Alamat <span class="text-red-400">*</span>
                    </label>
                    <textarea id="alamat" name="alamat" rows="3"
                              placeholder="Alamat lengkap pelanggan"
                              class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                     placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 resize-none
                                     @error('alamat') border-red-500 @enderror">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                    @error('alamat') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Paket Harga --}}
                <div>
                    <label for="paket_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Paket Internet <span class="text-red-400">*</span>
                    </label>
                    <select id="paket_id" name="paket_id"
                            class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                   focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                   @error('paket_id') border-red-500 @enderror">
                        <option value="">-- Pilih Paket --</option>
                        @foreach($paketHarga as $paket)
                            <option value="{{ $paket->id }}"
                                    {{ old('paket_id', $pelanggan->paket_id) == $paket->id ? 'selected' : '' }}>
                                {{ $paket->nama_paket }} — Rp {{ number_format($paket->harga, 0, ',', '.') }}
                            </option>
                        @endforeach
                    </select>
                    @error('paket_id') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                    @if($paketHarga->isEmpty())
                    <p class="mt-1.5 text-xs text-amber-400">⚠ Belum ada paket tersedia.
                        <a href="{{ route('paket-harga.create') }}" class="underline">Tambah paket dulu</a>.
                    </p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-800">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                    {{ $pelanggan->exists ? 'Simpan Perubahan' : 'Tambah Pelanggan' }}
                </button>
                <a href="{{ route('pelanggan.index') }}"
                   class="px-5 py-2.5 text-slate-400 hover:text-slate-100 text-sm font-medium rounded-xl transition-colors hover:bg-slate-800">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
