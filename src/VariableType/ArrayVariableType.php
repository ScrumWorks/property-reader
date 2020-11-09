<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

use Exception;

/**
 * @property-read VariableTypeInterface $itemType
 * @property-read VariableTypeInterface $keyType
 */
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

    protected function getItemType(): VariableTypeInterface
    {
        return $this->itemType;
    }

    protected function getKeyType(): ?VariableTypeInterface
    {
        return $this->keyType;
    }

    protected function validate(): void
    {
        $keysToCheck = [];
        if ($this->keyType instanceof UnionVariableType) {
            if ($this->keyType->nullable) {
                throw new Exception("Key can't be nullable");
            }
            $keysToCheck += $this->keyType->types;
        } else {
            $keysToCheck[] = $this->keyType;
        }

        foreach ($keysToCheck as $key) {
            if ($key === null) {
                continue;
            }
            if (! ($key instanceof ScalarVariableType)) {
                throw new Exception("Keys can be only scalar types, '{$key->typeName}' given");
            }
            if (! \in_array($key->type, [ScalarVariableType::TYPE_STRING, ScalarVariableType::TYPE_INTEGER])) {
                throw new Exception("Key type can be only string or integer, '{$key->type}' given");
            }
            if ($key->nullable) {
                throw new Exception("Key can't be nullable");
            }
        }
    }
}
