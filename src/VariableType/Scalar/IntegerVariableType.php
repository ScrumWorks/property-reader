<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType\Scalar;

use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

final class IntegerVariableType extends AbstractVariableType implements ScalarVariableType
{
    public function __toString(): string
    {
        return 'INTEGER';
    }

    protected function validate(): void
    {
        // nothing to validate
    }
}
