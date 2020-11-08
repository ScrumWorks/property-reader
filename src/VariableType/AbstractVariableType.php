<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

use Nette\SmartObject;

/**
 * @property-read string $typeName
 * @property-read bool $nullable
 */
abstract class AbstractVariableType implements VariableTypeInterface/*, Stringable - after PHp8.0*/
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

    abstract protected function validate(): void;

    abstract public function __toString(): string;

    protected function getTypeName(): string
    {
        return $this->__toString();
    }
}
