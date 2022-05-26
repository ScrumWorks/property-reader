<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests;

use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

trait VariableTypeCreatingTrait
{
    protected function createMixed(): MixedVariableType
    {
        return new MixedVariableType();
    }

    protected function createInteger(bool $nullable = false): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, $nullable);
    }

    protected function createFloat(bool $nullable = false): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_FLOAT, $nullable);
    }

    protected function createBoolean(bool $nullable = false): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, $nullable);
    }

    protected function createString(bool $nullable = false): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_STRING, $nullable);
    }

    protected function createGenericArray(bool $nullable = false): ArrayVariableType
    {
        return new ArrayVariableType(null, null, $nullable);
    }

    protected function createSequenceArray(?VariableTypeInterface $type, bool $nullable = false): ArrayVariableType
    {
        return new ArrayVariableType(null, $type, $nullable);
    }

    protected function createHashmap(
        VariableTypeInterface $key,
        ?VariableTypeInterface $type,
        bool $nullable = false
    ): ArrayVariableType {
        return new ArrayVariableType($key, $type, $nullable);
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
