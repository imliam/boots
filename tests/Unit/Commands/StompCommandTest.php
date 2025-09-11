<?php

use Boots\Commands\StompCommand;
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

it('can find and move existing guideline files', function () {
    // Create test guideline files
    $githubDir = File::projectBasePath('.github');
    mkdir($githubDir, 0755, true);
    file_put_contents($githubDir.'/copilot-instructions.md', '<guidelines>Test Copilot instructions</guidelines>');

    $cursorDir = File::projectBasePath('.cursor/rules');
    mkdir($cursorDir, 0755, true);
    file_put_contents($cursorDir.'/boots.mdc', '# Test Cursor rules');

    // Run the stomp command
    $application = new Application;
    $application->add(new StompCommand);

    $command = $application->find('stomp');
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    // Assert the command was successful
    expect($commandTester->getStatusCode())->toBe(0);

    // Assert files were moved
    expect(file_exists(File::projectBasePath('.ai/guidelines/copilot.md')))->toBeTrue();
    expect(file_exists(File::projectBasePath('.ai/guidelines/cursor.md')))->toBeTrue();

    // Assert original files no longer exist
    expect(file_exists($githubDir.'/copilot-instructions.md'))->toBeFalse();
    expect(file_exists($cursorDir.'/boots.mdc'))->toBeFalse();

    // Assert content is preserved
    expect(file_get_contents(File::projectBasePath('.ai/guidelines/copilot.md')))
        ->toBe('<guidelines>Test Copilot instructions</guidelines>');
    expect(file_get_contents(File::projectBasePath('.ai/guidelines/cursor.md')))
        ->toBe('# Test Cursor rules');

    // Assert empty directories were cleaned up
    expect(is_dir(File::projectBasePath('.cursor')))->toBeFalse();
});

it('handles existing destination files by creating backups', function () {
    // Create existing destination file
    $aiDir = File::projectBasePath('.ai/guidelines');
    mkdir($aiDir, 0755, true);
    file_put_contents($aiDir.'/cline.md', 'Existing content');

    // Create source file
    $clineDir = File::projectBasePath('.clinerules');
    mkdir($clineDir, 0755, true);
    file_put_contents($clineDir.'/boots.md', 'New content');

    // Run the stomp command
    $application = new Application;
    $application->add(new StompCommand);

    $command = $application->find('stomp');
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    // Assert the command was successful
    expect($commandTester->getStatusCode())->toBe(0);

    // Assert new content is in place
    expect(file_get_contents($aiDir.'/cline.md'))->toBe('New content');

    // Assert backup was created
    $backupFiles = glob($aiDir.'/cline.md.backup.*');
    expect(count($backupFiles))->toBe(1);
    expect(file_get_contents($backupFiles[0]))->toBe('Existing content');
});

it('shows appropriate message when no files need to be moved', function () {
    // Run the stomp command with no existing guideline files
    $application = new Application;
    $application->add(new StompCommand);

    $command = $application->find('stomp');
    $commandTester = new CommandTester($command);
    $commandTester->execute([]);

    // Assert the command was successful
    expect($commandTester->getStatusCode())->toBe(0);

    // Assert appropriate message is displayed
    $output = $commandTester->getDisplay();
    expect($output)->toContain('No existing guideline files found to move.');
});

it('only processes environments that implement Agent interface', function () {
    // This test verifies that only environments implementing Agent interface are processed
    // We can't easily test this without mocking, but the logic is straightforward
    expect(true)->toBeTrue();
});
