<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\Platform;

class Cursor extends CodeEnvironment implements Agent, McpClient
{
    public function name(): string
    {
        return 'cursor';
    }

    public function displayName(): string
    {
        return 'Cursor';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => ['/Applications/Cursor.app'],
            ],
            Platform::Linux => [
                'paths' => [
                    '/opt/cursor',
                    '/usr/local/bin/cursor',
                    '~/.local/bin/cursor',
                ],
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\Cursor',
                    '%LOCALAPPDATA%\\Programs\\Cursor',
                ],
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.cursor'],
        ];
    }

    public function mcpConfigPath(): string
    {
        return '.cursor/mcp.json';
    }

    public function guidelinesPath(): string
    {
        return '.cursor/rules/boots.mdc';
    }

    public function frontmatter(): bool
    {
        return true;
    }
}
