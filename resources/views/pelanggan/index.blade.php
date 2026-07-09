@extends('layouts.app')

@section('title', 'Master Pelanggan')
@section('page-title', 'Master Pelanggan')
@section('page-subtitle', 'Kelola data pelanggan internet')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-slate-400 text-sm">Total <span class="text-white font-semibold">{{ $pelanggan->total() }}</span> pelanggan terdaftar</p>
    <a href="{{ route('pelanggan.create') }}"
       class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition-colors shadow-lg shadow-blue-900/30">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        Tambah Pelanggan
    </a>
</div>

<div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-800/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-10">#</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Pelanggan</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">No. HP</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tgl Bayar</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Paket</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Alamat</th>
                <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($pelanggan as $index => $item)
            <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="px-5 py-4 text-slate-500 text-xs">{{ $pelanggan->firstItem() + $index }}</td>
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-100">{{ $item->nama }}</p>
                </td>
                <td class="px-5 py-4 text-slate-300">{{ $item->no_hp }}</td>
                <td class="px-5 py-4 text-slate-300">
                    @if($item->tanggal_pembayaran)
                        Tgl {{ $item->tanggal_pembayaran }}
                    @else
                        <span class="text-slate-600">-</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    @if($item->paketHarga)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-blue-500/15 text-blue-300 text-xs font-medium">
                        {{ $item->paketHarga->nama_paket }}
                        <span class="ml-1.5 text-blue-400/60">— Rp {{ number_format($item->paketHarga->harga, 0, ',', '.') }}</span>
                    </span>
                    @else
                    <span class="text-slate-600">-</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-slate-400 max-w-xs truncate">{{ $item->alamat }}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('pelanggan.edit', $item) }}"
                           class="px-3 py-1.5 text-xs font-medium text-blue-400 border border-blue-500/30 rounded-lg hover:bg-blue-500/10 transition-colors">
                            Edit
                        </a>
                        <form action="{{ route('pelanggan.destroy', $item) }}" method="POST"
                              onsubmit="return confirm('Yakin hapus pelanggan {{ $item->nama }}?')">
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
                <td colspan="6" class="px-5 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    Belum ada pelanggan. <a href="{{ route('pelanggan.create') }}" class="text-blue-400 hover:underline">Tambah sekarang</a>.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($pelanggan->hasPages())
    <div class="px-5 py-4 border-t border-slate-800">
        {{ $pelanggan->links() }}
    </div>
    @endif
</div>
@endsection
