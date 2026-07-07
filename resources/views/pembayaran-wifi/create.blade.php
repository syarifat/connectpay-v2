@extends('layouts.app')

@section('title', 'Catat Pembayaran WiFi')
@section('page-title', 'Catat Pembayaran WiFi')
@section('page-subtitle', 'Pilih pelanggan dan tagihan yang akan dibayar')

@section('content')
<div class="max-w-2xl" x-data="pembayaranForm()">
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <form action="{{ route('pembayaran-wifi.store') }}" method="POST">
            @csrf

            <div class="space-y-5">

                {{-- 1. Pilih Pelanggan --}}
                <div>
                    <label for="pelanggan_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                        1. Pilih Pelanggan <span class="text-red-400">*</span>
                    </label>
                    <select id="pelanggan_id" name="pelanggan_id"
                            x-model="pelangganId"
                            @change="loadTagihan()"
                            class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                   focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                   @error('pelanggan_id') border-red-500 @enderror">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->id }}"
                                    {{ (old('pelanggan_id', request('pelanggan_id'))) == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('pelanggan_id') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Loading state --}}
                <div x-show="loading" x-cloak class="flex items-center gap-2 text-slate-500 text-sm py-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Memuat tagihan...
                </div>

                {{-- Tidak ada tagihan belum lunas --}}
                <div x-show="pelangganId && !loading && tagihanList.length === 0" x-cloak
                     class="p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-300 text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    Tidak ada tagihan yang perlu dibayar untuk pelanggan ini.
                </div>

                {{-- 2. Pilih Tagihan --}}
                <div x-show="tagihanList.length > 0" x-cloak>
                    <label class="block text-sm font-medium text-slate-300 mb-2">
                        2. Pilih Tagihan yang Akan Dibayar <span class="text-red-400">*</span>
                    </label>

                    <div class="space-y-2">
                        <template x-for="t in tagihanList" :key="t.id">
                            <label class="flex items-center gap-4 p-4 bg-slate-800 border border-slate-700 rounded-xl cursor-pointer
                                          hover:border-blue-500/50 transition-colors"
                                   :class="selectedTagihanId == t.id ? 'border-blue-500 bg-blue-500/10' : ''">
                                <input type="radio" name="tagihan_id" :value="t.id"
                                       x-model="selectedTagihanId"
                                       @change="onTagihanSelect(t)"
                                       class="w-4 h-4 accent-blue-500">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-slate-100 text-sm" x-text="t.label"></span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="t.status === 'Cicilan' ? 'bg-amber-500/15 text-amber-300' : 'bg-slate-500/15 text-slate-300'"
                                              x-text="t.status"></span>
                                    </div>
                                    <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                                        <span>Total: Rp <span x-text="fmt(t.total_tagihan)"></span></span>
                                        <span>Dibayar: Rp <span x-text="fmt(t.nominal_dibayar)"></span></span>
                                        <span class="text-red-400 font-medium">Sisa: Rp <span x-text="t.sisa_format"></span></span>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                    @error('tagihan_id') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- 3. Detail Pembayaran --}}
                <div x-show="selectedTagihanId" x-cloak class="space-y-4 pt-2 border-t border-slate-800">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider pt-1">3. Detail Pembayaran</p>

                    {{-- Tanggal Bayar --}}
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

                    {{-- Nominal Bayar --}}
                    <div>
                        <label for="nominal_dibayar" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nominal Dibayar (Rp) <span class="text-red-400">*</span>
                            <span class="text-slate-500 font-normal">— maks. Rp <span x-text="fmt(sisaTagihan)"></span></span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 text-sm">Rp</span>
                            <input type="number" id="nominal_dibayar" name="nominal_dibayar"
                                   x-model="nominalBayar"
                                   :max="sisaTagihan"
                                   @input="if (parseFloat(nominalBayar) > sisaTagihan) nominalBayar = sisaTagihan"
                                   value="{{ old('nominal_dibayar') }}"
                                   placeholder="0" min="1" step="1"
                                   class="w-full pl-10 pr-36 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                          placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50">
                            <button type="button" @click="bayarPenuh()"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs rounded-lg transition-colors">
                                Bayar Penuh
                            </button>
                        </div>
                        @error('nominal_dibayar') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Preview Sisa & Status --}}
                    <div x-show="nominalBayar > 0" x-cloak>
                        <div class="grid grid-cols-3 gap-3">
                            <div class="p-3 bg-slate-800 rounded-xl text-center">
                                <p class="text-xs text-slate-500 mb-1">Sisa Tagihan</p>
                                <p class="text-sm font-bold text-red-400">Rp <span x-text="fmt(sisaTagihan)"></span></p>
                            </div>
                            <div class="p-3 bg-slate-800 rounded-xl text-center">
                                <p class="text-xs text-slate-500 mb-1">Dibayar</p>
                                <p class="text-sm font-bold text-emerald-400">Rp <span x-text="fmt(nominalBayar || 0)"></span></p>
                            </div>
                            <div class="p-3 bg-slate-800 rounded-xl text-center">
                                <p class="text-xs text-slate-500 mb-1">Sisa Setelah Bayar</p>
                                <p class="text-sm font-bold"
                                   :class="sisaSetelah <= 0 ? 'text-emerald-400' : 'text-amber-400'">
                                    Rp <span x-text="fmt(Math.max(0, sisaSetelah))"></span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <span class="text-sm text-slate-400">Status setelah bayar:</span>
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold"
                                  :class="sisaSetelah <= 0 ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300'"
                                  x-text="sisaSetelah <= 0 ? '✓ Lunas' : '◑ Cicilan'">
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-800">
                <button type="submit"
                        x-show="selectedTagihanId"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                    Simpan & Cetak Kuitansi
                </button>
                <a href="{{ route('pembayaran-wifi.index') }}"
                   class="px-5 py-2.5 text-slate-400 hover:text-slate-100 text-sm font-medium rounded-xl hover:bg-slate-800 transition-colors">
                    Kembali
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function pembayaranForm() {
    return {
        pelangganId: '{{ old('pelanggan_id', request('pelanggan_id', '')) }}',
        tagihanList: [],
        selectedTagihanId: '{{ old('tagihan_id', request('tagihan_id', '')) }}',
        sisaTagihan: 0,
        nominalBayar: {{ old('nominal_dibayar', 0) }},
        loading: false,

        get sisaSetelah() {
            return this.sisaTagihan - parseFloat(this.nominalBayar || 0);
        },

        async loadTagihan() {
            if (!this.pelangganId) {
                this.tagihanList = [];
                this.selectedTagihanId = '';
                this.sisaTagihan = 0;
                return;
            }
            this.loading = true;
            try {
                const res = await fetch(`/pembayaran-wifi/api/tagihan/${this.pelangganId}`);
                this.tagihanList = await res.json();

                // Jika ada tagihan_id dari query param (dari tombol Bayar di index tagihan)
                const preselect = '{{ request('tagihan_id') }}';
                if (preselect && this.tagihanList.find(t => t.id == preselect)) {
                    this.selectedTagihanId = preselect;
                    const t = this.tagihanList.find(t => t.id == preselect);
                    if (t) this.onTagihanSelect(t);
                }
            } catch (e) {
                this.tagihanList = [];
            } finally {
                this.loading = false;
            }
        },

        onTagihanSelect(tagihan) {
            this.sisaTagihan = parseFloat(tagihan.sisa_tagihan);
            this.nominalBayar = 0;
        },

        bayarPenuh() {
            this.nominalBayar = this.sisaTagihan;
        },

        fmt(val) {
            return Number(val || 0).toLocaleString('id-ID');
        },

        init() {
            if (this.pelangganId) {
                this.$nextTick(() => this.loadTagihan());
            }
        }
    }
}
</script>
@endpush
@endsection
