<?php

use Boots\File;

test('basePath returns current working directory when called', function () {
    $basePath = File::basePath();
    $currentWorkingDirectory = getcwd();

    // Should return the current working directory
    expect($basePath)->toBe($currentWorkingDirectory);
    expect($basePath)->toBeString();
});

test('basePath can append path segments', function () {
    $basePath = File::basePath('src/Commands');
    $expectedPath = getcwd().'/src/Commands';

    expect($basePath)->toBe($expectedPath);
});

test('basePath handles leading and trailing slashes', function () {
    $path1 = File::basePath('/src/Commands/');
    $path2 = File::basePath('src/Commands');

    expect($path1)->toBe($path2);
});
