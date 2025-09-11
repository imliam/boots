<?php

namespace Boots;

class File
{
    public static function projectBasePath(string $path = ''): string
    {
        $projectBasePath = dirname(static::packageBasePath(), 3);

        if (file_exists($projectBasePath.'/vendor/autoload.php')) {
            return $projectBasePath.'/'.trim($path, '/');
        }

        return static::packageBasePath().'/'.trim($path, '/');
    }

    public static function packageBasePath(): string
    {
        return dirname(__DIR__);
    }
}
