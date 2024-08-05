<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType\Scalar;

use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

final class StringVariableType extends AbstractVariableType implements ScalarVariableType
{
    public function __construct(
        bool $nullable,
        private readonly bool $canBeEmpty,
    ) {
        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'STRING';
    }

    public function canBeEmpty(): bool
    {
        return $this->canBeEmpty;
    }

    protected function validate(): void
    {
    }
}
