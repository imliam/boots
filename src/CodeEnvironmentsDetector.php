<?php

namespace Boots;

use Boots\CodeEnvironment\ClaudeCode;
use Boots\CodeEnvironment\Cline;
use Boots\CodeEnvironment\CodeEnvironment;
use Boots\CodeEnvironment\Copilot;
use Boots\CodeEnvironment\Cursor;
use Boots\CodeEnvironment\JetBrainsIDEA;
use Boots\CodeEnvironment\KiloCode;
use Boots\CodeEnvironment\Kiro;
use Boots\CodeEnvironment\Roocode;
use Boots\CodeEnvironment\Trae;
use Boots\CodeEnvironment\VSCode;
use Boots\CodeEnvironment\Warp;
use Boots\CodeEnvironment\Windsurf;
use Boots\Enums\Platform;
use Illuminate\Support\Collection;

class CodeEnvironmentsDetector
{
    /** @var array<string, class-string<CodeEnvironment>> */
    private array $programs = [
        'idea' => JetBrainsIDEA::class,
        'vscode' => VSCode::class,
        'cursor' => Cursor::class,
        'claudecode' => ClaudeCode::class,
        'copilot' => Copilot::class,
        'warp' => Warp::class,
        'windsurf' => Windsurf::class,
        'cline' => Cline::class,
        'kilocode' => KiloCode::class,
        'roocode' => Roocode::class,
        'trae' => Trae::class,
        'kiro' => Kiro::class,
    ];

    /**
     * Detect installed applications on the current platform.
     *
     * @return array<string>
     */
    public function discoverSystemInstalledCodeEnvironments(): array
    {
        $platform = Platform::current();

        return $this->getCodeEnvironments()
            ->filter(fn (CodeEnvironment $program) => $program->detectOnSystem($platform))
            ->map(fn (CodeEnvironment $program) => $program->name())
            ->values()
            ->toArray();
    }

    /**
     * Detect applications used in the current project.
     *
     * @return array<string>
     */
    public function discoverProjectInstalledCodeEnvironments(string $basePath): array
    {
        return $this->getCodeEnvironments()
            ->filter(fn ($program) => $program->detectInProject($basePath))
            ->map(fn ($program) => $program->name())
            ->values()
            ->toArray();
    }

    /**
     * Get all registered code environments.
     *
     * @return Collection<string, CodeEnvironment>
     */
    public function getCodeEnvironments(): Collection
    {
        return collect($this->programs)->map(fn (string $className) => new $className);
    }
}
