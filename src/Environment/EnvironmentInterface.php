<?php

declare(strict_types=1);

namespace Liliana\Setup\Environment;

interface EnvironmentInterface
{
    public function get(): ?string;
}