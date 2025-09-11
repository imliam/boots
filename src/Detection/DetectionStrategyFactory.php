<?php

namespace Boots\Detection;

use Boots\Contracts\DetectionStrategy;
use InvalidArgumentException;

class DetectionStrategyFactory
{
    private const TYPE_DIRECTORY = 'directory';

    private const TYPE_COMMAND = 'command';

    private const TYPE_FILE = 'file';

    public function make(string|array $type, array $config = []): DetectionStrategy
    {
        if (is_array($type)) {
            return new CompositeDetectionStrategy(
                array_map(fn ($singleType) => $this->make($singleType, $config), $type)
            );
        }

        return match ($type) {
            self::TYPE_DIRECTORY => new DirectoryDetectionStrategy,
            self::TYPE_COMMAND => new CommandDetectionStrategy,
            self::TYPE_FILE => new FileDetectionStrategy,
            default => throw new InvalidArgumentException("Unknown detection type: {$type}"),
        };
    }

    public function makeFromConfig(array $config): DetectionStrategy
    {
        $type = $this->inferTypeFromConfig($config);

        return $this->make($type, $config);
    }

    private function inferTypeFromConfig(array $config): string|array
    {
        $typeMap = [
            'files' => self::TYPE_FILE,
            'paths' => self::TYPE_DIRECTORY,
            'command' => self::TYPE_COMMAND,
        ];

        $types = collect($typeMap)
            ->only(array_keys($config))
            ->values()
            ->all();

        if (empty($types)) {
            throw new InvalidArgumentException(
                'Cannot infer detection type from config keys. Expected one of: '.collect($typeMap)->keys()->join(', ')
            );
        }

        return count($types) > 1 ? $types : reset($types);
    }
}
