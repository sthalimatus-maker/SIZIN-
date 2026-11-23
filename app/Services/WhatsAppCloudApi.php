<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class WhatsAppCloudApi
{
    /** @return array{sent: bool, detail: string} */
    public function sendTemplate(string $recipient, string $message): array
    {
        $token = config('services.whatsapp.access_token');
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $templateName = config('services.whatsapp.template_name');

        if (! $token || ! $phoneNumberId || ! $templateName) {
            return ['sent' => false, 'detail' => 'Konfigurasi WhatsApp belum lengkap (token, phone number ID, atau template).'];
        }

        $to = preg_replace('/\D+/', '', $recipient) ?? '';
        if (str_starts_with($to, '0')) {
            $to = '62' . substr($to, 1);
        }
        if (strlen($to) < 8) {
            return ['sent' => false, 'detail' => 'Nomor WhatsApp tujuan tidak valid. Gunakan format internasional, misalnya 62812…'];
        }

        $version = config('services.whatsapp.graph_version', 'v27.0');
        $endpoint = "https://graph.facebook.com/{$version}/{$phoneNumberId}/messages";

        try {
            $response = Http::withToken($token)->acceptJson()->timeout(20)->post($endpoint, [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => ['code' => config('services.whatsapp.template_language', 'id')],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => [['type' => 'text', 'text' => mb_substr($message, 0, 1024)]],
                    ]],
                ],
            ]);

            if (! $response->successful()) {
                return ['sent' => false, 'detail' => $response->json('error.message') ?: 'WhatsApp API menolak pesan (HTTP ' . $response->status() . ').'];
            }

            $messageId = $response->json('messages.0.id');
            return $messageId
                ? ['sent' => true, 'detail' => 'Terkirim. ID pesan: ' . $messageId]
                : ['sent' => false, 'detail' => 'WhatsApp API tidak mengembalikan ID pesan.'];
        } catch (Throwable $exception) {
            report($exception);
            return ['sent' => false, 'detail' => 'Tidak dapat menghubungi WhatsApp API: ' . $exception->getMessage()];
        }
    }
}
