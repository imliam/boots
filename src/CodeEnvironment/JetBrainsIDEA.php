<?php

namespace Boots\CodeEnvironment;

use Boots\Contracts\Agent;
use Boots\Contracts\McpClient;
use Boots\Enums\Platform;

class JetBrainsIDEA extends CodeEnvironment implements Agent, McpClient
{
    public bool $useAbsolutePathForMcp = true;

    public function name(): string
    {
        return 'idea';
    }

    public function displayName(): string
    {
        return 'JetBrains IDEA';
    }

    public function systemDetectionConfig(Platform $platform): array
    {
        return match ($platform) {
            Platform::Darwin => [
                'paths' => [
                    '/Applications/IntelliJ IDEA.app',
                    '/Applications/PhpStorm.app',
                    '/Applications/WebStorm.app',
                    '/Applications/PyCharm.app',
                    '/Applications/CLion.app',
                    '/Applications/GoLand.app',
                    '/Applications/Rider.app',
                    '/Applications/RubyMine.app',
                    '/Applications/ReSharper.app',
                    '/Applications/RustRover.app',
                ],
            ],
            Platform::Linux => [
                'paths' => [
                    '/opt/intellij-idea',
                    '/opt/IntelliJ IDEA*',
                    '/usr/local/bin/intellij-idea',
                    '~/.local/share/JetBrains/Toolbox/apps/IntelliJ IDEA/ch-*',

                    '/opt/phpstorm',
                    '/opt/PhpStorm*',
                    '/usr/local/bin/phpstorm',
                    '~/.local/share/JetBrains/Toolbox/apps/PhpStorm/ch-*',

                    '/opt/webstorm',
                    '/opt/WebStorm*',
                    '/usr/local/bin/webstorm',
                    '~/.local/share/JetBrains/Toolbox/apps/WebStorm/ch-*',

                    '/opt/pycharm',
                    '/opt/PyCharm*',
                    '/usr/local/bin/pycharm',
                    '~/.local/share/JetBrains/Toolbox/apps/PyCharm/ch-*',

                    '/opt/clion',
                    '/opt/CLion*',
                    '/usr/local/bin/clion',
                    '~/.local/share/JetBrains/Toolbox/apps/CLion/ch-*',

                    '/opt/goland',
                    '/opt/GoLand*',
                    '/usr/local/bin/goland',
                    '~/.local/share/JetBrains/Toolbox/apps/GoLand/ch-*',

                    '/opt/rider',
                    '/opt/Rider*',
                    '/usr/local/bin/rider',
                    '~/.local/share/JetBrains/Toolbox/apps/Rider/ch-*',

                    '/opt/rubymine',
                    '/opt/RubyMine*',
                    '/usr/local/bin/rubymine',
                    '~/.local/share/JetBrains/Toolbox/apps/RubyMine/ch-*',

                    '/opt/resharper',
                    '/opt/ReSharper*',
                    '/usr/local/bin/resharper',
                    '~/.local/share/JetBrains/Toolbox/apps/ReSharper/ch-*',

                    '/opt/rustrover',
                    '/opt/RustRover*',
                    '/usr/local/bin/rustrover',
                    '~/.local/share/JetBrains/Toolbox/apps/RustRover/ch-*',
                ],
            ],
            Platform::Windows => [
                'paths' => [
                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',

                    '%ProgramFiles%\\JetBrains\\PhpStorm*',
                    '%LOCALAPPDATA%\\JetBrains\\Toolbox\\apps\\PhpStorm\\ch-*',
                ],
            ],
        };
    }

    public function projectDetectionConfig(): array
    {
        return [
            'paths' => ['.idea', '.junie'],
        ];
    }

    public function agentName(): string
    {
        return 'Junie';
    }

    public function mcpConfigPath(): string
    {
        return '.junie/mcp/mcp.json';
    }

    public function guidelinesPath(): string
    {
        return '.junie/guidelines.md';
    }
}
