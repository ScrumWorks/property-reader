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
        if ($a->nullable !== $b->nullable) {
            return false;
        }
        if ($a instanceof MixedVariableType) {
            return true;
        } elseif ($a instanceof ScalarVariableType) {
            /** @var ScalarVariableType $b */
            return $a->type === $b->type;
        } elseif ($a instanceof ArrayVariableType) {
            /** @var ArrayVariableType $b */
            return $this->same($a->keyType, $b->keyType)
                && $this->same($a->itemType, $b->itemType);
        } elseif ($a instanceof ClassVariableType) {
            /** @var ClassVariableType $b */
            return $a->class === $b->class;
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
            throw new Exception(\sprintf("Incompatible types '%s' and '%s'", $a->typeName, $b->typeName));
        }

        if ($a->nullable !== $b->nullable) {
            throw new Exception(\sprintf(
                "Incompatible nullable settings for '%s' and '%s'",
                $a->typeName,
                $b->typeName
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
        foreach ($a->types as $aType) {
            foreach ($b->types as $bType) {
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
        if ($a->type !== $b->type) {
            throw new Exception(\sprintf("Can't merge %s and %s scalar types", $a->type, $b->type));
        }

        return clone $a;
    }

    private function unifyArray(ArrayVariableType $a, ArrayVariableType $b): VariableTypeInterface
    {
        // `array` and `B` === `B`
        if ($a->itemType instanceof MixedVariableType && $a->keyType === null) {
            return clone $b;
        }
        if ($b->itemType instanceof MixedVariableType && $b->keyType === null) {
            return clone $a;
        }

        if (! $this->same($a->keyType, $b->keyType)) {
            throw new Exception(\sprintf('Array must have key type'));
        }

        return new ArrayVariableType(
            $this->unify($a->itemType, $b->itemType),
            $a->keyType !== null ? clone $a->keyType : null,
            $a->nullable
        );
    }

    private function unifyClass(ClassVariableType $a, ClassVariableType $b): VariableTypeInterface
    {
        if ($a->class !== $b->class) {
            throw new Exception(\sprintf("Can't merge %s and %s classes", $a->class, $b->class));
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
