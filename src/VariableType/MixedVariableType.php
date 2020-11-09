<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

final class MixedVariableType extends AbstractVariableType
{
    public function __construct()
    {
        parent::__construct(true);
    }

    public function __toString(): string
    {
        return 'MIXED';
    }

    protected function validate(): void
    {
    }
}
