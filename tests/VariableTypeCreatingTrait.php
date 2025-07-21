<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests;

use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\BooleanVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\FloatVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\IntegerVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\StringVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

trait VariableTypeCreatingTrait
{
    protected function createMixed(): MixedVariableType
    {
        return new MixedVariableType();
    }

    protected function createInteger(
        bool $nullable = false,
        ?string $typeExtension = null,
        ?int $min = null,
        ?int $max = null,
    ): ScalarVariableType {
        return new IntegerVariableType($nullable, $typeExtension, $min, $max);
    }

    protected function createFloat(bool $nullable = false): ScalarVariableType
    {
        return new FloatVariableType($nullable);
    }

    protected function createBoolean(bool $nullable = false): ScalarVariableType
    {
        return new BooleanVariableType($nullable);
    }

    protected function createString(bool $nullable = false): ScalarVariableType
    {
        return new StringVariableType($nullable);
    }

    protected function createGenericArray(bool $nullable = false): ArrayVariableType
    {
        return new ArrayVariableType(null, null, $nullable);
    }

    protected function createSequenceArray(
        ?VariableTypeInterface $type,
        bool $nullable = false,
        ?string $typeExtension = null,
    ): ArrayVariableType {
        return new ArrayVariableType(null, $type, $nullable, $typeExtension);
    }

    protected function createHashmap(
        VariableTypeInterface $key,
        ?VariableTypeInterface $type,
        bool $nullable = false,
        ?string $typeExtension = null,
    ): ArrayVariableType {
        return new ArrayVariableType($key, $type, $nullable, $typeExtension);
    }

    protected function createClass(string $className, bool $nullable = false): ClassVariableType
    {
        return new ClassVariableType($className, $nullable);
    }

    protected function variableTypeEquals(?VariableTypeInterface $a, ?VariableTypeInterface $b): bool
    {
        return AbstractVariableType::objectEquals($a, $b);
    }
}
