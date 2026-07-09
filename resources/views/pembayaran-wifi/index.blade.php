@extends('layouts.app')

@section('title', 'Riwayat Pembayaran WiFi')
@section('page-title', 'Riwayat Pembayaran')
@section('page-subtitle', 'Histori semua transaksi pembayaran internet')

@section('content')
<div class="flex items-center justify-between mb-5">
    <p class="text-slate-400 text-sm">Total <span class="text-white font-semibold">{{ $riwayat->total() }}</span> transaksi</p>
    <a href="{{ route('pembayaran-wifi.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Catat Pembayaran
    </a>
</div>

<div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-800/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Periode Tagihan</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tgl. Bayar</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Nominal Bayar</th>
                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Status Tagihan</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($riwayat as $cicilan)
            @php
                $tagihan = $cicilan->pembayaranWifi;
                $sc = $tagihan ? $tagihan->statusColor : [];
            @endphp
            <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-100">{{ $tagihan?->pelanggan?->nama ?? '-' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $tagihan?->pelanggan?->paketHarga?->nama_paket ?? '-' }}</p>
                </td>
                <td class="px-5 py-4 text-slate-300">
                    {{ $tagihan ? ($tagihan->nama_bulan . ' ' . $tagihan->tahun_tagihan) : '-' }}
                </td>
                <td class="px-5 py-4 text-slate-400">
                    {{ $cicilan->tanggal_bayar->isoFormat('D MMM Y') }}
                </td>
                <td class="px-5 py-4 text-right text-emerald-400 font-semibold">
                    Rp {{ number_format($cicilan->nominal, 0, ',', '.') }}
                </td>
                <td class="px-5 py-4 text-center">
                    @if($tagihan && $sc)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }}">
                        {{ $sc['label'] }}
                    </span>
                    @else
                    <span class="text-slate-600">-</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-right">
                    @if($tagihan)
                    <a href="{{ route('tagihan-wifi.cetak', $tagihan->secure_key) }}" target="_blank"
                       class="px-3 py-1.5 text-xs font-medium text-slate-300 border border-slate-700 rounded-lg hover:bg-slate-800 transition-colors">
                        Kuitansi
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75" /></svg>
                    Belum ada riwayat pembayaran. <a href="{{ route('pembayaran-wifi.create') }}" class="text-blue-400 hover:underline">Catat pembayaran</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($riwayat->hasPages())
    <div class="px-5 py-4 border-t border-slate-800">{{ $riwayat->links() }}</div>
    @endif
</div>
@endsection
