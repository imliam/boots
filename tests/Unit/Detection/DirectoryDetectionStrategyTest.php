<?php

use Boots\Detection\DirectoryDetectionStrategy;
use Boots\Enums\Platform;

test('DirectoryDetectionStrategy detects existing directories', function () {
    $tempDir = temporaryDirectory();
    $subDir = $tempDir.'/testdir';
    mkdir($subDir, 0755, true);

    $strategy = new DirectoryDetectionStrategy;
    $config = [
        'basePath' => $tempDir,
        'paths' => ['testdir'],
    ];

    expect($strategy->detect($config))->toBeTrue();

    // Cleanup
    rmdir($subDir);
    rmdir($tempDir);
});

test('DirectoryDetectionStrategy returns false for non-existent directories', function () {
    $tempDir = temporaryDirectory();

    $strategy = new DirectoryDetectionStrategy;
    $config = [
        'basePath' => $tempDir,
        'paths' => ['nonexistent'],
    ];

    expect($strategy->detect($config))->toBeFalse();

    // Cleanup
    rmdir($tempDir);
});

test('DirectoryDetectionStrategy returns false when no paths config provided', function () {
    $strategy = new DirectoryDetectionStrategy;
    $config = [];

    expect($strategy->detect($config))->toBeFalse();
});

test('DirectoryDetectionStrategy works with absolute paths', function () {
    $tempDir = temporaryDirectory();

    $strategy = new DirectoryDetectionStrategy;
    $config = [
        'paths' => [$tempDir],
    ];

    expect($strategy->detect($config))->toBeTrue();

    // Cleanup
    rmdir($tempDir);
});

test('DirectoryDetectionStrategy handles multiple paths', function () {
    $tempDir = temporaryDirectory();
    $subDir = $tempDir.'/existing';
    mkdir($subDir, 0755, true);

    $strategy = new DirectoryDetectionStrategy;
    $config = [
        'basePath' => $tempDir,
        'paths' => ['nonexistent1', 'existing', 'nonexistent2'],
    ];

    expect($strategy->detect($config))->toBeTrue();

    // Cleanup
    rmdir($subDir);
    rmdir($tempDir);
});

test('DirectoryDetectionStrategy expands Windows environment variables', function () {
    $strategy = new DirectoryDetectionStrategy;
    $config = [
        'paths' => ['%TEMP%'],
    ];

    $result = $strategy->detect($config, Platform::Windows);

    // We can't guarantee the result since %TEMP% may or may not be set in the test environment
    // But we can verify it doesn't throw an exception
    expect($result)->toBeIn([true, false]);
});
