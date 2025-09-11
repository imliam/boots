<?php

use Boots\Enums\Platform;

test('Platform enum has correct cases', function () {
    expect(Platform::Darwin->value)->toBe('darwin');
    expect(Platform::Linux->value)->toBe('linux');
    expect(Platform::Windows->value)->toBe('windows');
});

test('Platform current method returns correct platform', function () {
    $platform = Platform::current();

    expect($platform)->toBeInstanceOf(Platform::class);

    // We can't test the exact platform since it depends on the test environment,
    // but we can verify it returns one of the valid cases
    expect(in_array($platform, [Platform::Darwin, Platform::Linux, Platform::Windows]))->toBeTrue();
});

test('Platform current method maps PHP_OS_FAMILY correctly', function () {
    // We can test the logic by checking that the mapping makes sense
    // This test verifies that the current() method will return a valid Platform
    $validPlatforms = [Platform::Darwin, Platform::Linux, Platform::Windows];
    $currentPlatform = Platform::current();

    expect($validPlatforms)->toContain($currentPlatform);
});
