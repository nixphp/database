<?php

declare(strict_types=1);

namespace NixPHP\Database\Support;

final class MigrationRegistry
{
    /**
     * @var string[]
     */
    private static array $paths = [];

    public static function addPath(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $normalized = rtrim($path, '/\\');

        if (in_array($normalized, self::$paths, true)) {
            return;
        }

        self::$paths[] = $normalized;
    }

    /**
     * @return string[]
     */
    public static function getPaths(): array
    {
        return self::$paths;
    }
}
