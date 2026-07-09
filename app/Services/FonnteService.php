<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    protected string $token;

    public function __construct()
    {
        $this->token = config('services.fonnte.token', '');
    }

    /**
     * Send message (optional with attachment url / local file path)
     */
    public function sendMessage(string $target, string $message, ?string $url = null, ?string $filename = null, ?string $filePath = null): array
    {
        if (empty($this->token)) {
            Log::warning('Fonnte token is empty. Message not sent.');
            return ['success' => false, 'message' => 'Fonnte token is empty'];
        }

        try {
            if ($filePath && file_exists($filePath)) {
                // Send as multipart/form-data for direct file upload (bypasses Cloudflare block)
                $request = Http::withHeaders([
                    'Authorization' => $this->token
                ])->attach(
                    'file', 
                    file_get_contents($filePath), 
                    $filename ?? basename($filePath)
                );

                $payload = [
                    'target' => $target,
                    'message' => $message,
                ];

                $response = $request->post('https://api.fonnte.com/send', $payload);
            } else {
                // Send as standard JSON payload
                $payload = [
                    'target' => $target,
                    'message' => $message,
                ];

                if ($url) {
                    $payload['url'] = $url;
                }
                if ($filename) {
                    $payload['filename'] = $filename;
                }

                $response = Http::withHeaders([
                    'Authorization' => $this->token
                ])->post('https://api.fonnte.com/send', $payload);
            }

            $result = $response->json();
            return [
                'success' => $result['status'] ?? false,
                'message' => $result['reason'] ?? ($result['detail'] ?? 'No reason provided'),
                'raw' => $result
            ];
        } catch (\Exception $e) {
            Log::error('Fonnte API error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Check device status
     */
    public function getDeviceStatus(): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'device_status' => 'disconnect', 'message' => 'Fonnte token is empty'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token
            ])->post('https://api.fonnte.com/device');

            $result = $response->json();
            return [
                'success' => $result['status'] ?? false,
                'device_status' => $result['device_status'] ?? 'disconnect',
                'name' => $result['name'] ?? '-',
                'device' => $result['device'] ?? '-',
                'quota' => $result['quota'] ?? 0,
                'expired' => $result['expired'] ?? '-',
                'raw' => $result
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'device_status' => 'disconnect', 'message' => $e->getMessage()];
        }
    }
}
