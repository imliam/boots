<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Enums\Platform;

class Warp extends CodeEnvironment implements Agent
{
    public function name(): string
    {
        return 'warp';
    }

    public function displayName(): string
    {
        return 'Warp';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => [
                    '/Applications/Warp.app',
                    '/Applications/WarpPreview.app',
                ],
            ],
            Platform::Linux => [
                'command' => 'which warp-terminal',
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\Warp',
                    '%ProgramFiles%\\WarpPreview',
                    '%LOCALAPPDATA%\\Programs\\Warp',
                    '%LOCALAPPDATA%\\Programs\\WarpPreview',
                ],
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'files' => ['WARP.md'],
        ];
    }

    public function guidelinesPath(): string
    {
        return 'WARP.md';
    }

    public function frontmatter(): bool
    {
        return false;
    }
}
