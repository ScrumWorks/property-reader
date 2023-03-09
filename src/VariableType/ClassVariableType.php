<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;

final class ClassVariableType extends AbstractVariableType
{
    public function __construct(
        protected string $class,
        bool $nullable
    ) {
        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'CLASS[' . $this->class . ']';
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function isClass(): bool
    {
        return \class_exists($this->class);
    }

    public function isInterface(): bool
    {
        return \interface_exists($this->class);
    }

    public function equals(VariableTypeInterface $object): bool
    {
        if (! parent::equals($object)) {
            return false;
        }
        /** @var ClassVariableType $object */
        return $this->getClass() === $object->getClass();
    }

    protected function validate(): void
    {
        if (! \class_exists($this->class) && ! \interface_exists($this->class)) {
            throw new InvalidArgumentException("Unknown class/interface '{$this->class}' given");
        }
    }
}
