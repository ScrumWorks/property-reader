<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

use Exception;

/**
 * @property-read VariableTypeInterface[] $types
 */
final class UnionVariableType extends AbstractVariableType
{
    protected array $types;

    /**
     * @param VariableTypeInterface[] $types
     */
    public function __construct(array $types, bool $nullable)
    {
        $this->types = $types;

        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'UNION';
    }

    /**
     * @return VariableTypeInterface[]
     */
    protected function getTypes(): array
    {
        return $this->types;
    }

    protected function validate(): void
    {
        if (\count($this->types) < 2) {
            throw new Exception(\sprintf('Union must have minimal two types, %d given', \count($this->types)));
        }
        foreach ($this->types as $type) {
            if (! ($type instanceof VariableTypeInterface)) {
                throw new Exception(\sprintf(
                    "Given type '%s' doesn't implements %s interface",
                    \is_object($type) ? \get_class($type) : \gettype($type),
                    VariableTypeInterface::class
                ));
            }
        }
    }
}
