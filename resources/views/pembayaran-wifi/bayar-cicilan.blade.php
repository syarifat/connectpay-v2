@extends('layouts.app')

@section('title', 'Bayar Cicilan')
@section('page-title', 'Bayar Cicilan')
@section('page-subtitle', 'Lanjutkan pelunasan tagihan yang belum lunas')

@section('content')
<div class="max-w-2xl space-y-5">
    {{-- Info Tagihan --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Informasi Tagihan</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-slate-500 mb-0.5">Pelanggan</p>
                <p class="font-semibold text-slate-100">{{ $pembayaran->pelanggan->nama }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-0.5">Paket</p>
                <p class="font-medium text-slate-300">{{ $pembayaran->pelanggan->paketHarga->nama_paket }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-0.5">Periode</p>
                <p class="font-medium text-slate-300">{{ $pembayaran->nama_bulan }} {{ $pembayaran->tahun_tagihan }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 mb-0.5">Status</p>
                <span class="inline-flex px-2.5 py-1 rounded-full bg-amber-500/15 text-amber-300 text-xs font-semibold">◑ Cicilan</span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-800">
            <div class="p-3 bg-slate-800 rounded-xl text-center">
                <p class="text-xs text-slate-500 mb-1">Total Tagihan</p>
                <p class="font-bold text-slate-100 text-sm">Rp {{ number_format($pembayaran->total_tagihan, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-slate-800 rounded-xl text-center">
                <p class="text-xs text-slate-500 mb-1">Sudah Dibayar</p>
                <p class="font-bold text-emerald-400 text-sm">Rp {{ number_format($pembayaran->nominal_dibayar, 0, ',', '.') }}</p>
            </div>
            <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-center">
                <p class="text-xs text-red-400 mb-1">Sisa Tagihan</p>
                <p class="font-bold text-red-300 text-sm">Rp {{ number_format($pembayaran->sisa_tagihan, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Histori Cicilan --}}
    @if($pembayaran->cicilanPembayaran->isNotEmpty())
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Riwayat Cicilan</h3>
        <div class="space-y-2">
            @foreach($pembayaran->cicilanPembayaran as $cicilan)
            <div class="flex items-center justify-between py-2.5 border-b border-slate-800 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <span class="text-sm text-slate-400">{{ $cicilan->tanggal_bayar->isoFormat('D MMMM Y') }}</span>
                </div>
                <span class="font-semibold text-emerald-400">Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Form Bayar --}}
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6" x-data="{
        nominal: '',
        sisa: {{ $pembayaran->sisa_tagihan }},
        get sisaSetelah() {
            return Math.max(0, this.sisa - parseFloat(this.nominal || 0));
        },
        lunasPenuh() {
            this.nominal = this.sisa;
        },
        fmt(v) { return Number(v).toLocaleString('id-ID'); }
    }">
        <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-4">Tambah Pembayaran</h3>
        <form action="{{ route('pembayaran-wifi.proses-bayar-cicilan', $pembayaran->id) }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label for="tanggal_bayar" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tanggal Bayar <span class="text-red-400">*</span>
                    </label>
                    <input type="date" id="tanggal_bayar" name="tanggal_bayar"
                           value="{{ old('tanggal_bayar', date('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50">
                    @error('tanggal_bayar') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nominal_bayar" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nominal Bayar (maks. Rp {{ number_format($pembayaran->sisa_tagihan, 0, ',', '.') }}) <span class="text-red-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                        <input type="number" id="nominal_bayar" name="nominal_bayar"
                               x-model="nominal"
                               value="{{ old('nominal_bayar') }}"
                               placeholder="0"
                               min="1" step="500" max="{{ $pembayaran->sisa_tagihan }}"
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                      placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50">
                        <button type="button" @click="lunasPenuh()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs rounded-lg transition-colors">
                            Lunasi
                        </button>
                    </div>
                    @error('nominal_bayar') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Preview --}}
                <div x-show="nominal" x-cloak class="flex items-center gap-2 text-sm">
                    <span class="text-slate-400">Sisa setelah bayar:</span>
                    <span class="font-bold" :class="sisaSetelah <= 0 ? 'text-emerald-400' : 'text-red-400'">
                        Rp <span x-text="fmt(sisaSetelah)"></span>
                    </span>
                    <span x-show="sisaSetelah <= 0" class="px-2 py-0.5 bg-emerald-500/15 text-emerald-300 text-xs rounded-full font-semibold">→ LUNAS</span>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-800">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    Catat Pembayaran
                </button>
                <a href="{{ route('pembayaran-wifi.index') }}"
                   class="px-5 py-2.5 text-slate-400 hover:text-slate-100 text-sm font-medium rounded-xl hover:bg-slate-800 transition-colors">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
