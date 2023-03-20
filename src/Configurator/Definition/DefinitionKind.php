<?php

namespace Liliana\Setup\Configurator\Definition;

enum DefinitionKind: string
{
    case ServiceDefinition = "service_definition";
    case Parameter = "parameter";

}
