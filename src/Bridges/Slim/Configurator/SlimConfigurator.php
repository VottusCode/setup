<?php

declare(strict_types=1);

namespace Liliana\Setup\Bridges\Slim\Configurator;

use DI\Bridge\Slim\Bridge;
use Liliana\Setup\Exception\ConfiguratorException;
use Liliana\Setup\Extra\Configurator\ExtraConfigurator;
use Psr\Container\ContainerInterface;
use Slim\App;

class SlimConfigurator extends ExtraConfigurator
{
    public function addSlimDefinition(array $bootstraps = [], App|callable|null $app = null): static
    {
        $this->addParameter("bootstraps", $bootstraps);
        $this->addParameter("_app", $app ?? fn (ContainerInterface $container) => Bridge::create($container));

        $this->addServiceDefinition(App::class, function (ContainerInterface $di) {
            $app = $di->get("_app");
            
            foreach ($di->get("bootstraps") as $bootstrap) {
                $callable = null;

                if (is_string($bootstrap) && is_file($bootstrap)) {
                    if (!is_readable($bootstrap)) {
                        throw new ConfiguratorException("Cannot read bootstrap file '$bootstrap'");
                    }
                    if (!is_callable($callable = @include $bootstrap)) {
                        throw new ConfiguratorException("Registered invalid bootstrap '$bootstrap'");
                    }
                } elseif (is_callable($bootstrap)) {
                    $callable = $bootstrap;
                } else {
                    $type = gettype($bootstrap);
                    throw new ConfiguratorException("Unknown bootstrap of type '$type' found");
                }

                $callable($app, $di);
            }

            return $app;
        });

        return $this;
    }

}