<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;

final class ScalarVariableType extends AbstractVariableType
{
    public const TYPE_INTEGER = 'INTEGER';

    public const TYPE_FLOAT = 'FLOAT';

    public const TYPE_BOOLEAN = 'BOOLEAN';

    public const TYPE_STRING = 'STRING';

    public function __construct(
        protected string $type,
        bool $nullable
    ) {
        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'SCALAR[' . $this->type . ']';
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(VariableTypeInterface $object): bool
    {
        if (! parent::equals($object)) {
            return false;
        }
        /** @var ScalarVariableType $object */
        return $this->getType() === $object->getType();
    }

    protected function validate(): void
    {
        if (! \in_array(
            $this->type,
            [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_BOOLEAN, self::TYPE_STRING],
            true
        )) {
            throw new InvalidArgumentException("Unknown '{$this->type}' scalar type given");
        }
    }
}
