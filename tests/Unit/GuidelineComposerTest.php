<?php

use Boots\GuidelineComposer;

test('composeGuidelines formats guidelines correctly', function () {
    $guidelines = collect([
        'General' => [
            'content' => 'These are general guidelines',
            'name' => 'general',
            'path' => '/path/to/general.md',
            'custom' => false,
        ],
        'PHP' => [
            'content' => 'These are PHP guidelines',
            'name' => 'php',
            'path' => '/path/to/php.md',
            'custom' => false,
        ],
    ]);

    $result = GuidelineComposer::composeGuidelines($guidelines);

    expect($result)->toContain('=== General rules ===');
    expect($result)->toContain('These are general guidelines');
    expect($result)->toContain('=== PHP rules ===');
    expect($result)->toContain('These are PHP guidelines');
});

test('composeGuidelines filters out empty guidelines', function () {
    $guidelines = collect([
        'Valid' => [
            'content' => 'Valid content',
            'name' => 'valid',
            'path' => '/path/to/valid.md',
            'custom' => false,
        ],
        'Empty' => [
            'content' => '',
            'name' => 'empty',
            'path' => '/path/to/empty.md',
            'custom' => false,
        ],
        'Whitespace' => [
            'content' => "   \n   \t   ",
            'name' => 'whitespace',
            'path' => '/path/to/whitespace.md',
            'custom' => false,
        ],
    ]);

    $result = GuidelineComposer::composeGuidelines($guidelines);

    expect($result)->toContain('=== Valid rules ===');
    expect($result)->toContain('Valid content');
    expect($result)->not->toContain('=== Empty rules ===');
    expect($result)->not->toContain('=== Whitespace rules ===');
});

test('composeGuidelines handles empty collection', function () {
    $guidelines = collect([]);

    $result = GuidelineComposer::composeGuidelines($guidelines);

    expect($result)->toBe('');
});

test('composeGuidelines removes excessive newlines', function () {
    $guidelines = collect([
        'Test' => [
            'content' => 'Content with extra newlines',
            'name' => 'test',
            'path' => '/path/to/test.md',
            'custom' => false,
        ],
    ]);

    $result = GuidelineComposer::composeGuidelines($guidelines);

    expect($result)->not->toContain("\n\n\n\n");
});
