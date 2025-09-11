<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Enums\Platform;

class KiloCode extends CodeEnvironment implements Agent
{
    public function name(): string
    {
        return 'kilocode';
    }

    public function displayName(): string
    {
        return 'Kilo Code';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        // Kilo Code doesn't have system-wide detection as it's a VS Code extension
        return [
            'files' => [],
        ];
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.kilocode'],
            'files' => ['.kilocoderules'],
        ];
    }

    public function detectOnSystem(Platform $platform): bool
    {
        return false;
    }

    public function mcpClientName(): ?string
    {
        return null;
    }

    public function guidelinesPath(): string
    {
        return '.kilocode/rules/boots.md';
    }
}
