<?php

declare(strict_types=1);

namespace Liliana\Setup\Extra\Configurator\Definition\Macro;

class MacroData
{
    public function __construct(
        public Macro $type,
        public string $content
    )
    {
    }

}