<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use Stringable;

/*, Stringable - after PHp8.0*/
abstract class AbstractVariableType implements VariableTypeInterface, Stringable
{
    public function __construct(
        protected bool $nullable
    ) {
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
        if (static::class !== $object::class) {
            return false;
        }
        return $this->isNullable() === $object->isNullable();
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
