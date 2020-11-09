<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use Exception;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

final class VariableTypeUnifyService implements VariableTypeUnifyServiceInterface
{
    /**
     * @TODO maybe move directly to VariableTypeInterface?
     */
    public function same(?VariableTypeInterface $a, ?VariableTypeInterface $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        if (! ($a instanceof $b)) {
            return false;
        }
        if ($a->isNullable() !== $b->isNullable()) {
            return false;
        }
        if ($a instanceof MixedVariableType) {
            return true;
        } elseif ($a instanceof ScalarVariableType) {
            /** @var ScalarVariableType $b */
            return $a->getType() === $b->getType();
        } elseif ($a instanceof ArrayVariableType) {
            /** @var ArrayVariableType $b */
            return $this->same($a->getKeyType(), $b->getKeyType())
                && $this->same($a->getItemType(), $b->getItemType());
        } elseif ($a instanceof ClassVariableType) {
            /** @var ClassVariableType $b */
            return $a->getClass() === $b->getClass();
        } elseif ($a instanceof UnionVariableType) {
            /** @var UnionVariableType $b */
            return $this->isSubset($a, $b) && $this->isSubset($b, $a);
        }
        return false;
    }

    public function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): VariableTypeInterface
    {
        if ($a === null && $b === null) {
            return new MixedVariableType();
        }
        if ($a === null) {
            $a = $b;
        }
        if ($b === null) {
            $b = $a;
        }

        /** @var VariableTypeInterface $a */
        /** @var VariableTypeInterface $b */
        if (! $a instanceof $b) {
            throw new Exception(\sprintf("Incompatible types '%s' and '%s'", $a->getTypeName(), $b->getTypeName()));
        }

        if ($a->isNullable() !== $b->isNullable()) {
            throw new Exception(\sprintf(
                "Incompatible nullable settings for '%s' and '%s'",
                $a->getTypeName(),
                $b->getTypeName()
            ));
        }

        if ($a instanceof MixedVariableType) {
            /** @var MixedVariableType $b */
            return $this->unifyMixed($a, $b);
        } elseif ($a instanceof ScalarVariableType) {
            /** @var ScalarVariableType $b */
            return $this->unifyScalar($a, $b);
        } elseif ($a instanceof ArrayVariableType) {
            /** @var ArrayVariableType $b */
            return $this->unifyArray($a, $b);
        } elseif ($a instanceof ClassVariableType) {
            /** @var ClassVariableType $b */
            return $this->unifyClass($a, $b);
        } elseif ($a instanceof UnionVariableType) {
            /** @var UnionVariableType $b */
            return $this->unifyUnion($a, $b);
        }

        throw new Exception(\sprintf('Uknown %s for merging', VariableTypeInterface::class));
    }

    /**
     * Is union type $a subset of $b?
     * Actually O(n^2) in worst case :/
     */
    private function isSubset(UnionVariableType $a, UnionVariableType $b): bool
    {
        foreach ($a->getTypes() as $aType) {
            foreach ($b->getTypes() as $bType) {
                if ($this->same($aType, $bType)) {
                    // found, we can move to next element
                    continue 2;
                }
            }
            // not found, it can't be subset
            return false;
        }
        return true;
    }

    private function unifyMixed(MixedVariableType $a, MixedVariableType $b): VariableTypeInterface
    {
        return clone $a;
    }

    private function unifyScalar(ScalarVariableType $a, ScalarVariableType $b): VariableTypeInterface
    {
        if ($a->getType() !== $b->getType()) {
            throw new Exception(\sprintf("Can't merge %s and %s scalar types", $a->getType(), $b->getType()));
        }

        return clone $a;
    }

    private function unifyArray(ArrayVariableType $a, ArrayVariableType $b): VariableTypeInterface
    {
        // `array` and `B` === `B`
        if ($a->getItemType() instanceof MixedVariableType && $a->getKeyType() === null) {
            return clone $b;
        }
        if ($b->getItemType() instanceof MixedVariableType && $b->getKeyType() === null) {
            return clone $a;
        }

        if (! $this->same($a->getKeyType(), $b->getKeyType())) {
            throw new Exception(\sprintf('Array must have key type'));
        }

        return new ArrayVariableType(
            $this->unify($a->getItemType(), $b->getItemType()),
            $a->getKeyType() !== null ? clone $a->getKeyType() : null,
            $a->isNullable()
        );
    }

    private function unifyClass(ClassVariableType $a, ClassVariableType $b): VariableTypeInterface
    {
        if ($a->getClass() !== $b->getClass()) {
            throw new Exception(\sprintf("Can't merge %s and %s classes", $a->getClass(), $b->getClass()));
        }

        return clone $a;
    }

    private function unifyUnion(UnionVariableType $a, UnionVariableType $b): VariableTypeInterface
    {
        if ($this->same($a, $b)) {
            return clone $a;
        }

        throw new Exception("Can't merge this union types (@TODO)");
    }
}
