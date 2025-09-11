<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\McpClient;
use Boots\Enums\Platform;

class VSCode extends CodeEnvironment implements McpClient
{
    public function name(): string
    {
        return 'vscode';
    }

    public function displayName(): string
    {
        return 'VS Code';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => ['/Applications/Visual Studio Code.app'],
            ],
            Platform::Linux => [
                'command' => 'which code',
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\Microsoft VS Code',
                    '%LOCALAPPDATA%\\Programs\\Microsoft VS Code',
                ],
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.vscode'],
        ];
    }

    public function mcpConfigPath(): string
    {
        return '.vscode/mcp.json';
    }

    public function mcpConfigKey(): string
    {
        return 'servers';
    }
}
