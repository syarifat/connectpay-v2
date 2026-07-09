@extends('layouts.app')

@section('title', 'Tagihan WiFi')
@section('page-title', 'Tagihan WiFi')
@section('page-subtitle', 'Daftar tagihan internet semua pelanggan')

@section('content')
<div class="flex items-center justify-between mb-5">
    <p class="text-slate-400 text-sm">Total <span class="text-white font-semibold">{{ $tagihan->total() }}</span> tagihan</p>
    <a href="{{ route('tagihan-wifi.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Buat Tagihan
    </a>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('tagihan-wifi.index') }}"
      class="flex items-center gap-3 mb-5 p-4 bg-slate-900 rounded-xl border border-slate-800">
    <div class="flex-1 max-w-xs">
        <select name="pelanggan_id"
                class="w-full px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:border-blue-500">
            <option value="">Semua Pelanggan</option>
            @foreach($pelanggan as $p)
                <option value="{{ $p->id }}" {{ request('pelanggan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <select name="status"
                class="px-3 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-lg text-sm focus:outline-none focus:border-blue-500">
            <option value="">Semua Status</option>
            <option value="Belum Dibayar" {{ request('status') === 'Belum Dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
            <option value="Cicilan"       {{ request('status') === 'Cicilan'       ? 'selected' : '' }}>Cicilan</option>
            <option value="Lunas"         {{ request('status') === 'Lunas'         ? 'selected' : '' }}>Lunas</option>
        </select>
    </div>
    <button type="submit"
            class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-colors">
        Filter
    </button>
    @if(request('status') || request('pelanggan_id'))
    <a href="{{ route('tagihan-wifi.index') }}"
       class="px-3 py-2 text-slate-500 hover:text-slate-300 text-sm transition-colors">Reset</a>
    @endif
</form>

{{-- Tabel Tagihan --}}
<div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-800/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Periode</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Total</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Dibayar</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa</th>
                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($tagihan as $item)
            @php $sc = $item->statusColor; @endphp
            <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-100">{{ $item->pelanggan->nama ?? '-' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $item->pelanggan->paketHarga->nama_paket ?? '-' }}</p>
                </td>
                <td class="px-5 py-4 text-slate-300 whitespace-nowrap">
                    {{ $item->nama_bulan }} {{ $item->tahun_tagihan }}
                </td>
                <td class="px-5 py-4 text-right text-slate-300">Rp {{ number_format($item->total_tagihan, 0, ',', '.') }}</td>
                <td class="px-5 py-4 text-right text-emerald-400 font-medium">Rp {{ number_format($item->nominal_dibayar, 0, ',', '.') }}</td>
                <td class="px-5 py-4 text-right font-medium {{ $item->sisa_tagihan > 0 ? 'text-red-400' : 'text-slate-600' }}">
                    Rp {{ number_format($item->sisa_tagihan, 0, ',', '.') }}
                </td>
                <td class="px-5 py-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                        {{ $sc['label'] }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">
                        {{-- Tombol Bayar hanya jika belum lunas --}}
                        @if($item->status !== 'Lunas')
                        <a href="{{ route('pembayaran-wifi.create', ['tagihan_id' => $item->id, 'pelanggan_id' => $item->pelanggan_id]) }}"
                           class="px-3 py-1.5 text-xs font-semibold text-blue-300 bg-blue-500/15 border border-blue-500/30 rounded-lg hover:bg-blue-500/25 transition-colors whitespace-nowrap">
                            Bayar
                        </a>
                        @endif
                        <a href="{{ route('tagihan-wifi.cetak', $item->secure_key) }}" target="_blank"
                           class="px-3 py-1.5 text-xs font-medium text-slate-300 border border-slate-700 rounded-lg hover:bg-slate-800 transition-colors">
                            Cetak
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    Belum ada tagihan. <a href="{{ route('tagihan-wifi.create') }}" class="text-blue-400 hover:underline">Buat tagihan pertama</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($tagihan->hasPages())
    <div class="px-5 py-4 border-t border-slate-800">{{ $tagihan->links() }}</div>
    @endif
</div>
@endsection
