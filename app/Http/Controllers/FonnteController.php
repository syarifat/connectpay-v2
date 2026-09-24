<?php

namespace App\Http\Controllers;

use App\Models\WaChatHistory;
use App\Services\FonnteService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class FonnteController extends Controller
{
    protected FonnteService $fonnte;

    public function __construct(FonnteService $fonnte)
    {
        $this->fonnte = $fonnte;
    }

    /**
     * Show Fonnte connect status page
     */
    public function status(): View
    {
        $status = $this->fonnte->getDeviceStatus();
        return view('wa.status', compact('status'));
    }

    /**
     * Show Chat History list (filter by date)
     */
    public function history(Request $request): View
    {
        $query = WaChatHistory::with('pelanggan')->latest();

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $history = $query->paginate(20)->withQueryString();
        $failedCount = WaChatHistory::where('status', '!=', 'sent')->count();

        return view('wa.history', compact('history', 'failedCount'));
    }

    /**
     * Resend a single failed WhatsApp message
     */
    public function resend($id): RedirectResponse
    {
        $chat = WaChatHistory::with('pelanggan')->findOrFail($id);

        if ($chat->pelanggan && !$chat->pelanggan->is_aktif) {
            return redirect()->back()->with('error', "Gagal: Pelanggan {$chat->pelanggan->nama} sedang nonaktif. Pesan tidak dikirim.");
        }

        $target = $chat->target;
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        $result = $this->fonnte->sendMessage($target, $chat->message);

        $chat->update([
            'status' => $result['success'] ? 'sent' : 'failed',
            'response' => json_encode($result['raw'] ?? ['error' => $result['message'] ?? 'Unknown error']),
            'updated_at' => now(),
        ]);

        if ($result['success']) {
            return redirect()->back()->with('success', "Pesan ke {$chat->target} berhasil dikirim ulang.");
        }

        return redirect()->back()->with('error', 'Gagal mengirim ulang pesan: ' . ($result['message'] ?? 'Unknown error'));
    }

    /**
     * Resend all failed WhatsApp messages
     */
    public function resendAllFailed(): RedirectResponse
    {
        @set_time_limit(0);

        $failedChats = WaChatHistory::where('status', '!=', 'sent')
            ->with('pelanggan')
            ->get();

        if ($failedChats->isEmpty()) {
            return redirect()->back()->with('info', 'Tidak ada pesan gagal untuk dikirim ulang.');
        }

        $countSuccess = 0;
        $countFailed  = 0;
        $countSkipped = 0;
        $totalFailed  = $failedChats->count();
        $currentIndex = 0;

        foreach ($failedChats as $chat) {
            $currentIndex++;

            if ($chat->pelanggan && !$chat->pelanggan->is_aktif) {
                $countSkipped++;
                continue;
            }

            $target = $chat->target;
            if (str_starts_with($target, '0')) {
                $target = '62' . substr($target, 1);
            }

            $result = $this->fonnte->sendMessage($target, $chat->message);

            $chat->update([
                'status' => $result['success'] ? 'sent' : 'failed',
                'response' => json_encode($result['raw'] ?? ['error' => $result['message'] ?? 'Unknown error']),
                'updated_at' => now(),
            ]);

            if ($result['success']) {
                $countSuccess++;
            } else {
                $countFailed++;
            }

            // Proteksi jeda 15 detik antar pesan
            if ($currentIndex < $totalFailed) {
                sleep(15);
            }
        }

        $msg = "Kirim ulang selesai: {$countSuccess} berhasil, {$countFailed} gagal";
        if ($countSkipped > 0) {
            $msg .= ", {$countSkipped} dilewati karena pelanggan nonaktif";
        }
        $msg .= ".";

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Test sending WhatsApp message
     */
    public function testSend(Request $request): RedirectResponse
    {
        $request->validate([
            'target' => 'required|string',
            'message' => 'required|string',
        ]);

        $target = $request->target;
        if (str_starts_with($target, '0')) {
            $target = '62' . substr($target, 1);
        }

        $result = $this->fonnte->sendMessage($target, $request->message);

        if ($result['success']) {
            // Save to logs as manual test
            WaChatHistory::create([
                'target' => $request->target,
                'message' => $request->message,
                'status' => 'sent',
                'response' => json_encode($result['raw'] ?? []),
            ]);

            return redirect()->back()->with('success', 'Pesan uji coba berhasil terkirim ke WhatsApp.');
        }

        return redirect()->back()->with('error', 'Gagal mengirim pesan: ' . ($result['message'] ?? 'Unknown error'));
    }
}
