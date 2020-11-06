<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

/**
 * @property-read VariableTypeInterface[] $types
 */
final class UnionVariableType extends AbstractVariableType
{
    protected array $types;

    /**
     * @param VariableTypeInterface[] $types
     * @param bool $nullable
     */
    public function __construct(array $types, bool $nullable)
    {
        $this->types = $types;
        parent::__construct($nullable);
    }

    /**
     * @return VariableTypeInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function validate(): void
    {
        parent::validate();
        foreach ($this->types as $type) {
            if (!($type instanceof VariableTypeInterface)) {
                throw new \Exception('All types must implements VariableTypeInterface');
            }
        }
    }
}
