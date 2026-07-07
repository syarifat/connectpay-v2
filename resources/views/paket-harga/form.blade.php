@extends('layouts.app')

@section('title', $paket->exists ? 'Edit Paket Harga' : 'Tambah Paket Harga')
@section('page-title', $paket->exists ? 'Edit Paket Harga' : 'Tambah Paket Harga')
@section('page-subtitle', 'Master Data Paket Internet')

@section('content')
<div class="max-w-xl">
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <form action="{{ $paket->exists ? route('paket-harga.update', $paket) : route('paket-harga.store') }}" method="POST">
            @csrf
            @if($paket->exists)
                @method('PUT')
            @endif

            <div class="space-y-5">
                {{-- Nama Paket --}}
                <div>
                    <label for="nama_paket" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nama Paket <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="nama_paket" name="nama_paket"
                           value="{{ old('nama_paket', $paket->nama_paket) }}"
                           placeholder="Contoh: Paket 10 Mbps"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                  @error('nama_paket') border-red-500 @enderror">
                    @error('nama_paket')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Harga --}}
                <div>
                    <label for="harga" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Harga / Bulan (Rp) <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-medium">Rp</span>
                        <input type="number" id="harga" name="harga"
                               value="{{ old('harga', $paket->harga) }}"
                               placeholder="150000"
                               min="0" step="500"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                      placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                      @error('harga') border-red-500 @enderror">
                    </div>
                    @error('harga')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Deskripsi <span class="text-slate-600 text-xs">(opsional)</span>
                    </label>
                    <textarea id="deskripsi" name="deskripsi" rows="3"
                              placeholder="Contoh: Kecepatan up to 10 Mbps, cocok untuk rumahan"
                              class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                     placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 resize-none">{{ old('deskripsi', $paket->deskripsi) }}</textarea>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-800">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                    {{ $paket->exists ? 'Simpan Perubahan' : 'Tambah Paket' }}
                </button>
                <a href="{{ route('paket-harga.index') }}"
                   class="px-5 py-2.5 text-slate-400 hover:text-slate-100 text-sm font-medium rounded-xl transition-colors hover:bg-slate-800">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
