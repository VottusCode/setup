<?php

declare(strict_types=1);

namespace Liliana\Setup\Extra\Configurator\Definition\Macro;

enum Macro: string
{
    case Include = "include";

    public function use(string $content): MacroData
    {
        return new MacroData($this, $content);
    }

}
