<?php

namespace Boots\Commands;

use Boots\Cli\DisplayHelper;
use Boots\CodeEnvironment\CodeEnvironment;
use Boots\CodeEnvironmentsDetector;
use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\File;
use Boots\GuidelineComposer;
use Boots\GuidelineWriter;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Laravel\Prompts\Concerns\Colors;
use Laravel\Prompts\Terminal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;

#[AsCommand(
    name: 'boots',
    description: 'Run boots over the project\'s guidelines.',
)]
class BootsCommand extends Command
{
    use Colors;

    private InputInterface $input;

    private OutputInterface $output;

    private CodeEnvironmentsDetector $codeEnvironmentsDetector;

    private Terminal $terminal;

    /** @var Collection<int, Agent> */
    private Collection $selectedTargetAgents;

    /** @var Collection<int, McpClient> */
    private Collection $selectedTargetMcpClient;

    private string $projectName;

    /** @var array<non-empty-string> */
    private array $systemInstalledCodeEnvironments = [];

    private array $projectInstalledCodeEnvironments = [];

    private string $greenTick;

    private string $redCross;

    protected function configure()
    {
        $this->setHelp('This command will combine your .ai/guidelines/ directory to the appropriate AI agent files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->bootstrap(new CodeEnvironmentsDetector, new Terminal);

        $this->displayBootsHeader();
        $this->discoverEnvironment();
        $this->collectInstallationPreferences();
        $this->installGuidelines();
        $this->outro();

        return Command::SUCCESS;
    }

    private function bootstrap(CodeEnvironmentsDetector $codeEnvironmentsDetector, Terminal $terminal): void
    {
        $this->codeEnvironmentsDetector = $codeEnvironmentsDetector;
        $this->terminal = $terminal;

        $this->terminal->initDimensions();
        $this->greenTick = $this->green('✓');
        $this->redCross = $this->red('✗');

        $this->selectedTargetAgents = collect();
        $this->selectedTargetMcpClient = collect();

        $this->projectName = $this->getProjectName();
    }

    private function getProjectName(): string
    {
        if (file_exists(File::projectBasePath('composer.json'))) {
            $composer = json_decode(file_get_contents(File::projectBasePath('composer.json')), true);
            if (isset($composer['name']) && is_string($composer['name'])) {
                return $composer['name'];
            }
        }

        if (file_exists(File::projectBasePath('package.json'))) {
            $package = json_decode(file_get_contents(File::projectBasePath('package.json')), true);
            if (isset($package['name']) && is_string($package['name'])) {
                return $package['name'];
            }
        }

        return basename(File::projectBasePath());
    }

    private function displayBootsHeader(): void
    {
        note($this->bootsLogo());
        note("Let's give {$this->bgYellow($this->black($this->bold($this->projectName)))} a Boot or two!");
    }

    private function bootsLogo(): string
    {
        return
         <<<'HEADER'
        ██████╗   ██████╗   ██████╗ ████████╗ ███████╗
        ██╔══██╗ ██╔═══██╗ ██╔═══██╗╚══██╔══╝ ██╔════╝
        ██████╔╝ ██║   ██║ ██║   ██║   ██║    ███████╗
        ██╔══██╗ ██║   ██║ ██║   ██║   ██║    ╚════██║
        ██████╔╝ ╚██████╔╝ ╚██████╔╝   ██║    ███████║
        ╚═════╝   ╚═════╝   ╚═════╝    ╚═╝    ╚══════╝
        HEADER;
    }

    private function discoverEnvironment(): void
    {
        $this->systemInstalledCodeEnvironments = $this->codeEnvironmentsDetector->discoverSystemInstalledCodeEnvironments();
        $this->projectInstalledCodeEnvironments = $this->codeEnvironmentsDetector->discoverProjectInstalledCodeEnvironments(File::projectBasePath());
    }

    private function collectInstallationPreferences(): void
    {
        $this->selectedTargetAgents = $this->selectTargetAgents();
    }

    private function outro(): void
    {
        $text = 'Enjoy the boots! 🥾 Now take your next steps with AI!';
        $paddingLength = (int) (floor(($this->terminal->cols() - mb_strlen($text)) / 2)) - 2;

        echo "\033[42m\033[2K".str_repeat(' ', max(0, $paddingLength)); // Make the entire line have a green background
        echo $this->black($this->bold($text)).$this->reset(PHP_EOL).$this->reset(PHP_EOL);
    }

    private function hyperlink(string $label, string $url): string
    {
        return "\033]8;;{$url}\007{$label}\033]8;;\033\\";
    }

    /**
     * @return Collection<int, CodeEnvironment>
     */
    private function selectTargetAgents(): Collection
    {
        return $this->selectCodeEnvironments(
            Agent::class,
            sprintf('Which agents need AI guidelines for %s?', $this->projectName)
        );
    }

    /**
     * Get configuration settings for contract-specific selection behavior.
     *
     * @return array{scroll: int, required: bool, displayMethod: string}
     */
    private function getSelectionConfig(string $contractClass): array
    {
        return match ($contractClass) {
            Agent::class => ['scroll' => 5, 'required' => false, 'displayMethod' => 'agentName'],
            McpClient::class => ['scroll' => 5, 'required' => true, 'displayMethod' => 'displayName'],
            default => throw new InvalidArgumentException("Unsupported contract class: {$contractClass}"),
        };
    }

    /**
     * @return Collection<int, CodeEnvironment>
     */
    private function selectCodeEnvironments(string $contractClass, string $label): Collection
    {
        $allEnvironments = $this->codeEnvironmentsDetector->getCodeEnvironments();
        $config = $this->getSelectionConfig($contractClass);

        $availableEnvironments = $allEnvironments->filter(function (CodeEnvironment $environment) use ($contractClass) {
            return $environment instanceof $contractClass;
        });

        if ($availableEnvironments->isEmpty()) {
            return collect();
        }

        $options = $availableEnvironments->mapWithKeys(function (CodeEnvironment $environment) use ($config) {
            $displayMethod = $config['displayMethod'];
            $displayText = $environment->{$displayMethod}();

            return [get_class($environment) => $displayText];
        })->sort();

        $detectedClasses = [];
        $installedEnvNames = array_unique(array_merge(
            $this->projectInstalledCodeEnvironments,
            $this->systemInstalledCodeEnvironments
        ));

        foreach ($installedEnvNames as $envKey) {
            $matchingEnv = $availableEnvironments->first(fn (CodeEnvironment $env) => strtolower($envKey) === strtolower($env->name()));
            if ($matchingEnv) {
                $detectedClasses[] = get_class($matchingEnv);
            }
        }

        foreach ($availableEnvironments as $environment) {
            $guidelinesFile = File::projectBasePath(".ai/guidelines/{$environment->name()}.md");
            if (file_exists($guidelinesFile) && ! in_array(get_class($environment), $detectedClasses)) {
                $detectedClasses[] = get_class($environment);
            }
        }

        if (! $this->input->isInteractive() && $options->isEmpty()) {
            note(' No compatible code environments detected for guideline installation.');

            return collect();
        }

        if ($this->input->isInteractive()) {
            $selectedClasses = collect(multiselect(
                label: $label,
                options: $options->toArray(),
                default: array_unique($detectedClasses),
                scroll: $config['scroll'],
                required: $config['required'],
                hint: empty($detectedClasses) ? '' : sprintf('Auto-detected %s for you',
                    Arr::join(array_map(function ($className) use ($availableEnvironments, $config) {
                        $env = $availableEnvironments->first(fn ($env) => get_class($env) === $className);
                        $displayMethod = $config['displayMethod'];

                        return $env->{$displayMethod}();
                    }, $detectedClasses), ', ', ' & ')
                )
            ))->sort();
        } else {
            $selectedClasses = collect($detectedClasses);
        }

        return $selectedClasses->map(fn ($className) => $availableEnvironments->first(fn ($env) => get_class($env) === $className));
    }

    private function installGuidelines(): void
    {
        if ($this->selectedTargetAgents->isEmpty()) {
            error(' No agents selected for guideline installation.');

            return;
        }

        $composer = new GuidelineComposer;
        $guidelines = $composer->guidelines();

        $guidelinesText = $guidelines->count() === 1
            ? 'guideline'
            : 'guidelines';

        info(sprintf(' Adding %d %s to your selected agents', $guidelines->count(), $guidelinesText));
        DisplayHelper::grid(
            $guidelines
                ->map(fn ($guideline, string $key) => $key.($guideline['custom'] ? '*' : ''))
                ->values()
                ->sort()
                ->toArray(),
            $this->terminal->cols()
        );

        $failed = [];
        $composedAiGuidelines = $composer->compose();

        $longestAgentName = max(1, ...$this->selectedTargetAgents->map(fn ($agent) => strlen($agent->agentName()))->toArray());
        /** @var CodeEnvironment $agent */
        foreach ($this->selectedTargetAgents as $agent) {
            $agentName = $agent->agentName();
            $displayAgentName = str_pad($agentName, $longestAgentName);
            $this->output->write("  {$displayAgentName} ... ");
            /** @var Agent $agent */
            try {
                (new GuidelineWriter($agent))
                    ->write($composedAiGuidelines);

                note($this->greenTick);
            } catch (Exception $e) {
                $failed[$agentName] = $e->getMessage();
                note($this->redCross);
            }
        }

        if (count($failed) > 0) {
            error(sprintf('✗ Failed to install guidelines to %d agent%s:',
                count($failed),
                count($failed) === 1 ? '' : 's'
            ));
            foreach ($failed as $agentName => $error) {
                note("  - {$agentName}: {$error}");
            }
        }
    }
}
