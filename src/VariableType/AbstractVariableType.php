<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

use Nette\SmartObject;

/**
 * @property-read bool $nullable
 */
abstract class AbstractVariableType implements VariableTypeInterface
{
    use SmartObject;

    protected bool $nullable;

    public function __construct(bool $nullable)
    {
        $this->nullable = $nullable;
        $this->validate();
    }

    protected function isNullable(): bool
    {
        return $this->nullable;
    }

    protected function validate(): void
    {
    }
}
