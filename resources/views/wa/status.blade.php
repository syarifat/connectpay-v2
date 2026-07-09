@extends('layouts.app')

@section('title', 'Status Fonnte Gateway')
@section('page-title', 'Status WhatsApp Gateway')
@section('page-subtitle', 'Koneksi perangkat WhatsApp menggunakan Fonnte')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Device Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h3 class="text-white font-bold text-base mb-5">Detail Perangkat</h3>
            
            <div class="space-y-4">
                {{-- Status --}}
                <div class="flex items-center justify-between py-2.5 border-b border-slate-800">
                    <span class="text-sm text-slate-400">Status Koneksi</span>
                    <div class="flex items-center gap-2">
                        @if(($status['device_status'] ?? 'disconnect') === 'connect')
                            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-sm font-semibold text-emerald-400 uppercase">Terkoneksi</span>
                        @else
                            <span class="w-3 h-3 rounded-full bg-red-500"></span>
                            <span class="text-sm font-semibold text-red-400 uppercase">Terputus</span>
                        @endif
                    </div>
                </div>

                {{-- Device Name --}}
                <div class="flex items-center justify-between py-2.5 border-b border-slate-800">
                    <span class="text-sm text-slate-400">Nama Perangkat</span>
                    <span class="text-sm text-slate-200 font-medium">{{ $status['name'] ?? '-' }}</span>
                </div>

                {{-- Phone Number --}}
                <div class="flex items-center justify-between py-2.5 border-b border-slate-800">
                    <span class="text-sm text-slate-400">Nomor Terhubung</span>
                    <span class="text-sm text-slate-200 font-mono">{{ $status['device'] ?? '-' }}</span>
                </div>

                {{-- Quota --}}
                <div class="flex items-center justify-between py-2.5 border-b border-slate-800">
                    <span class="text-sm text-slate-400">Sisa Kuota Chat</span>
                    <span class="text-sm text-slate-200 font-semibold">{{ number_format($status['quota'] ?? 0, 0, ',', '.') }}</span>
                </div>

                {{-- Expired --}}
                <div class="flex items-center justify-between py-2.5">
                    <span class="text-sm text-slate-400">Masa Aktif</span>
                    <span class="text-sm text-slate-200">{{ $status['expired'] ?? '-' }}</span>
                </div>
            </div>
            
            @if(($status['device_status'] ?? 'disconnect') !== 'connect')
            <div class="mt-6 p-4 bg-amber-500/10 border border-amber-500/30 text-amber-300 rounded-xl text-xs leading-relaxed">
                ⚠️ <strong>Perangkat Terputus!</strong> Silakan buka dasbor Fonnte Anda di <a href="https://fonnte.com" target="_blank" class="underline text-amber-200">fonnte.com</a> untuk men-scan kembali QR Code WhatsApp.
            </div>
            @endif
        </div>
    </div>

    {{-- Test Send Form --}}
    <div class="lg:col-span-2">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h3 class="text-white font-bold text-base mb-5">Uji Coba Pengiriman Pesan</h3>

            <form action="{{ route('wa-status.test-send') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label for="target" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Nomor WhatsApp Tujuan
                        </label>
                        <input type="text" id="target" name="target" 
                               placeholder="Contoh: 08123456789 atau 628123456789"
                               class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                      placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50"
                               required>
                        <p class="mt-1.5 text-xs text-slate-500">Pastikan nomor aktif dan terhubung ke WhatsApp.</p>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-300 mb-1.5">
                            Isi Pesan
                        </label>
                        <textarea id="message" name="message" rows="4"
                                  placeholder="Tulis pesan uji coba di sini..."
                                  class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 text-slate-100 rounded-xl text-sm
                                         placeholder-slate-600 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500/50 resize-none"
                                  required></textarea>
                    </div>

                    <button type="submit" 
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors shadow-lg shadow-blue-900/30">
                        Kirim Pesan Uji Coba
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
