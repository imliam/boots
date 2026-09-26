<?php

use Boots\CodeEnvironment\Cursor;
use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\Platform;

test('Cursor has correct name and display name', function () {
    $cursor = new Cursor;

    expect($cursor->name())->toBe('cursor');
    expect($cursor->displayName())->toBe('Cursor');
});

test('Cursor implements both Agent and McpClient interfaces', function () {
    $cursor = new Cursor;

    expect($cursor)->toBeInstanceOf(Agent::class);
    expect($cursor)->toBeInstanceOf(McpClient::class);
    expect($cursor->isAgent())->toBeTrue();
    expect($cursor->isMcpClient())->toBeTrue();
});

test('Cursor has correct guidelines configuration', function () {
    $cursor = new Cursor;

    expect($cursor->guidelinesPath())->toBe('.cursor/rules/boots.mdc');
    expect($cursor->frontmatter())->toBeTrue();
});

test('Cursor has correct MCP configuration', function () {
    $cursor = new Cursor;

    expect($cursor->mcpConfigPath())->toBe('.cursor/mcp.json');
});

test('Cursor system detection config varies by platform', function () {
    $cursor = new Cursor;

    $darwinConfig = $cursor->systemDetectionConfig(Platform::Darwin);
    expect($darwinConfig)->toHaveKey('paths');
    expect($darwinConfig['paths'])->toContain('/Applications/Cursor.app');

    $linuxConfig = $cursor->systemDetectionConfig(Platform::Linux);
    expect($linuxConfig)->toHaveKey('paths');
    expect($linuxConfig['paths'])->toContain('/opt/cursor');
    expect($linuxConfig['paths'])->toContain('/usr/local/bin/cursor');
    expect($linuxConfig['paths'])->toContain('~/.local/bin/cursor');

    $windowsConfig = $cursor->systemDetectionConfig(Platform::Windows);
    expect($windowsConfig)->toHaveKey('paths');
    expect($windowsConfig['paths'])->toContain('%ProgramFiles%\\Cursor');
    expect($windowsConfig['paths'])->toContain('%LOCALAPPDATA%\\Programs\\Cursor');
});

test('Cursor project detection config looks for .cursor directory', function () {
    $cursor = new Cursor;

    $config = $cursor->projectDetectionConfig();

    expect($config)->toHaveKey('paths');
    expect($config['paths'])->toContain('.cursor');
});

test('Cursor detectInProject returns true when .cursor directory exists', function () {
    $tempDir = temporaryDirectory();
    $cursorDir = $tempDir.'/.cursor';
    mkdir($cursorDir, 0755, true);

    $cursor = new Cursor;

    expect($cursor->detectInProject($tempDir))->toBeTrue();

    // Cleanup
    rmdir($cursorDir);
    rmdir($tempDir);
});

test('Cursor detectInProject returns false when .cursor directory does not exist', function () {
    $tempDir = temporaryDirectory();

    $cursor = new Cursor;

    expect($cursor->detectInProject($tempDir))->toBeFalse();

    // Cleanup
    rmdir($tempDir);
});

test('Cursor agent name matches display name', function () {
    $cursor = new Cursor;

    expect($cursor->agentName())->toBe('Cursor');
});
