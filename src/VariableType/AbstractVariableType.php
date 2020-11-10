<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

/*, Stringable - after PHp8.0*/
abstract class AbstractVariableType implements VariableTypeInterface
{
    protected bool $nullable;

    public function __construct(bool $nullable)
    {
        $this->nullable = $nullable;
        $this->validate();
    }

    abstract public function __toString(): string;

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getTypeName(): string
    {
        return $this->__toString();
    }

    public function equals(VariableTypeInterface $object): bool
    {
        if (static::class !== \get_class($object)) {
            return false;
        }
        if ($this->isNullable() !== $object->isNullable()) {
            return false;
        }
        return true;
    }

    public static function objectEquals(?VariableTypeInterface $a, ?VariableTypeInterface $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        return $a->equals($b);
    }

    abstract protected function validate(): void;
}
