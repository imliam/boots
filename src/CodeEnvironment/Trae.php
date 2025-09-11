<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\Platform;

class Trae extends CodeEnvironment implements Agent, McpClient
{
    public function name(): string
    {
        return 'trae';
    }

    public function displayName(): string
    {
        return 'Trae';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => ['/Applications/Trae.app'],
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\Trae',
                    '%LOCALAPPDATA%\\Programs\\Trae',
                ],
            ],
            Platform::Linux => [
                // Trae is not supported on Linux
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.trae'],
        ];
    }

    public function guidelinesPath(): string
    {
        return '.trae/rules/project_rules.md';
    }

    public function frontmatter(): bool
    {
        return false;
    }

    public function mcpConfigPath(): string
    {
        return match (Platform::current()) {
            Platform::Darwin => '~/Library/Application Support/Trae/User/mcp.json',
            Platform::Windows => '%APPDATA%\\Trae\\User\\mcp.json',
            Platform::Linux => '', // Not supported
        };
    }

    public function mcpConfigKey(): string
    {
        return 'mcpServers';
    }
}
