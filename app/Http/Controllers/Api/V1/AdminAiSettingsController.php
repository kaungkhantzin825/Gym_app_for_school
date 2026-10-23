<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\AiKeyRotatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminAiSettingsController extends Controller
{
    /**
     * Get current AI provider and multi-key configuration.
     */
    public function index(Request $request): JsonResponse
    {
        $geminiKeys = AiKeyRotatorService::getGeminiKeys();
        $mistralKeys = AiKeyRotatorService::getMistralKeys();
        $primary = AiKeyRotatorService::getPrimaryProvider();

        $includeRaw = $request->boolean('include_raw', false);

        $response = [
            'primary_provider'    => $primary,
            'gemini_keys_count'   => count($geminiKeys),
            'mistral_keys_count'  => count($mistralKeys),
            'gemini_keys_masked'  => array_map([AiKeyRotatorService::class, 'maskKey'], $geminiKeys),
            'mistral_keys_masked' => array_map([AiKeyRotatorService::class, 'maskKey'], $mistralKeys),
        ];

        if ($includeRaw) {
            $response['gemini_keys_raw'] = AppSetting::getValue('gemini_api_keys', env('GEMINI_API_KEY', ''));
            $response['mistral_keys_raw'] = AppSetting::getValue('mistral_api_keys', env('MISTRAL_API_KEY', ''));
        }

        return response()->json($response);
    }

    /**
     * Update AI provider and multi-key settings in the database.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'primary_provider' => ['nullable', 'string', 'in:gemini,mistral'],
            'gemini_api_keys'  => ['nullable'],
            'mistral_api_keys' => ['nullable'],
        ]);

        if (isset($validated['primary_provider'])) {
            AppSetting::setValue('ai_primary_provider', $validated['primary_provider']);
        }

        if (array_key_exists('gemini_api_keys', $validated)) {
            $value = $validated['gemini_api_keys'];
            if (is_array($value)) {
                $value = implode("\n", array_filter(array_map('trim', $value)));
            }
            AppSetting::setValue('gemini_api_keys', (string) $value);
        }

        if (array_key_exists('mistral_api_keys', $validated)) {
            $value = $validated['mistral_api_keys'];
            if (is_array($value)) {
                $value = implode("\n", array_filter(array_map('trim', $value)));
            }
            AppSetting::setValue('mistral_api_keys', (string) $value);
        }

        $geminiKeys = AiKeyRotatorService::getGeminiKeys();
        $mistralKeys = AiKeyRotatorService::getMistralKeys();

        return response()->json([
            'message'             => 'AI multi-key settings updated successfully.',
            'primary_provider'    => AiKeyRotatorService::getPrimaryProvider(),
            'gemini_keys_count'   => count($geminiKeys),
            'mistral_keys_count'  => count($mistralKeys),
            'gemini_keys_masked'  => array_map([AiKeyRotatorService::class, 'maskKey'], $geminiKeys),
            'mistral_keys_masked' => array_map([AiKeyRotatorService::class, 'maskKey'], $mistralKeys),
        ]);
    }

    /**
     * Test key connectivity for all configured Gemini and Mistral keys.
     */
    public function test(Request $request): JsonResponse
    {
        $geminiKeys = AiKeyRotatorService::getGeminiKeys();
        $mistralKeys = AiKeyRotatorService::getMistralKeys();

        $geminiResults = [];
        foreach ($geminiKeys as $key) {
            $masked = AiKeyRotatorService::maskKey($key);
            try {
                $res = Http::withHeaders(['x-goog-api-key' => $key])
                    ->timeout(10)
                    ->get('https://generativelanguage.googleapis.com/v1beta/models');

                if ($res->successful()) {
                    $geminiResults[] = ['key' => $masked, 'status' => 'valid', 'code' => $res->status()];
                } else {
                    $geminiResults[] = ['key' => $masked, 'status' => 'error', 'code' => $res->status(), 'error' => $res->json()['error']['message'] ?? $res->body()];
                }
            } catch (\Throwable $e) {
                $geminiResults[] = ['key' => $masked, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        $mistralResults = [];
        foreach ($mistralKeys as $key) {
            $masked = AiKeyRotatorService::maskKey($key);
            try {
                $res = Http::withToken($key)
                    ->timeout(10)
                    ->get('https://api.mistral.ai/v1/models');

                if ($res->successful()) {
                    $mistralResults[] = ['key' => $masked, 'status' => 'valid', 'code' => $res->status()];
                } else {
                    $mistralResults[] = ['key' => $masked, 'status' => 'error', 'code' => $res->status(), 'error' => $res->json()['message'] ?? $res->body()];
                }
            } catch (\Throwable $e) {
                $mistralResults[] = ['key' => $masked, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'gemini_results'  => $geminiResults,
            'mistral_results' => $mistralResults,
        ]);
    }
}
