<?php

declare(strict_types=1);

namespace Liliana\Setup\Extra\Environment;

use Liliana\Setup\Environment\ValidatedEnvironment;

class Environment extends ValidatedEnvironment
{

    public const
        Prod = "prod",
        Dev = "dev",
        Environments = [self::Prod, self::Dev],
        FallbackEnv = self::Prod,
        Key = "ENVIRONMENT";

    public function __construct()
    {
        parent::__construct(
            self::Key,
            self::Environments,
            self::FallbackEnv
        );
    }

}