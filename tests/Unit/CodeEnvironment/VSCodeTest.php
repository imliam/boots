<?php

use Boots\CodeEnvironment\VSCode;
use Boots\Enums\Platform;

test('VSCode has correct name and display name', function () {
    $vscode = new VSCode;

    expect($vscode->name())->toBe('vscode');
    expect($vscode->displayName())->toBe('VS Code');
});

test('VSCode implements McpClient interface', function () {
    $vscode = new VSCode;

    expect($vscode)->toBeInstanceOf(\Boots\Contracts\McpClient::class);
    expect($vscode->isMcpClient())->toBeTrue();
});

test('VSCode has correct MCP configuration', function () {
    $vscode = new VSCode;

    expect($vscode->mcpConfigPath())->toBe('.vscode/mcp.json');
    expect($vscode->mcpConfigKey())->toBe('servers');
});

test('VSCode system detection config varies by platform', function () {
    $vscode = new VSCode;

    $darwinConfig = $vscode->systemDetectionConfig(Platform::Darwin);
    expect($darwinConfig)->toHaveKey('paths');
    expect($darwinConfig['paths'])->toContain('/Applications/Visual Studio Code.app');

    $linuxConfig = $vscode->systemDetectionConfig(Platform::Linux);
    expect($linuxConfig)->toHaveKey('command');
    expect($linuxConfig['command'])->toBe('which code');

    $windowsConfig = $vscode->systemDetectionConfig(Platform::Windows);
    expect($windowsConfig)->toHaveKey('paths');
    expect($windowsConfig['paths'])->toContain('%ProgramFiles%\\Microsoft VS Code');
    expect($windowsConfig['paths'])->toContain('%LOCALAPPDATA%\\Programs\\Microsoft VS Code');
});

test('VSCode project detection config looks for .vscode directory', function () {
    $vscode = new VSCode;

    $config = $vscode->projectDetectionConfig();

    expect($config)->toHaveKey('paths');
    expect($config['paths'])->toContain('.vscode');
});

test('VSCode detectInProject returns true when .vscode directory exists', function () {
    $tempDir = temporaryDirectory();
    $vscodeDir = $tempDir.'/.vscode';
    mkdir($vscodeDir, 0755, true);

    $vscode = new VSCode;

    expect($vscode->detectInProject($tempDir))->toBeTrue();

    // Cleanup
    rmdir($vscodeDir);
    rmdir($tempDir);
});

test('VSCode detectInProject returns false when .vscode directory does not exist', function () {
    $tempDir = temporaryDirectory();

    $vscode = new VSCode;

    expect($vscode->detectInProject($tempDir))->toBeFalse();

    // Cleanup
    rmdir($tempDir);
});
