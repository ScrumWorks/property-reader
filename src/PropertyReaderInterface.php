<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use ReflectionProperty;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

interface PropertyReaderInterface
{
    public function readUnifiedVariableType(ReflectionProperty $property): ?VariableTypeInterface;

    public function readVariableTypeFromPropertyType(ReflectionProperty $property): ?VariableTypeInterface;

    public function readVariableTypeFromPhpDoc(ReflectionProperty $property): ?VariableTypeInterface;
}
