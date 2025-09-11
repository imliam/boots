<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Detection\DetectionStrategyFactory;
use Boots\Enums\McpInstallationStrategy;
use Boots\Enums\Platform;
use Boots\File;
use Boots\Mcp\FileWriter;
use Symfony\Component\Process\Process;

abstract class CodeEnvironment
{
    public bool $useAbsolutePathForMcp = false;

    public function __construct(protected readonly DetectionStrategyFactory $strategyFactory = new DetectionStrategyFactory) {}

    abstract public function name(): string;

    abstract public function displayName(): string;

    public function agentName(): ?string
    {
        return $this->displayName();
    }

    public function mcpClientName(): ?string
    {
        return $this->displayName();
    }

    public function useAbsolutePathForMcp(): bool
    {
        return $this->useAbsolutePathForMcp;
    }

    public function getPhpPath(): string
    {
        return $this->useAbsolutePathForMcp() ? PHP_BINARY : 'php';
    }

    public function getArtisanPath(): string
    {
        return $this->useAbsolutePathForMcp() ? File::basePath('artisan') : 'artisan';
    }

    /**
     * Get the detection configuration for system-wide installation detection.
     *
     * @return array{paths?: string[], command?: string, files?: string[]}
     */
    abstract public function systemDetectionConfig(Platform $platform): array;

    /**
     * Get the detection configuration for project-specific detection.
     *
     * @return array{paths?: string[], files?: string[]}
     */
    abstract public function projectDetectionConfig(): array;

    public function detectOnSystem(Platform $platform): bool
    {
        $config = $this->systemDetectionConfig($platform);
        $strategy = $this->strategyFactory->makeFromConfig($config);

        return $strategy->detect($config, $platform);
    }

    public function detectInProject(string $basePath): bool
    {
        $config = array_merge($this->projectDetectionConfig(), ['basePath' => $basePath]);
        $strategy = $this->strategyFactory->makeFromConfig($config);

        return $strategy->detect($config);
    }

    public function isAgent(): bool
    {
        return $this->agentName() && $this instanceof Agent;
    }

    public function isMcpClient(): bool
    {
        return $this->mcpClientName() && $this instanceof McpClient;
    }

    public function mcpInstallationStrategy(): McpInstallationStrategy
    {
        return McpInstallationStrategy::FILE;
    }

    public function shellMcpCommand(): ?string
    {
        return null;
    }

    public function mcpConfigPath(): ?string
    {
        return null;
    }

    public function frontmatter(): bool
    {
        return false;
    }

    public function mcpConfigKey(): string
    {
        return 'mcpServers';
    }

    /**
     * Install MCP server using the appropriate strategy.
     *
     * @param  array<int, string>  $args
     * @param  array<string, string>  $env
     */
    public function installMcp(string $key, string $command, array $args = [], array $env = []): bool
    {
        return match ($this->mcpInstallationStrategy()) {
            McpInstallationStrategy::SHELL => $this->installShellMcp($key, $command, $args, $env),
            McpInstallationStrategy::FILE => $this->installFileMcp($key, $command, $args, $env),
            McpInstallationStrategy::NONE => false
        };
    }

    /**
     * Install MCP server using a shell command strategy.
     *
     * @param  array<int, string>  $args
     * @param  array<string, string>  $env
     */
    protected function installShellMcp(string $key, string $command, array $args = [], array $env = []): bool
    {
        $shellCommand = $this->shellMcpCommand();
        if ($shellCommand === null) {
            return false;
        }

        // Build environment string
        $envString = '';
        foreach ($env as $envKey => $value) {
            $envKey = strtoupper($envKey);
            $envString .= "-e {$envKey}=\"{$value}\" ";
        }

        // Replace placeholders in shell command
        $command = str_replace([
            '{key}',
            '{command}',
            '{args}',
            '{env}',
        ], [
            $key,
            $command,
            implode(' ', array_map(fn ($arg) => '"'.$arg.'"', $args)),
            trim($envString),
        ], $shellCommand);

        $process = Process::fromShellCommandline($command);
        $process->run();

        return $process->isSuccessful() || str_contains($process->getErrorOutput(), 'already exists');
    }

    /**
     * Install MCP server using a file-based configuration strategy.
     *
     * @param  array<int, string>  $args
     * @param  array<string, string>  $env
     */
    protected function installFileMcp(string $key, string $command, array $args = [], array $env = []): bool
    {
        $path = $this->mcpConfigPath();
        if (! $path) {
            return false;
        }

        return (new FileWriter($path))
            ->configKey($this->mcpConfigKey())
            ->addServer($key, $command, $args, $env)
            ->save();
    }
}
