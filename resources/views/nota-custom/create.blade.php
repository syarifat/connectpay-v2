@extends('layouts.app')

@section('title', 'Buat Nota Custom')
@section('page-title', 'Buat Nota Custom')
@section('page-subtitle', 'Transaksi barang/jasa non-WiFi')

@section('content')
<div class="max-w-4xl" x-data="notaForm()">
    <div class="bg-slate-900 rounded-2xl border border-slate-800 p-6">
        <form action="{{ route('nota-custom.store') }}" method="POST" id="form-nota">
            @csrf

            {{-- Header Nota --}}
            <div class="grid grid-cols-2 gap-5 mb-6 pb-6 border-b border-slate-800">
                <div>
                    <label for="nama_pembeli" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Nama Pembeli <span class="text-red-400">*</span>
                    </label>
                    <input type="text" id="nama_pembeli" name="nama_pembeli"
                           value="{{ old('nama_pembeli') }}"
                           placeholder="Nama pembeli atau pelanggan"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                  @error('nama_pembeli') border-red-500 @enderror">
                    @error('nama_pembeli') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="tanggal" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Tanggal Transaksi <span class="text-red-400">*</span>
                    </label>
                    <input type="date" id="tanggal" name="tanggal"
                           value="{{ old('tanggal', date('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                  focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50
                                  @error('tanggal') border-red-500 @enderror">
                    @error('tanggal') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Tabel Item --}}
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-300">Detail Item / Barang & Jasa</h3>
                <button type="button" @click="tambahBaris()"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-400 border border-blue-500/30
                               rounded-lg hover:bg-blue-500/10 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Baris
                </button>
            </div>

            @error('items') <p class="mb-3 text-xs text-red-400">{{ $message }}</p> @enderror

            {{-- Item Table --}}
            <div class="bg-slate-800 rounded-xl overflow-hidden border border-slate-700 mb-4">
                {{-- Header --}}
                <div class="grid items-center gap-3 px-4 py-3 bg-slate-700/60 border-b border-slate-700 text-xs font-semibold text-slate-400 uppercase tracking-wider"
                     style="grid-template-columns: 1fr 90px 130px 120px 36px;">
                    <span>Nama Barang / Jasa</span>
                    <span class="text-center">Qty</span>
                    <span class="text-right">Harga Satuan</span>
                    <span class="text-right">Subtotal</span>
                    <span></span>
                </div>

                {{-- Rows --}}
                <template x-for="(item, index) in items" :key="index">
                    <div class="grid items-center gap-3 px-4 py-3 border-b border-slate-700 last:border-0 hover:bg-slate-700/20 transition-colors"
                         style="grid-template-columns: 1fr 90px 130px 120px 36px;">

                        {{-- Nama Item --}}
                        <input type="text"
                               :name="`items[${index}][nama_item]`"
                               x-model="item.nama_item"
                               placeholder="Nama barang atau jasa..."
                               class="w-full px-3 py-2 bg-slate-900 border border-slate-600 text-slate-100 rounded-lg text-sm
                                      placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40">

                        {{-- Kuantitas --}}
                        <input type="number"
                               :name="`items[${index}][kuantitas]`"
                               x-model.number="item.kuantitas"
                               @input="hitungSubtotal(index)"
                               min="1" placeholder="1"
                               class="w-full px-3 py-2 bg-slate-900 border border-slate-600 text-slate-100 rounded-lg text-sm text-center
                                      placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40">

                        {{-- Harga Satuan --}}
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs">Rp</span>
                            <input type="number"
                                   :name="`items[${index}][harga_satuan]`"
                                   x-model.number="item.harga_satuan"
                                   @input="hitungSubtotal(index)"
                                   min="0" step="100" placeholder="0"
                                   class="w-full pl-8 pr-2 py-2 bg-slate-900 border border-slate-600 text-slate-100 rounded-lg text-sm text-right
                                          placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/40">
                        </div>

                        {{-- Subtotal (read-only) --}}
                        <div class="text-right">
                            <span class="text-sm font-semibold text-emerald-400"
                                  x-text="'Rp ' + formatRupiah(item.subtotal)">
                            </span>
                        </div>

                        {{-- Hapus --}}
                        <button type="button" @click="hapusBaris(index)"
                                x-show="items.length > 1"
                                class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:text-red-400
                                       hover:bg-red-500/10 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        <div x-show="items.length <= 1"></div>
                    </div>
                </template>
            </div>

            {{-- Grand Total --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500">
                        <span x-text="items.length"></span> item
                    </span>
                    <button type="button" @click="tambahBaris()"
                            class="text-xs text-blue-400 hover:text-blue-300 transition-colors">
                        + Tambah baris lagi
                    </button>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-slate-400 font-medium">Grand Total:</span>
                    <span class="text-xl font-bold text-emerald-400"
                          x-text="'Rp ' + formatRupiah(grandTotal)">
                    </span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-5 border-t border-slate-800">
                <button type="submit" id="btn-simpan"
                        class="flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                    </svg>
                    Simpan & Cetak Nota
                </button>
                <a href="{{ route('nota-custom.index') }}"
                   class="px-5 py-2.5 text-slate-400 hover:text-slate-100 text-sm font-medium rounded-xl transition-colors hover:bg-slate-800">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function notaForm() {
    return {
        items: [
            { nama_item: '', kuantitas: 1, harga_satuan: 0, subtotal: 0 }
        ],

        get grandTotal() {
            return this.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
        },

        tambahBaris() {
            this.items.push({ nama_item: '', kuantitas: 1, harga_satuan: 0, subtotal: 0 });
            // Focus ke input nama item baru
            this.$nextTick(() => {
                const inputs = document.querySelectorAll('input[name*="[nama_item]"]');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            });
        },

        hapusBaris(index) {
            if (this.items.length > 1) {
                this.items.splice(index, 1);
            }
        },

        hitungSubtotal(index) {
            const item = this.items[index];
            const qty = parseInt(item.kuantitas) || 0;
            const harga = parseFloat(item.harga_satuan) || 0;
            item.subtotal = qty * harga;
        },

        formatRupiah(val) {
            return Number(val || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
@endsection
