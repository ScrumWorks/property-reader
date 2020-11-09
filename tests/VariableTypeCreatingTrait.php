<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests;

use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

trait VariableTypeCreatingTrait
{
    private function createMixed(): MixedVariableType
    {
        return new MixedVariableType();
    }

    private function createInteger(bool $nullable): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, $nullable);
    }

    private function createFloat(bool $nullable): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_FLOAT, $nullable);
    }

    private function createBoolean(bool $nullable): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, $nullable);
    }

    private function createString(bool $nullable): ScalarVariableType
    {
        return new ScalarVariableType(ScalarVariableType::TYPE_STRING, $nullable);
    }
}
