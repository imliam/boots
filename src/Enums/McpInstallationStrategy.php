<?php

namespace Boots\Enums;

enum McpInstallationStrategy: string
{
    case SHELL = 'shell';
    case FILE = 'file';
    case NONE = 'none';
}
