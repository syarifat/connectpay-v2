@extends('layouts.app')

@section('title', 'Master Paket Harga')
@section('page-title', 'Master Paket Harga')
@section('page-subtitle', 'Kelola paket internet yang tersedia')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-slate-400 text-sm">Total <span class="text-white font-semibold">{{ $paket->total() }}</span> paket tersedia</p>
    </div>
    <a href="{{ route('paket-harga.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-blue-900/30">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah Paket
    </a>
</div>

<div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-800/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-10">#</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Paket</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Harga / Bulan</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Deskripsi</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($paket as $index => $item)
            <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="px-5 py-4 text-slate-500 text-xs">{{ $paket->firstItem() + $index }}</td>
                <td class="px-5 py-4">
                    <span class="font-semibold text-slate-100">{{ $item->nama_paket }}</span>
                </td>
                <td class="px-5 py-4">
                    <span class="text-emerald-400 font-semibold">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                </td>
                <td class="px-5 py-4 text-slate-400 max-w-xs truncate">{{ $item->deskripsi ?? '-' }}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('paket-harga.edit', $item) }}"
                           class="px-3 py-1.5 text-xs font-medium text-blue-400 border border-blue-500/30 rounded-lg hover:bg-blue-500/10 transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('paket-harga.destroy', $item) }}" method="POST"
                              onsubmit="return confirm('Yakin hapus paket {{ $item->nama_paket }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs font-medium text-red-400 border border-red-500/30 rounded-lg hover:bg-red-500/10 transition-colors">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /></svg>
                    Belum ada paket harga. <a href="{{ route('paket-harga.create') }}" class="text-blue-400 hover:underline">Tambah sekarang</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($paket->hasPages())
    <div class="px-5 py-4 border-t border-slate-800">
        {{ $paket->links() }}
    </div>
    @endif
</div>
@endsection
