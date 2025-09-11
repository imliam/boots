<?php

namespace Boots;

use RuntimeException;

class File
{
    public static function basePath(string $path = ''): string
    {
        $currentWorkingDirectory = getcwd();

        if ($currentWorkingDirectory === false) {
            throw new RuntimeException('Unable to determine current working directory');
        }

        if ($path === '') {
            return $currentWorkingDirectory;
        }

        return $currentWorkingDirectory.'/'.trim($path, '/');
    }
}
