<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType\Scalar;

use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

final class IntegerVariableType extends AbstractVariableType implements ScalarVariableType
{
    public function __construct(
        bool $nullable,
        ?string $typeExtension = null,
        private readonly ?int $minValue = null,
        private readonly ?int $maxValue = null,
    ) {
        parent::__construct($nullable, $typeExtension);
    }

    public function __toString(): string
    {
        return 'INTEGER';
    }

    public function getMinValue(): ?int
    {
        return $this->minValue;
    }

    public function getMaxValue(): ?int
    {
        return $this->maxValue;
    }

    protected function validate(): void
    {
        // nothing to validate
    }
}
