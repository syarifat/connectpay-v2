@extends('layouts.app')

@section('title', 'Buat Tagihan WiFi')
@section('page-title', 'Buat Tagihan WiFi')
@section('page-subtitle', 'Buat tagihan internet bulanan untuk pelanggan')

@section('content')
<div class="max-w-xl" x-data="tagihanForm()">
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <form action="{{ route('tagihan-wifi.store') }}" method="POST">
            @csrf

            <div class="space-y-5">
                {{-- Pilih Pelanggan --}}
                <div>
                    <label for="pelanggan_id" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Pelanggan <span class="text-red-400">*</span>
                    </label>
                    <select id="pelanggan_id" name="pelanggan_id"
                            x-model="selectedPelangganId"
                            @change="loadPaketInfo(); cekCicilan();"
                            class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                   focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                   @error('pelanggan_id') border-red-500 @enderror">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($pelanggan as $p)
                            <option value="{{ $p->id }}"
                                    data-paket="{{ $p->paketHarga->nama_paket ?? '-' }}"
                                    data-harga="{{ $p->paketHarga->harga ?? 0 }}"
                                    {{ old('pelanggan_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('pelanggan_id') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Info Paket Aktif --}}
                <div x-show="paketNama" x-cloak
                     class="flex items-center justify-between p-4 bg-blue-500/10 border border-blue-500/20 rounded-xl">
                    <div>
                        <p class="text-xs text-blue-400 font-medium uppercase tracking-wider">Paket Aktif</p>
                        <p class="text-slate-100 font-semibold mt-0.5" x-text="paketNama"></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-500">Tagihan per bulan</p>
                        <p class="text-emerald-400 font-bold text-lg">Rp <span x-text="formatRupiah(paketHarga)"></span></p>
                    </div>
                </div>

                {{-- ⚠️ Warning: Cicilan Belum Lunas --}}
                <div x-show="cicilanLalu.length > 0" x-cloak
                     class="p-4 bg-amber-500/10 border border-amber-500/30 rounded-xl space-y-2">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <p class="text-amber-300 text-sm font-semibold">Pelanggan ini masih punya tagihan belum lunas!</p>
                    </div>
                    <template x-for="c in cicilanLalu" :key="c.id">
                        <div class="flex items-center justify-between py-2 border-t border-amber-500/20">
                            <span class="text-amber-200 text-sm" x-text="c.nama_bulan + ' ' + c.tahun_tagihan"></span>
                            <div class="text-right">
                                <span class="text-xs text-amber-400 font-medium px-2 py-0.5 bg-amber-500/15 rounded-full" x-text="c.status"></span>
                                <span class="text-red-300 font-semibold text-sm ml-2">Sisa: <span x-text="c.sisa_format"></span></span>
                            </div>
                        </div>
                    </template>
                    <p class="text-xs text-amber-500 mt-1">
                        Tagihan baru akan tetap dibuat. Bayar tagihan lama via menu <strong>Pembayaran WiFi</strong>.
                    </p>
                </div>

                {{-- Bulan & Tahun --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="bulan_tagihan" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Bulan Tagihan <span class="text-red-400">*</span>
                        </label>
                        <select id="bulan_tagihan" name="bulan_tagihan"
                                class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                       focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                       @error('bulan_tagihan') border-red-500 @enderror">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $idx => $bulan)
                                <option value="{{ $idx + 1 }}"
                                        {{ old('bulan_tagihan', date('n')) == ($idx + 1) ? 'selected' : '' }}>
                                    {{ $bulan }}
                                </option>
                            @endforeach
                        </select>
                        @error('bulan_tagihan') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tahun_tagihan" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Tahun <span class="text-red-400">*</span>
                        </label>
                        <select id="tahun_tagihan" name="tahun_tagihan"
                                class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                       focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50">
                            @foreach($tahunList as $tahun)
                                <option value="{{ $tahun }}" {{ old('tahun_tagihan', date('Y')) == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-800">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                    Buat Tagihan
                </button>
                <a href="{{ route('tagihan-wifi.index') }}"
                   class="px-5 py-2.5 text-slate-400 hover:text-slate-100 text-sm font-medium rounded-xl hover:bg-slate-800 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function tagihanForm() {
    return {
        selectedPelangganId: '{{ old('pelanggan_id', '') }}',
        paketNama: '',
        paketHarga: 0,
        cicilanLalu: [],

        loadPaketInfo() {
            const select = document.getElementById('pelanggan_id');
            const opt = select.options[select.selectedIndex];
            this.paketNama  = opt.dataset.paket || '';
            this.paketHarga = parseFloat(opt.dataset.harga || 0);
        },

        async cekCicilan() {
            if (!this.selectedPelangganId) {
                this.cicilanLalu = [];
                return;
            }
            try {
                const res = await fetch(`/tagihan-wifi/api/cek-cicilan/${this.selectedPelangganId}`);
                this.cicilanLalu = await res.json();
            } catch (e) {
                this.cicilanLalu = [];
            }
        },

        formatRupiah(val) {
            return Number(val || 0).toLocaleString('id-ID');
        },

        init() {
            if (this.selectedPelangganId) {
                this.$nextTick(() => {
                    this.loadPaketInfo();
                    this.cekCicilan();
                });
            }
        }
    }
}
</script>
@endpush
@endsection
