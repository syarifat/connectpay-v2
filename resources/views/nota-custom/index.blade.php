@extends('layouts.app')

@section('title', 'Nota Custom')
@section('page-title', 'Nota Custom')
@section('page-subtitle', 'Transaksi & nota non-WiFi')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-slate-400 text-sm">Total <span class="text-white font-semibold">{{ $nota->total() }}</span> nota dibuat</p>
    <a href="{{ route('nota-custom.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-blue-900/30">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Buat Nota
    </a>
</div>

<div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-800/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nomor Nota</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Pembeli</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Harga</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($nota as $item)
            <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="px-5 py-4">
                    <span class="font-mono text-blue-300 text-xs bg-blue-500/10 px-2 py-1 rounded-lg">{{ $item->nomor_nota }}</span>
                </td>
                <td class="px-5 py-4 text-slate-400">{{ $item->tanggal->isoFormat('D MMM Y') }}</td>
                <td class="px-5 py-4 font-semibold text-slate-100">{{ $item->nama_pembeli }}</td>
                <td class="px-5 py-4 text-right text-emerald-400 font-semibold">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                <td class="px-5 py-4 text-right">
                    <a href="{{ route('nota-custom.cetak', $item->id) }}" target="_blank"
                       class="px-3 py-1.5 text-xs font-medium text-slate-300 border border-slate-700 rounded-lg hover:bg-slate-800 transition-colors">
                        Cetak Nota
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                    Belum ada nota. <a href="{{ route('nota-custom.create') }}" class="text-blue-400 hover:underline">Buat nota pertama</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($nota->hasPages())
    <div class="px-5 py-4 border-t border-slate-800">
        {{ $nota->links() }}
    </div>
    @endif
</div>
@endsection
