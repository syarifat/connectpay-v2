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

        return view('wa.history', compact('history'));
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
