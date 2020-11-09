<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use Nette\SmartObject;

/**
 * @property-read string $typeName
 * @property-read bool $nullable
 */
/*, Stringable - after PHp8.0*/
abstract class AbstractVariableType implements VariableTypeInterface
{
    use SmartObject;

    protected bool $nullable;

    public function __construct(bool $nullable)
    {
        $this->nullable = $nullable;
        $this->validate();
    }

    abstract public function __toString(): string;

    protected function isNullable(): bool
    {
        return $this->nullable;
    }

    abstract protected function validate(): void;

    protected function getTypeName(): string
    {
        return $this->__toString();
    }
}
