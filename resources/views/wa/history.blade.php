@extends('layouts.app')

@section('title', 'Riwayat Chat WhatsApp')
@section('page-title', 'Riwayat Chat WhatsApp')
@section('page-subtitle', 'Log pengiriman pengingat tagihan via Fonnte')

@section('content')
{{-- Filter & Search --}}
<div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mb-6 shadow-md">
    <form action="{{ route('wa-history.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label for="date" class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                Filter Tanggal Kirim
            </label>
            <input type="date" id="date" name="date" value="{{ request('date') }}"
                   class="px-4 py-2 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm focus:outline-none focus:border-blue-500">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors">
                Terapkan Filter
            </button>
            
            @if(request()->filled('date'))
                <a href="{{ route('wa-history.index') }}"
                   class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-semibold rounded-xl transition-colors">
                    Hapus Filter
                </a>
            @endif
        </div>

        @if(isset($failedCount) && $failedCount > 0)
            <div class="ml-auto">
                <form action="{{ route('wa-history.resend-all') }}" method="POST"
                      onsubmit="return confirm('Kirim ulang semua ({{ $failedCount }}) pesan yang gagal terkirim?')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-amber-900/30">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        Kirim Ulang Semua Gagal ({{ $failedCount }})
                    </button>
                </form>
            </div>
        @endif
    </form>
</div>

{{-- History Table --}}
<div class="bg-slate-900 rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-slate-800 bg-slate-800/50">
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider w-10">#</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Tanggal Kirim</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">No. HP / Target</th>
                <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Isi Pesan</th>
                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($history as $index => $item)
            <tr class="hover:bg-slate-800/40 transition-colors">
                <td class="px-5 py-4 text-slate-500 text-xs">
                    {{ $history->firstItem() + $index }}
                </td>
                <td class="px-5 py-4 text-slate-300 text-xs">
                    {{ $item->created_at->format('d M Y H:i:s') }}
                </td>
                <td class="px-5 py-4">
                    @if($item->pelanggan)
                        <p class="font-semibold text-slate-100 flex items-center gap-1.5">
                            {{ $item->pelanggan->nama }}
                            @if(!$item->pelanggan->is_aktif)
                                <span class="text-[10px] font-normal px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">Nonaktif</span>
                            @endif
                        </p>
                    @else
                        <span class="text-slate-500 font-medium">Uji Coba Manual</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-slate-300 font-mono text-xs">
                    {{ $item->target }}
                </td>
                <td class="px-5 py-4 text-slate-300 max-w-sm truncate whitespace-pre-line" title="{{ $item->message }}">
                    {{ Str::limit($item->message, 120) }}
                </td>
                <td class="px-5 py-4 text-center">
                    @if($item->status === 'sent')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-emerald-500/15 text-emerald-400 text-xs font-medium">
                            Terkirim
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-red-500/15 text-red-400 text-xs font-medium" title="{{ $item->response }}">
                            Gagal
                        </span>
                    @endif
                </td>
                <td class="px-5 py-4 text-center">
                    @if($item->status !== 'sent')
                        @if($item->pelanggan && !$item->pelanggan->is_aktif)
                            <span class="text-xs text-slate-500 italic" title="Pelanggan nonaktif, pesan tidak dapat dikirim">
                                Pelanggan Nonaktif
                            </span>
                        @else
                            <form action="{{ route('wa-history.resend', $item->id) }}" method="POST"
                                  onsubmit="return confirm('Kirim ulang pesan ini ke {{ $item->target }}?')">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-300 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 rounded-lg transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Kirim Ulang
                                </button>
                            </form>
                        @endif
                    @else
                        <span class="text-slate-600 text-xs">-</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center text-slate-500">
                    <svg class="w-10 h-10 mx-auto mb-3 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    Belum ada riwayat pengiriman pesan WhatsApp.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
    @if($history->hasPages())
    <div class="px-5 py-4 border-t border-slate-800">
        {{ $history->links() }}
    </div>
    @endif
</div>
@endsection
