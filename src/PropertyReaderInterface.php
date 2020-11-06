<?php

declare(strict_types=1);

namespace Amateri\PropertyReader;

use Amateri\PropertyReader\VariableType\VariableTypeInterface;

interface PropertyReaderInterface
{
    public function readVariableTypeFromPropertyType(\ReflectionProperty $property): ?VariableTypeInterface;

    public function readVariableTypeFromPhpDoc(\ReflectionProperty $property): ?VariableTypeInterface;
}
