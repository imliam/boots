<?php

namespace Boots\Detection;

use Boots\Contracts\DetectionStrategy;
use Boots\Enums\Platform;
use Symfony\Component\Process\Process;

class CommandDetectionStrategy implements DetectionStrategy
{
    public function detect(array $config, ?Platform $platform = null): bool
    {
        if (! isset($config['command'])) {
            return false;
        }

        $process = new Process([$config['command']]);
        $process->run();

        return $process->isSuccessful();
    }
}
