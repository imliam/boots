<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Enums\Platform;

class Roocode extends CodeEnvironment implements Agent
{
    public function name(): string
    {
        return 'roocode';
    }

    public function displayName(): string
    {
        return 'Roocode';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        // Roocode doesn't have system-wide detection as it's a VS Code extension
        return [
            'files' => [],
        ];
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.roo'],
            'files' => ['.roorules'],
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
        return '.roo/rules/boots.md';
    }
}
