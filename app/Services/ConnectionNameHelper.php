<?php

namespace App\Services;

class ConnectionNameHelper
{
    /**
     * Build a standardized provider instance name.
     * Pattern: Whatsapp_{friendlyName}_CWID_{accountId}
     */
    public static function buildProviderName(string $friendlyName, int $accountId): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', $friendlyName);

        return "Whatsapp_{$sanitized}_CWID_{$accountId}";
    }

    /**
     * Extract the user-friendly name from a provider instance name.
     */
    public static function extractFriendlyName(string $providerName, int $accountId): string
    {
        $prefix = 'Whatsapp_';
        $suffix = "_CWID_{$accountId}";

        $name = $providerName;

        if (str_starts_with($name, $prefix)) {
            $name = substr($name, strlen($prefix));
        }

        if (str_ends_with($name, $suffix)) {
            $name = substr($name, 0, -strlen($suffix));
        }

        return $name;
    }

    /**
     * Check if an instance name belongs to a specific account.
     */
    public static function belongsToAccount(string $providerName, int $accountId): bool
    {
        return str_ends_with($providerName, "_CWID_{$accountId}");
    }
}
