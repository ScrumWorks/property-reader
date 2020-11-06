<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;


/**
 * @property-read VariableTypeInterface $itemType
 * @property-read VariableTypeInterface $keyType
 */
final class ArrayVariableType extends AbstractVariableType
{
    protected VariableTypeInterface $itemType;
    protected VariableTypeInterface $keyType;

    public function __construct(VariableTypeInterface $itemType, VariableTypeInterface $keyType, bool $nullable)
    {
        $this->itemType = $itemType;
        $this->keyType = $keyType;
        parent::__construct($nullable);
    }

    protected function getItemType(): VariableTypeInterface
    {
        return $this->itemType;
    }

    protected function getKeyType(): VariableTypeInterface
    {
        return $this->keyType;
    }

    protected function validate(): void
    {
        parent::validate();
        if ($this->keyType instanceof MixedVariableType) return;
        if ($this->keyType instanceof ScalarVariableType) {
            if (in_array($this->keyType->type, [ScalarVariableType::TYPE_STRING, ScalarVariableType::TYPE_INTEGER])) return;
        }
        throw new \Exception("Key type must be mixed, string or integer");
    }
}
