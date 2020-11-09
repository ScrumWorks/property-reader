<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use Exception;

final class ArrayVariableType extends AbstractVariableType
{
    protected VariableTypeInterface $itemType;

    protected ?VariableTypeInterface $keyType;

    public function __construct(VariableTypeInterface $itemType, ?VariableTypeInterface $keyType, bool $nullable)
    {
        $this->itemType = $itemType;
        $this->keyType = $keyType;

        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'ARRAY';
    }

    public function getItemType(): VariableTypeInterface
    {
        return $this->itemType;
    }

    public function getKeyType(): ?VariableTypeInterface
    {
        return $this->keyType;
    }

    protected function validate(): void
    {
        $keysToCheck = [];
        if ($this->keyType instanceof UnionVariableType) {
            if ($this->keyType->isNullable()) {
                throw new Exception("Key can't be nullable");
            }
            $keysToCheck += $this->keyType->getTypes();
        } else {
            $keysToCheck[] = $this->keyType;
        }

        foreach ($keysToCheck as $key) {
            if ($key === null) {
                continue;
            }
            if (! ($key instanceof ScalarVariableType)) {
                throw new Exception(\sprintf("Keys can be only scalar types, '%s' given", $key->getTypeName()));
            }
            if (! \in_array($key->getType(), [ScalarVariableType::TYPE_STRING, ScalarVariableType::TYPE_INTEGER])) {
                throw new Exception(\sprintf("Key type can be only string or integer, '%s' given", $key->getType()));
            }
            if ($key->nullable) {
                throw new Exception("Key can't be nullable");
            }
        }
    }
}
