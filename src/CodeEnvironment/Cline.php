<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Enums\Platform;

class Cline extends CodeEnvironment implements Agent
{
    public function name(): string
    {
        return 'cline';
    }

    public function displayName(): string
    {
        return 'Cline';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        // Cline doesn't have system-wide detection as it's a VS Code extension
        return [
            'files' => [],
        ];
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.clinerules'],
            'files' => ['.clinerules'],
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
        return '.clinerules/boots.md';
    }
}
