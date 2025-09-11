<?php

use Boots\Detection\CommandDetectionStrategy;
use Boots\Detection\CompositeDetectionStrategy;
use Boots\Detection\DetectionStrategyFactory;
use Boots\Detection\DirectoryDetectionStrategy;
use Boots\Detection\FileDetectionStrategy;

test('DetectionStrategyFactory creates directory detection strategy', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->make('directory');

    expect($strategy)->toBeInstanceOf(DirectoryDetectionStrategy::class);
});

test('DetectionStrategyFactory creates command detection strategy', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->make('command');

    expect($strategy)->toBeInstanceOf(CommandDetectionStrategy::class);
});

test('DetectionStrategyFactory creates file detection strategy', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->make('file');

    expect($strategy)->toBeInstanceOf(FileDetectionStrategy::class);
});

test('DetectionStrategyFactory creates composite strategy for array input', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->make(['directory', 'file']);

    expect($strategy)->toBeInstanceOf(CompositeDetectionStrategy::class);
});

test('DetectionStrategyFactory throws exception for unknown type', function () {
    $factory = new DetectionStrategyFactory;

    expect(fn () => $factory->make('unknown'))->toThrow(InvalidArgumentException::class);
});

test('DetectionStrategyFactory infers type from config with files', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->makeFromConfig(['files' => ['test.txt']]);

    expect($strategy)->toBeInstanceOf(FileDetectionStrategy::class);
});

test('DetectionStrategyFactory infers type from config with paths', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->makeFromConfig(['paths' => ['/some/path']]);

    expect($strategy)->toBeInstanceOf(DirectoryDetectionStrategy::class);
});

test('DetectionStrategyFactory infers type from config with command', function () {
    $factory = new DetectionStrategyFactory;
    $strategy = $factory->makeFromConfig(['command' => 'some command']);

    expect($strategy)->toBeInstanceOf(CommandDetectionStrategy::class);
});
