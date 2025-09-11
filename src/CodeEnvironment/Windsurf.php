<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Enums\Platform;

class Windsurf extends CodeEnvironment implements Agent
{
    public function name(): string
    {
        return 'windsurf';
    }

    public function displayName(): string
    {
        return 'Windsurf';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => ['/Applications/Windsurf.app'],
            ],
            Platform::Linux => [
                'paths' => [
                    '/opt/windsurf',
                    '/usr/local/bin/windsurf',
                    '~/.local/bin/windsurf',
                    '/snap/bin/windsurf',
                ],
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\Windsurf',
                    '%LOCALAPPDATA%\\Programs\\Windsurf',
                    '%APPDATA%\\Windsurf',
                ],
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.windsurf'],
        ];
    }

    public function agentName(): string
    {
        return 'Cascade';
    }

    public function guidelinesPath(): string
    {
        return '.windsurf/rules/boots.md';
    }
}
