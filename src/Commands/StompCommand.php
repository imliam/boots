<?php

namespace Boots\Commands;

use Boots\CodeEnvironmentsDetector;
use Boots\File;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $this->codeEnvironmentsDetector = new CodeEnvironmentsDetector();

        $this->displayStompHeader();
        $this->findAndMoveGuidelineFiles();

        return Command::SUCCESS;
    }

    private function displayStompHeader(): void
    {
        $this->output->writeln('<info>🥾 Boots Stomp</info>');
        $this->output->writeln('<comment>Finding and moving existing guideline files to .ai/guidelines/</comment>');
    }

    private function findAndMoveGuidelineFiles(): void
    {
        $allEnvironments = $this->codeEnvironmentsDetector->getCodeEnvironments();
        $movedFiles = 0;

        foreach ($allEnvironments as $environment) {
            // Only process environments that implement Agent interface
            if (! $environment instanceof \Boots\Contracts\Agent) {
                continue;
            }

            $currentPath = File::projectBasePath($environment->guidelinesPath());

            // Skip if the file doesn't exist
            if (! file_exists($currentPath)) {
                continue;
            }

            $agentName = $environment->name();
            $newPath = File::projectBasePath(".ai/guidelines/{$agentName}.md");

            // Create the target directory if it doesn't exist
            $targetDir = dirname($newPath);
            if (! is_dir($targetDir)) {
                if (! mkdir($targetDir, 0755, true)) {
                    $this->output->writeln("<error>Failed to create directory: {$targetDir}</error>");

                    continue;
                }
            }

            // Move the file
            if ($this->moveFile($currentPath, $newPath)) {
                $this->output->writeln("<info>✓ Moved {$environment->guidelinesPath()} → .ai/guidelines/{$agentName}.md</info>");
                $movedFiles++;
            } else {
                $this->output->writeln("<error>✗ Failed to move {$environment->guidelinesPath()}</error>");
            }
        }

        if ($movedFiles === 0) {
            $this->output->writeln('<comment>No existing guideline files found to move.</comment>');
        } else {
            $this->output->writeln("<info>Successfully moved {$movedFiles} guideline file(s) to .ai/guidelines/</info>");
        }
    }

    private function moveFile(string $source, string $destination): bool
    {
        // If destination already exists, we need to handle it
        if (file_exists($destination)) {
            $backupPath = $destination.'.backup.'.time();
            if (! rename($destination, $backupPath)) {
                $this->output->writeln("<error>Failed to backup existing file at {$destination}</error>");

                return false;
            }
            $this->output->writeln("<info>Backed up existing file to {$backupPath}</info>");
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
        $projectBasePath = File::projectBasePath();

        // Don't try to remove the project base directory or above
        if ($dir === $projectBasePath || strpos($dir, $projectBasePath) !== 0) {
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
