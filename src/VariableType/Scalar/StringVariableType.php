<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType\Scalar;

use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

final class StringVariableType extends AbstractVariableType implements ScalarVariableType
{
    public function __construct(bool $nullable, ?string $typeExtension = null)
    {
        parent::__construct($nullable, $typeExtension);
    }

    public function __toString(): string
    {
        return 'STRING';
    }

    protected function validate(): void
    {
    }
}
