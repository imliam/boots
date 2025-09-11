<?php

namespace Boots\Commands;

use Boots\CodeEnvironmentsDetector;
use Boots\Contracts\Agent;
use Boots\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;

#[AsCommand(
    name: 'stomp',
    description: 'Find existing guideline files and move them to .ai/guidelines/{agent}.md',
)]
class StompCommand extends Command
{
    private InputInterface $input;

    private OutputInterface $output;

    private CodeEnvironmentsDetector $codeEnvironmentsDetector;

    protected function configure()
    {
        $this->setHelp('This command will find any existing AI guideline files in the project and move them to the standardized .ai/guidelines/ directory structure.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        $this->codeEnvironmentsDetector = new CodeEnvironmentsDetector;

        $this->displayStompHeader();
        $this->findAndMoveGuidelineFiles();

        return Command::SUCCESS;
    }

    private function displayStompHeader(): void
    {
        note('🥾 Finding and stomping existing guideline files into .ai/guidelines/');
    }

    private function findAndMoveGuidelineFiles(): void
    {
        $allEnvironments = $this->codeEnvironmentsDetector->getCodeEnvironments();
        $movedFiles = 0;

        foreach ($allEnvironments as $environment) {
            // Only process environments that implement Agent interface
            if (! $environment instanceof Agent) {
                continue;
            }

            $currentPath = File::basePath($environment->guidelinesPath());

            // Skip if the file doesn't exist
            if (! file_exists($currentPath)) {
                continue;
            }

            $agentName = $environment->name();
            $newPath = File::basePath(".ai/guidelines/{$agentName}.md");

            // Create the target directory if it doesn't exist
            $targetDir = dirname($newPath);
            if (! is_dir($targetDir)) {
                if (! mkdir($targetDir, 0755, true)) {
                    error("Failed to create directory: {$targetDir}");

                    continue;
                }
            }

            // Move the file
            if ($this->moveFile($currentPath, $newPath)) {
                info("✓ Moved {$environment->guidelinesPath()} → .ai/guidelines/{$agentName}.md");
                $movedFiles++;
            } else {
                error("✗ Failed to move {$environment->guidelinesPath()}");
            }
        }

        if ($movedFiles === 0) {
            note('No existing guideline files found to move.');
        } else {
            info("Successfully moved {$movedFiles} guideline file(s) to .ai/guidelines/");
        }
    }

    private function moveFile(string $source, string $destination): bool
    {
        // If destination already exists, we need to handle it
        if (file_exists($destination)) {
            $backupPath = $destination.'.backup.'.time();
            if (! rename($destination, $backupPath)) {
                error("Failed to backup existing file at {$destination}");

                return false;
            }
            info("Backed up existing file to {$backupPath}");
        }

        // Move the file
        if (! rename($source, $destination)) {
            return false;
        }

        // Clean up empty directories in the source path
        $this->cleanupEmptyDirectories(dirname($source));

        return true;
    }

    private function cleanupEmptyDirectories(string $dir): void
    {
        $basePath = File::basePath();

        // Don't try to remove the project base directory or above
        if ($dir === $basePath || strpos($dir, $basePath) !== 0) {
            return;
        }

        // Check if directory is empty
        if (is_dir($dir) && count(scandir($dir)) === 2) { // Only . and .. entries
            if (rmdir($dir)) {
                // Recursively try to clean up parent directories
                $this->cleanupEmptyDirectories(dirname($dir));
            }
        }
    }
}
