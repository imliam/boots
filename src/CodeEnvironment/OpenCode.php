<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\McpInstallationStrategy;
use Boots\Enums\Platform;

class OpenCode extends CodeEnvironment implements Agent, McpClient
{
    public function name(): string
    {
        return 'opencode';
    }

    public function displayName(): string
    {
        return 'opencode'; // intentional
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin, Platform::Linux => [
                'command' => 'which opencode',
            ],
            Platform::Windows => [
                'command' => 'where opencode 2>null',
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'files' => ['AGENTS.md', 'opencode.json'],
        ];
    }

    public function mcpInstallationStrategy(): McpInstallationStrategy
    {
        return McpInstallationStrategy::FILE;
    }

    public function mcpConfigPath(): string
    {
        return 'opencode.json';
    }

    public function guidelinesPath(): string
    {
        return 'AGENTS.md';
    }

    public function mcpConfigKey(): string
    {
        return 'mcp';
    }
}
