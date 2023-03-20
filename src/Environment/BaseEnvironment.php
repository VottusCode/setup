<?php

declare(strict_types=1);

namespace Liliana\Setup\Environment;

class BaseEnvironment implements EnvironmentInterface
{
    public function __construct(
        protected readonly string $key
    )
    {
    }

    public function get(): ?string
    {
        $env = getenv($this->key);

        if (!is_string($env)) {
            trigger_error("Non-string value returned from getenv(" . json_encode($this->key) . "), got: " . json_encode($env));
            return null;
        }

        return $env;
    }
}