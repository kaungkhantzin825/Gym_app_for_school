<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Throwable;

class AiKeyRotatorService
{
    private static int $geminiPointer = 0;
    private static int $mistralPointer = 0;

    /**
     * Get list of configured Gemini API keys (from app_settings and .env).
     *
     * @return array<int, string>
     */
    public static function getGeminiKeys(): array
    {
        $keys = [];

        // 1. Database setting
        $dbKeys = AppSetting::getValue('gemini_api_keys')
            ?? AppSetting::getValue('gemini_api_key')
            ?? AppSetting::getValue('GEMINI_API_KEY');
        if ($dbKeys) {
            $keys = array_merge($keys, self::parseKeys($dbKeys));
        }

        // 2. Environment variable
        $envKey = env('GEMINI_API_KEY');
        if ($envKey) {
            $keys = array_merge($keys, self::parseKeys($envKey));
        }

        return array_values(array_unique(array_filter($keys)));
    }

    /**
     * Get list of configured Mistral API keys (from app_settings and .env).
     *
     * @return array<int, string>
     */
    public static function getMistralKeys(): array
    {
        $keys = [];

        // 1. Database setting
        $dbKeys = AppSetting::getValue('mistral_api_keys')
            ?? AppSetting::getValue('mistral_api_key')
            ?? AppSetting::getValue('MISTRAL_API_KEY');
        if ($dbKeys) {
            $keys = array_merge($keys, self::parseKeys($dbKeys));
        }

        // 2. Environment variable
        $envKey = env('MISTRAL_API_KEY');
        if ($envKey) {
            $keys = array_merge($keys, self::parseKeys($envKey));
        }

        return array_values(array_unique(array_filter($keys)));
    }

    /**
     * Get primary provider (defaults to 'gemini').
     */
    public static function getPrimaryProvider(): string
    {
        $configured = AppSetting::getValue('ai_primary_provider')
            ?? AppSetting::getValue('ai_provider')
            ?? config('ai.default', 'gemini');

        return in_array($configured, ['gemini', 'mistral']) ? $configured : 'gemini';
    }

    /**
     * Parse keys from comma, newline, semicolon, or JSON array string.
     *
     * @param  mixed  $input
     * @return array<int, string>
     */
    public static function parseKeys(mixed $input): array
    {
        if (is_array($input)) {
            $clean = [];
            foreach ($input as $k) {
                if (is_string($k) && trim($k) !== '') {
                    $clean[] = trim($k);
                }
            }
            return $clean;
        }

        if (! is_string($input)) {
            return [];
        }

        $input = trim($input);
        if ($input === '') {
            return [];
        }

        // If JSON array string
        if (str_starts_with($input, '[') && str_ends_with($input, ']')) {
            $decoded = json_decode($input, true);
            if (is_array($decoded)) {
                return self::parseKeys($decoded);
            }
        }

        // Split by newlines, commas, or semicolons
        $parts = preg_split('/[\r\n,;]+/', $input);
        $clean = [];
        foreach ($parts as $part) {
            $trimmed = trim($part, " \t\n\r\0\x0B\"'");
            if ($trimmed !== '') {
                $clean[] = $trimmed;
            }
        }

        return $clean;
    }

    /**
     * Run an AI operation with automatic key rotation and cross-provider failover.
     *
     * @template T
     * @param  callable(string $provider, string $key): T  $operation
     * @param  string|null  $preferredProvider
     * @return T
     * @throws Throwable
     */
    public static function runWithRotation(callable $operation, ?string $preferredProvider = null)
    {
        $primary = $preferredProvider ?? self::getPrimaryProvider();
        $secondary = $primary === 'gemini' ? 'mistral' : 'gemini';

        $providersToTry = [$primary, $secondary];
        $allExceptions = [];

        foreach ($providersToTry as $provider) {
            $keys = $provider === 'gemini' ? self::getGeminiKeys() : self::getMistralKeys();

            if (empty($keys)) {
                Log::warning("AiKeyRotator: No keys configured for provider [{$provider}]. Skipping to alternate.");
                continue;
            }

            $keyCount = count($keys);
            $startIndex = $provider === 'gemini'
                ? self::$geminiPointer % $keyCount
                : self::$mistralPointer % $keyCount;

            // Loop through all keys for this provider starting from the current pointer
            for ($i = 0; $i < $keyCount; $i++) {
                $currentIndex = ($startIndex + $i) % $keyCount;
                $activeKey = $keys[$currentIndex];
                $maskedKey = self::maskKey($activeKey);

                // Dynamically inject configuration for laravel/ai
                Config::set("ai.providers.{$provider}.key", $activeKey);
                Config::set('ai.default', $provider);

                try {
                    $result = $operation($provider, $activeKey);

                    // Successfully executed! Persist current working key pointer
                    if ($provider === 'gemini') {
                        self::$geminiPointer = $currentIndex;
                    } else {
                        self::$mistralPointer = $currentIndex;
                    }

                    return $result;
                } catch (Throwable $e) {
                    $allExceptions[] = "[{$provider}:{$maskedKey}] {$e->getMessage()}";
                    Log::warning("AiKeyRotator: Provider [{$provider}] key {$maskedKey} failed: {$e->getMessage()}. Rotating to next key...");

                    // Advance pointer to next key
                    if ($provider === 'gemini') {
                        self::$geminiPointer = ($currentIndex + 1) % $keyCount;
                    } else {
                        self::$mistralPointer = ($currentIndex + 1) % $keyCount;
                    }
                }
            }

            Log::error("AiKeyRotator: All {$keyCount} key(s) failed for provider [{$provider}]. Attempting failover...");
        }

        throw new \RuntimeException(
            'All AI providers and API keys exhausted. Errors: ' . implode(' | ', $allExceptions)
        );
    }

    /**
     * Mask a key for safe display (e.g. AIzaSy...AEY).
     */
    public static function maskKey(string $key): string
    {
        $len = strlen($key);
        if ($len <= 8) {
            return '***';
        }
        return substr($key, 0, 6) . '...' . substr($key, -4);
    }
}
