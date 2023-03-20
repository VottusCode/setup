<?php

declare(strict_types=1);

namespace Liliana\Setup\Configurator\DebugMode;

/**
 * Classes that can read the global value of debug mode.
 */
interface DebugModeReader
{
    public function isDebugMode(): bool;

}