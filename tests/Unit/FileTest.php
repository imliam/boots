<?php

use Boots\File;

test('projectBasePath returns correct base path when vendor directory exists', function () {
    $projectBasePath = File::projectBasePath();

    // Should return the project root directory
    expect($projectBasePath)->toBeString();
    expect(basename($projectBasePath))->toBe('boots');
});

test('projectBasePath can append path segments', function () {
    $projectBasePath = File::projectBasePath('src/Commands');

    expect($projectBasePath)->toEndWith('boots/src/Commands');
});

test('projectBasePath handles leading and trailing slashes', function () {
    $path1 = File::projectBasePath('/src/Commands/');
    $path2 = File::projectBasePath('src/Commands');

    expect($path1)->toBe($path2);
});

test('packageBasePath returns the package root directory', function () {
    $packageBasePath = File::packageBasePath();

    expect($packageBasePath)->toBeString();
    expect(basename($packageBasePath))->toBe('boots');
    expect(file_exists($packageBasePath.'/composer.json'))->toBeTrue();
});
