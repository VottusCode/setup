<?php

declare(strict_types=1);

namespace Liliana\Setup\Environment;

/**
 * Implementation with simple validation of the variable.
 */
class ValidatedEnvironment extends CachedEnvironment
{
    /**
     * Cached value of the environment variable
     * @var string|null
     */
    private ?string $_env = null;

    /**
     * @param string $key
     * @param string[] $allowedValues
     * @param string $fallbackValue
     */
    public function __construct(
        string $key,
        private readonly array $allowedValues,
        private readonly string $fallbackValue,
    )
    {
        parent::__construct($key);
    }

    public function __toString(): string
    {
        return self::get();
    }

    /**
     * Returns raw value of the environment variable without validation.
     *
     * @intenral
     */
    public function getRaw(): ?string
    {
        return parent::get();
    }

    /**
     * Validates the value against $allowedValues and falls back to $fallbackValue if invalid.
     *
     * @return string one of $allowedValues
     */
    public function get(): string
    {
        if (!is_null($this->_env)) {
            return $this->_env;
        }

        $env = self::getRaw();
        if ($env === null) {
            return $this->fallbackValue;
        }

        $env = trim(strtolower($env));

        if (!in_array($env, $this->allowedValues)) {
            trigger_error("Invalid environment " . json_encode($env) . ", expected " . json_encode($this->allowedValues));
            return $this->fallbackValue;
        }

        return $this->_env = $env;
    }

}