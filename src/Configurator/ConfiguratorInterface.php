<?php

declare(strict_types=1);

namespace Liliana\Setup\Configurator;

use Liliana\Setup\Exception\ConfiguratorException;
use Liliana\Setup\Exception\ContainerBuildException;
use Psr\Container\ContainerInterface;

/**
 * Base interface for Configurator
 */
interface ConfiguratorInterface
{
    public function onBuild(callable $callable): static;

    public function offBuild(callable $callable): static;

    /**
     * @throws ConfiguratorException $tempDir is not a dir or isn't writable
     */
    public function setTempDirectory(?string $tempDir = null): static;

    /**
     * @throws ConfiguratorException Invalid path to a parameters file
     */
    public function addParameters(array $parameters): static;

    public function addParameter(string $key, mixed $value): static;

    /**
     * @throws ConfiguratorException Invalid path to a definition file
     */
    public function addServiceDefinitions(array $definitions): static;

    public function addServiceDefinition(string $key, mixed $definition): static;

    public function getDefinitions(): array;

    public function getParameters(): array;

    public function getTempDirectory(): ?string;

    /**
     * @throws ContainerBuildException
     */
    public function build(): ContainerInterface;

}