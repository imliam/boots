<?php

use Boots\Commands\BootsCommand;
use Boots\File;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

beforeEach(function () {
    // Clean up any test files
    $testDirs = ['.ai', '.github', '.cursor', '.clinerules'];
    foreach ($testDirs as $dir) {
        $path = File::projectBasePath($dir);
        if (is_dir($path)) {
            exec("rm -rf {$path}");
        }
    }
});

afterEach(function () {
    // Clean up any test files
    $testDirs = ['.ai', '.github', '.cursor', '.clinerules'];
    foreach ($testDirs as $dir) {
        $path = File::projectBasePath($dir);
        if (is_dir($path)) {
            exec("rm -rf {$path}");
        }
    }
});

it('auto-detects agents based on existing .ai/guidelines files', function () {
    // Create some guideline files in .ai/guidelines/
    $aiDir = File::projectBasePath('.ai/guidelines');
    mkdir($aiDir, 0755, true);
    file_put_contents($aiDir . '/copilot.md', '# Copilot Guidelines');
    file_put_contents($aiDir . '/cursor.md', '# Cursor Guidelines');
    file_put_contents($aiDir . '/cline.md', '# Cline Guidelines');

    // Create a main guidelines file that will be combined
    file_put_contents($aiDir . '/main.md', '# Main Guidelines\nThese are the main guidelines.');

    // Run the boots command in non-interactive mode
    $application = new Application();
    $application->add(new BootsCommand());

    $command = $application->find('boots');
    $commandTester = new CommandTester($command);
    $commandTester->setInputs([]); // Provide empty input to avoid hanging
    $commandTester->execute([], ['interactive' => false]);

    // Assert the command was successful
    expect($commandTester->getStatusCode())->toBe(0);

    // Verify that the agents were auto-detected by checking if their guideline files were updated
    // The command should have combined the main.md content into the agent-specific files
    expect(file_exists(File::projectBasePath('.github/copilot-instructions.md')))->toBeTrue();
    expect(file_exists(File::projectBasePath('.cursor/rules/boots.mdc')))->toBeTrue();
    expect(file_exists(File::projectBasePath('.clinerules/boots.md')))->toBeTrue();
});

it('combines traditional detection with .ai/guidelines detection', function () {
    // Create a traditional detection scenario (project-based detection)
    $cursorDir = File::projectBasePath('.cursor');
    mkdir($cursorDir, 0755, true);

    // Create a guideline file for a different agent
    $aiDir = File::projectBasePath('.ai/guidelines');
    mkdir($aiDir, 0755, true);
    file_put_contents($aiDir . '/copilot.md', '# Copilot Guidelines');
    file_put_contents($aiDir . '/main.md', '# Main Guidelines');

    // Run the boots command in non-interactive mode
    $application = new Application();
    $application->add(new BootsCommand());

    $command = $application->find('boots');
    $commandTester = new CommandTester($command);
    $commandTester->setInputs([]);
    $commandTester->execute([], ['interactive' => false]);

    // Assert the command was successful
    expect($commandTester->getStatusCode())->toBe(0);

    // Verify both agents were detected - cursor from project detection, copilot from .ai/guidelines
    expect(file_exists(File::projectBasePath('.github/copilot-instructions.md')))->toBeTrue();
    expect(file_exists(File::projectBasePath('.cursor/rules/boots.mdc')))->toBeTrue();
});

it('does not duplicate agents detected by multiple methods', function () {
    // Create both traditional detection and .ai/guidelines file for the same agent
    $cursorDir = File::projectBasePath('.cursor');
    mkdir($cursorDir, 0755, true);

    $aiDir = File::projectBasePath('.ai/guidelines');
    mkdir($aiDir, 0755, true);
    file_put_contents($aiDir . '/cursor.md', '# Cursor Guidelines');
    file_put_contents($aiDir . '/main.md', '# Main Guidelines');

    // Run the boots command in non-interactive mode
    $application = new Application();
    $application->add(new BootsCommand());

    $command = $application->find('boots');
    $commandTester = new CommandTester($command);
    $commandTester->setInputs([]);
    $commandTester->execute([], ['interactive' => false]);

    // Assert the command was successful
    expect($commandTester->getStatusCode())->toBe(0);

    // Verify cursor was only processed once by checking it created the guideline file
    expect(file_exists(File::projectBasePath('.cursor/rules/boots.mdc')))->toBeTrue();
});
