<?php

declare(strict_types=1);

namespace Liliana\Setup\Extra\Configurator;

use Liliana\Setup\Configurator\DebugMode\DebugModeReader;
use Liliana\Setup\Configurator\DebugMode\DebugModeWriter;
use Liliana\Setup\Configurator\Definition\DefinitionKind;
use Liliana\Setup\Configurator\PDIConfigurator as BasePDIConfigurator;
use Liliana\Setup\Exception\ConfiguratorException;
use Liliana\Setup\Extra\Configurator\Definition\Macro\Macro;
use Liliana\Setup\Extra\Configurator\Definition\Macro\MacroData;
use Liliana\Setup\Helper;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\ErrorHandler\ErrorHandler;
use function DI\autowire;

/**
 * Configurator with definition macros, debug mode and Symfony ErrorHandler
 */
class ExtraConfigurator extends BasePDIConfigurator implements DebugModeReader, DebugModeWriter
{
    public const Include = "include";

    protected array $macroMappings = [];

    public function __construct(
        ?string $tempDir = null,
        bool $debugMode = false,
        array $macroMappings = []
    )
    {
        parent::__construct($tempDir);

        $this->macroMappings = [
                Macro::Include->value => [$this, 'macroInclude']
            ] + $macroMappings;

        $this->setDebugMode($debugMode);
    }

    public static function include(string $content): MacroData
    {
        return new MacroData(Macro::Include, $content);
    }

    public function isDebugMode(): bool
    {
        return $this->getParameters()["debugMode"];
    }

    public function setDebugMode(bool $debugMode = true): static
    {
        $this->addParameter("debugMode", $debugMode);
        return $this;
    }

    /**
     * Enables the Symfony ErrorHandler component.
     *
     * @return ErrorHandler
     */
    public function enableErrorHandler(): ErrorHandler
    {
        return Debug::enable();
    }

    protected function processDefinitionInput(
        array $definitions,
        DefinitionKind $kind = DefinitionKind::ServiceDefinition,
    ): array
    {
        foreach ($definitions as $key => $value) {
            // Prevent use of $reservedKeys
            if (is_string($key)) {
                if (in_array($key, $this->reservedKeys)) {
                    throw new ConfiguratorException("Attempted to use reserved key '$key'");
                }

                if ($kind === DefinitionKind::ServiceDefinition && !Helper::objectExists($key)) {
                    throw new ConfiguratorException("Attempted to add '$key' as a service definition");
                }
            }

            // Macros defined with strings
            // eg. include::/path/to/file.php
            if (is_string($value)) {
                $match = Helper::match("/(\w+)::(.*)/", $value);
                var_dump($match);

                // Non-macro strings are considered parameters and therefore not allowed
                // to be defined as service definitions.
                if (!$match) {
                    if ($kind === DefinitionKind::ServiceDefinition) {
                        // Allow classes to be registered in sequential arrays, uses the autowire() helper
                        // eg. [AutowiredService::class] -> [AutowiredService::class => autowire(AutowiredService::class)]
                        if (is_int($key) && class_exists($value)) {
                            unset($definitions[$key]);
                            $definitions[$value] = autowire($value);

                            continue;
                        }
                    }

                    continue;
                }

                [, $type, $content] = $match;
                if (!$macroType = Macro::tryFrom($type)) {
                    throw new ConfiguratorException("Unknown macro '$type' used in '$value'");
                }

                $this->processMacros($definitions, $kind, $key, new MacroData($macroType, $content));
                continue;
            }

            // Macros defined using `Macro::Include->use(content)` or new `MacroData(Macro::Type, content)`
            if ($value instanceof MacroData) {
                $this->processMacros($definitions, $kind, $key, $value);
                continue;
            }

            // Non-macro sequential array entry that is not a service definition is not allowed
            if (is_int($key)) {
                throw new ConfiguratorException("Dangling sequential array entry '$key' => '$value'");
            }

        }

        return $definitions;
    }

    protected function processMacros(array &$definitions, DefinitionKind $kind, int|string $key, MacroData $macro): void
    {
        if (!array_key_exists($macro->type->value, $this->macroMappings) ||
            !($callable = $this->macroMappings[$macro->type->value])
        ) {
            throw new ConfiguratorException("Macro $macro->type is not mapped to a callable handler");
        }

        $callable($definitions, $kind, $key, $macro);
    }

    protected function macroInclude(array &$definitions, DefinitionKind $kind, int|string $key, MacroData $macro): void
    {
        $resolved = @include $macro->content;

        if (!is_array($resolved)) {
            $type = gettype($resolved);
            throw new ConfiguratorException("'$macro->content' resolved to a value of type $type while an array was expected");
        }

        $resolved = $this->processDefinitionInput($resolved, $kind);

        if (is_int($key)) {
            unset($definitions[$key]);
            foreach ($resolved as $resolvedKey => $resolvedValue) {
                $definitions[$resolvedKey] = $resolvedValue;
            }
        } else {
            $definitions[$key] = $resolved;
        }
    }

}