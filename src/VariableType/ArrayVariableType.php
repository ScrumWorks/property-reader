<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;
use ScrumWorks\PropertyReader\VariableType\Scalar\IntegerVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\StringVariableType;

final class ArrayVariableType extends AbstractVariableType
{
    public function __construct(
        protected ?VariableTypeInterface $keyType,
        protected ?VariableTypeInterface $itemType,
        bool $nullable
    ) {
        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'ARRAY';
    }

    public function getKeyType(): ?VariableTypeInterface
    {
        return $this->keyType;
    }

    public function getItemType(): ?VariableTypeInterface
    {
        return $this->itemType;
    }

    public function isGenericArray(): bool
    {
        return $this->keyType === null && $this->itemType === null;
    }

    public function isSequenceArray(): bool
    {
        return $this->keyType === null && $this->itemType !== null;
    }

    public function isHashmap(): bool
    {
        return $this->keyType !== null;
    }

    public function equals(VariableTypeInterface $object): bool
    {
        if (! parent::equals($object)) {
            return false;
        }
        /** @var ArrayVariableType $object */
        return self::objectEquals($this->getKeyType(), $object->getKeyType())
            && self::objectEquals($this->getItemType(), $object->getItemType());
    }

    protected function validate(): void
    {
        $keysToCheck = [];
        if ($this->keyType instanceof UnionVariableType) {
            if ($this->keyType->isNullable()) {
                throw new InvalidArgumentException("Key can't be nullable");
            }
            $keysToCheck += $this->keyType->getTypes();
        } else {
            $keysToCheck[] = $this->keyType;
        }

        foreach ($keysToCheck as $key) {
            if ($key === null) {
                continue;
            }
            if (! $key instanceof IntegerVariableType && ! $key instanceof StringVariableType) {
                throw new InvalidArgumentException(\sprintf(
                    "Key type can be only string or integer, '%s' given",
                    $key->getTypeName(),
                ));
            }
            if ($key->nullable) {
                throw new InvalidArgumentException("Key can't be nullable");
            }
        }
    }
}
