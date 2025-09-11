<?php

use Boots\Detection\CommandDetectionStrategy;

test('CommandDetectionStrategy returns false when no command provided', function () {
    $strategy = new CommandDetectionStrategy;
    $config = [];

    expect($strategy->detect($config))->toBeFalse();
});

test('CommandDetectionStrategy returns true for successful command', function () {
    $strategy = new CommandDetectionStrategy;
    $config = [
        'command' => 'true', // Unix command that always succeeds
    ];

    expect($strategy->detect($config))->toBeTrue();
});

test('CommandDetectionStrategy returns false for failed command', function () {
    $strategy = new CommandDetectionStrategy;
    $config = [
        'command' => 'exit 1',
    ];

    expect($strategy->detect($config))->toBeFalse();
});

test('CommandDetectionStrategy returns false for non-existent command', function () {
    $strategy = new CommandDetectionStrategy;
    $config = [
        'command' => 'nonexistentcommandthatdoesntexist123456',
    ];

    expect($strategy->detect($config))->toBeFalse();
});
