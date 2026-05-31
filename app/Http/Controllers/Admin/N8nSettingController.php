<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use Inertia\Response;

class N8nSettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/N8n', [
            'webhookUrl' => Setting::getValue('n8n_webhook_url', 'https://n8n-mt8umikivytz.n8x.biz.id/webhook-test/368036b6-64ef-43a1-b296-3dc3ec12ebef'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'webhook_url' => ['required', 'url'],
        ]);

        Setting::setValue('n8n_webhook_url', $validated['webhook_url']);

        return redirect()->route('admin.settings.n8n.edit')->with('success', 'Webhook N8n berjaya dikemaskini.');
    }

    public function test(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'webhook_url' => ['required', 'url'],
        ]);

        try {
            $response = Http::timeout(10)->post($validated['webhook_url'], [
                'type' => 'test',
                'message' => 'Test dari StickerTermurah Admin',
                'link_gambar' => null,
            ]);

            return response()->json([
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
