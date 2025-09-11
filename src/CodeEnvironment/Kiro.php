<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\Platform;

class Kiro extends CodeEnvironment implements Agent, McpClient
{
    public function name(): string
    {
        return 'kiro';
    }

    public function displayName(): string
    {
        return 'Kiro';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => [
                    '/Applications/Kiro.app',
                    '~/Applications/Kiro.app',
                ],
            ],
            Platform::Linux => [
                'paths' => [
                    '/opt/kiro',
                    '/usr/local/bin/kiro',
                    '~/.local/bin/kiro',
                    '/snap/bin/kiro',
                ],
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\Kiro',
                    '%LOCALAPPDATA%\\Programs\\Kiro',
                    '%APPDATA%\\Kiro',
                ],
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.kiro'],
        ];
    }

    public function mcpConfigPath(): string
    {
        return '.kiro/settings/mcp.json';
    }

    public function guidelinesPath(): string
    {
        return '.kiro/steering/boots.md';
    }

    public function frontmatter(): bool
    {
        return true;
    }
}
