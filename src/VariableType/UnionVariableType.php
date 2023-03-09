<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;

final class UnionVariableType extends AbstractVariableType
{
    /**
     * @param VariableTypeInterface[] $types
     */
    public function __construct(
        protected array $types,
        bool $nullable
    ) {
        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'UNION';
    }

    /**
     * @return VariableTypeInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function equals(VariableTypeInterface $object): bool
    {
        if (! parent::equals($object)) {
            return false;
        }
        /** @var UnionVariableType $object */
        return $this->isSubset($this, $object);
    }

    protected function validate(): void
    {
        if (\count($this->types) < 2) {
            throw new InvalidArgumentException(\sprintf(
                'Union must have minimal two types, %d given',
                \count($this->types)
            ));
        }
        foreach ($this->types as $type) {
            if (! ($type instanceof VariableTypeInterface)) {
                throw new InvalidArgumentException(\sprintf(
                    "Given type '%s' doesn't implements %s interface",
                    \get_debug_type($type),
                    VariableTypeInterface::class
                ));
            }
        }
    }

    /**
     * Is union type $a subset of $b?
     * Actually O(n^2) in worst case :/
     */
    private function isSubset(self $a, self $b): bool
    {
        foreach ($a->getTypes() as $aType) {
            foreach ($b->getTypes() as $bType) {
                if ($aType->equals($bType)) {
                    // found, we can move to next element
                    continue 2;
                }
            }
            // not found, it can't be subset
            return false;
        }
        return true;
    }
}
