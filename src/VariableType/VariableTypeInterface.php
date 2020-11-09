<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

interface VariableTypeInterface
{
    public function isNullable(): bool;

    public function getTypeName(): string;
}
