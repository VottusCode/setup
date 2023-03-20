<?php

declare(strict_types=1);

namespace Liliana\Setup\Configurator;

use DI\ContainerBuilder;
use Exception;
use Liliana\Setup\Configurator\Definition\DefinitionKind;
use Liliana\Setup\Exception\ConfiguratorException;
use Liliana\Setup\Exception\ContainerBuildException;
use Liliana\Setup\Helper;
use Psr\Container\ContainerInterface;

/**
 * Configurator implementation utilizing PHP-DI.
 *
 * This class may be extended for use with any other PSR compliant
 * DI libraries.
 *
 * @see ConfiguratorInterface temporary location
 */
class PDIConfigurator implements ConfiguratorInterface
{
    protected array $onBuild = [];

    protected array $parameters = [];

    protected array $definitions = [];

    // Reserved for future use
    public const DefaultReservedKeys = [];

    /**
     * @throws ConfiguratorException
     */
    public function __construct(
        ?string $tempDir = null,
        protected array $reservedKeys = self::DefaultReservedKeys,
    )
    {
        if (!class_exists(ContainerBuilder::class)) {
            throw new ConfiguratorException("php-di");
        }

        $this->setTempDirectory($tempDir);
    }

    public function onBuild(callable $callable): static
    {
        $this->onBuild[] = $callable;
        return $this;
    }

    public function offBuild(callable $callable): static
    {
        $key = array_search($callable, $this->onBuild, true);

        if ($key !== false) {
            unset($this->onBuild[$key]);
        }

        return $this;
    }

    public function setTempDirectory(?string $tempDir = null): static
    {
        if (!is_null($tempDir)) {
            if (!is_dir($tempDir)) {
                throw new ConfiguratorException("$tempDir is not a directory");
            }

            if (!is_writable($tempDir)) {
                throw new ConfiguratorException("$tempDir is not writable");
            }
        }

        $this->addParameter("tempDir", $tempDir);

        return $this;
    }

    public function addParameters(array $parameters): static
    {
        $this->parameters = Helper::merge(
            $this->processDefinitionInput($parameters, DefinitionKind::Parameter),
            $this->parameters
        );
        return $this;
    }

    public function addParameter(string $key, mixed $value): static
    {
        return $this->addParameters([$key => $value]);
    }

    public function addServiceDefinitions(array $definitions): static
    {
        $this->definitions = Helper::merge(
            $this->processDefinitionInput($definitions),
            $this->definitions
        );
        return $this;
    }

    public function addServiceDefinition(string $key, mixed $definition): static
    {
        return $this->addServiceDefinitions([$key => $definition]);
    }

    public function getDefinitions(): array
    {
        return $this->definitions;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getTempDirectory(): ?string
    {
        return $this->getParameters()["tempDir"];
    }

    protected function processDefinitionInput(
        array $definitions,
        DefinitionKind $kind = DefinitionKind::ServiceDefinition,
    ): array
    {
        foreach ($definitions as $key => $value) {
            if (is_string($key)) {
                if (in_array($key, $this->reservedKeys)) {
                    throw new ConfiguratorException("Attempted to use reserved key '$key'");
                }

                if ($kind === DefinitionKind::ServiceDefinition && is_string($value)) {
                    throw new ConfiguratorException("Attempted to define parameter '$key' with service definitions");
                }
            }
        }

        return $definitions;
    }

    public function build(): ContainerInterface
    {
        try {
            $containerBuilder = new ContainerBuilder();

            $definitions = [
                ...$this->definitions,
                ...$this->parameters,
            ];

            $containerBuilder->addDefinitions($definitions);

            if ($tempDir = $this->getParameters()["tempDir"]) {
                $containerBuilder->enableCompilation($tempDir);
            }

            foreach ($this->onBuild as $callable) {
                $callable($containerBuilder);
            }

            return $containerBuilder->build();
        } catch (Exception $exception) {
            throw new ContainerBuildException("Failed to build container: " . $exception->getMessage(), previous: $exception);
        }
    }
}