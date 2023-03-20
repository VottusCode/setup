<?php

namespace Liliana\Setup\Configurator\DebugMode;

use Liliana\Setup\Configurator\ConfiguratorInterface;

/**
 * Classes that can globally modify debug mode.
 */
interface DebugModeWriter
{
    public function setDebugMode(bool $debugMode): static;

}