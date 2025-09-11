<?php

use Boots\Detection\FileDetectionStrategy;
use Boots\File;

test('FileDetectionStrategy detects existing files', function () {
    $tempDir = temporaryDirectory();
    $testFile = $tempDir.'/test.txt';
    file_put_contents($testFile, 'test content');

    $strategy = new FileDetectionStrategy;
    $config = [
        'basePath' => $tempDir,
        'files' => ['test.txt'],
    ];

    expect($strategy->detect($config))->toBeTrue();

    // Cleanup
    unlink($testFile);
    rmdir($tempDir);
});

test('FileDetectionStrategy returns false for non-existent files', function () {
    $tempDir = temporaryDirectory();

    $strategy = new FileDetectionStrategy;
    $config = [
        'basePath' => $tempDir,
        'files' => ['nonexistent.txt'],
    ];

    expect($strategy->detect($config))->toBeFalse();

    // Cleanup
    rmdir($tempDir);
});

test('FileDetectionStrategy returns false when no files config provided', function () {
    $strategy = new FileDetectionStrategy;
    $config = [];

    expect($strategy->detect($config))->toBeFalse();
});

test('FileDetectionStrategy uses current working directory as default base path', function () {
    $strategy = new FileDetectionStrategy;
    $config = [
        'files' => ['composer.json'], // Should exist in the project root
    ];

    // Change working directory to project root
    $originalCwd = getcwd();
    chdir(File::projectBasePath());

    $result = $strategy->detect($config);

    // Restore original working directory
    chdir($originalCwd);

    expect($result)->toBeTrue();
});

test('FileDetectionStrategy handles multiple files', function () {
    $tempDir = temporaryDirectory();
    $testFile = $tempDir.'/exists.txt';
    file_put_contents($testFile, 'test content');

    $strategy = new FileDetectionStrategy;
    $config = [
        'basePath' => $tempDir,
        'files' => ['nonexistent1.txt', 'exists.txt', 'nonexistent2.txt'],
    ];

    expect($strategy->detect($config))->toBeTrue();

    // Cleanup
    unlink($testFile);
    rmdir($tempDir);
});
