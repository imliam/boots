<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\McpInstallationStrategy;
use Boots\Enums\Platform;

class ClaudeCode extends CodeEnvironment implements Agent, McpClient
{
    public function name(): string
    {
        return 'claudecode';
    }

    public function displayName(): string
    {
        return 'Claude Code';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin, Platform::Linux => [
                'command' => 'which claude',
            ],
            Platform::Windows => [
                'command' => 'where claude 2>nul',
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.claude'],
            'files' => ['CLAUDE.md'],
        ];
    }

    public function mcpInstallationStrategy(): McpInstallationStrategy
    {
        return McpInstallationStrategy::FILE;
    }

    public function mcpConfigPath(): string
    {
        return '.mcp.json';
    }

    public function guidelinesPath(): string
    {
        return 'CLAUDE.md';
    }
}
