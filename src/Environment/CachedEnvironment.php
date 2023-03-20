<?php

declare(strict_types=1);

namespace Liliana\Setup\Environment;

/**
 * Extension of BaseEnvironment with cached value.
 */
class CachedEnvironment extends BaseEnvironment
{
    private string $_env;

    public function get(): ?string
    {
        if ($this->_env) return $this->_env;
        return $this->_env = parent::get();
    }

}