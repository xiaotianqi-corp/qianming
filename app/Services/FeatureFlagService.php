<?php

namespace App\Services;

class FeatureFlagService
{
    public static function isEnabled(string $feature): bool
    {
        // Esto puede leer de un archivo .env o de una tabla de settings en la BD
        return (bool) config("features.$feature", true);
    }
}