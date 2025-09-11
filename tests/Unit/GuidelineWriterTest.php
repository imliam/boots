<?php

use Boots\Contracts\Agent;
use Boots\GuidelineWriter;

test('GuidelineWriter has correct constants', function () {
    expect(GuidelineWriter::NEW)->toBe(0);
    expect(GuidelineWriter::REPLACED)->toBe(1);
    expect(GuidelineWriter::FAILED)->toBe(2);
    expect(GuidelineWriter::NOOP)->toBe(3);
});

test('GuidelineWriter returns NOOP for empty guidelines', function () {
    $agent = new class implements Agent
    {
        public function agentName(): ?string
        {
            return 'Test Agent';
        }

        public function guidelinesPath(): string
        {
            return '/tmp/test.md';
        }

        public function frontmatter(): bool
        {
            return false;
        }
    };

    $writer = new GuidelineWriter($agent);
    $result = $writer->write('');

    expect($result)->toBe(GuidelineWriter::NOOP);
});

test('GuidelineWriter creates new file with guidelines', function () {
    $tempFile = createTempFile();

    $agent = new class($tempFile) implements Agent
    {
        public function __construct(private string $filePath) {}

        public function agentName(): ?string
        {
            return 'Test Agent';
        }

        public function guidelinesPath(): string
        {
            return $this->filePath;
        }

        public function frontmatter(): bool
        {
            return false;
        }
    };

    $writer = new GuidelineWriter($agent);
    $result = $writer->write('Test guidelines content');

    expect($result)->toBe(GuidelineWriter::NEW);

    $content = file_get_contents($tempFile);
    expect($content)->toContain('<guidelines>');
    expect($content)->toContain('Test guidelines content');
    expect($content)->toContain('</guidelines>');

    // Cleanup
    unlink($tempFile);
});

test('GuidelineWriter replaces existing guidelines', function () {
    $tempFile = createTempFile('<guidelines>Old content</guidelines>');

    $agent = new class($tempFile) implements Agent
    {
        public function __construct(private string $filePath) {}

        public function agentName(): ?string
        {
            return 'Test Agent';
        }

        public function guidelinesPath(): string
        {
            return $this->filePath;
        }

        public function frontmatter(): bool
        {
            return false;
        }
    };

    $writer = new GuidelineWriter($agent);
    $result = $writer->write('New guidelines content');

    expect($result)->toBe(GuidelineWriter::REPLACED);

    $content = file_get_contents($tempFile);
    expect($content)->toContain('<guidelines>');
    expect($content)->toContain('New guidelines content');
    expect($content)->toContain('</guidelines>');
    expect($content)->not->toContain('Old content');

    // Cleanup
    unlink($tempFile);
});

test('GuidelineWriter adds frontmatter when agent requires it', function () {
    $tempFile = createTempFile();

    $agent = new class($tempFile) implements Agent
    {
        public function __construct(private string $filePath) {}

        public function agentName(): ?string
        {
            return 'Test Agent';
        }

        public function guidelinesPath(): string
        {
            return $this->filePath;
        }

        public function frontmatter(): bool
        {
            return true;
        }
    };

    $writer = new GuidelineWriter($agent);
    $result = $writer->write('Test guidelines');

    expect($result)->toBe(GuidelineWriter::NEW);

    $content = file_get_contents($tempFile);
    expect($content)->toStartWith("---\nalwaysApply: true\n---\n");

    // Cleanup
    unlink($tempFile);
});

test('GuidelineWriter preserves existing content when adding guidelines', function () {
    $tempFile = createTempFile('Existing content in file');

    $agent = new class($tempFile) implements Agent
    {
        public function __construct(private string $filePath) {}

        public function agentName(): ?string
        {
            return 'Test Agent';
        }

        public function guidelinesPath(): string
        {
            return $this->filePath;
        }

        public function frontmatter(): bool
        {
            return false;
        }
    };

    $writer = new GuidelineWriter($agent);
    $result = $writer->write('New guidelines');

    expect($result)->toBe(GuidelineWriter::NEW);

    $content = file_get_contents($tempFile);
    expect($content)->toContain('Existing content in file');
    expect($content)->toContain('===');
    expect($content)->toContain('<guidelines>');
    expect($content)->toContain('New guidelines');
    expect($content)->toContain('</guidelines>');

    // Cleanup
    unlink($tempFile);
});

test('GuidelineWriter creates directory if it does not exist', function () {
    $tempDir = temporaryDirectory();
    $filePath = $tempDir.'/subdir/test.md';

    $agent = new class($filePath) implements Agent
    {
        public function __construct(private string $filePath) {}

        public function agentName(): ?string
        {
            return 'Test Agent';
        }

        public function guidelinesPath(): string
        {
            return $this->filePath;
        }

        public function frontmatter(): bool
        {
            return false;
        }
    };

    $writer = new GuidelineWriter($agent);
    $result = $writer->write('Test content');

    expect($result)->toBe(GuidelineWriter::NEW);
    expect(file_exists($filePath))->toBeTrue();
    expect(file_get_contents($filePath))->toContain('Test content');

    // Cleanup
    exec('rm -rf '.escapeshellarg($tempDir));
});
