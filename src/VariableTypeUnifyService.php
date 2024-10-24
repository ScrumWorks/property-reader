<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use ScrumWorks\PropertyReader\Exception\DomainException;
use ScrumWorks\PropertyReader\Exception\IncompatibleVariableTypesException;
use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;
use ScrumWorks\PropertyReader\VariableType\AbstractVariableType;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\StringVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

final class VariableTypeUnifyService implements VariableTypeUnifyServiceInterface
{
    public function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): ?VariableTypeInterface
    {
        if ($a === null && $b === null) {
            return null;
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
            throw new IncompatibleVariableTypesException(\sprintf(
                "Incompatible types '%s' and '%s'",
                $a->getTypeName(),
                $b->getTypeName()
            ));
        }

        if ($a->isNullable() !== $b->isNullable()) {
            throw new IncompatibleVariableTypesException(\sprintf(
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

        throw new InvalidArgumentException(\sprintf('Unknown %s for merging', VariableTypeInterface::class));
    }

    private function unifyMixed(MixedVariableType $a, MixedVariableType $b): VariableTypeInterface
    {
        return clone $a;
    }

    private function unifyScalar(ScalarVariableType $a, ScalarVariableType $b): VariableTypeInterface
    {
        if ($a::class !== $b::class) {
            throw new IncompatibleVariableTypesException(\sprintf(
                "Can't merge %s and %s scalar types",
                $a->getTypeName(),
                $b->getTypeName(),
            ));
        }
        if ($a instanceof StringVariableType && $b instanceof StringVariableType && $a->canBeEmpty() && ! $b->canBeEmpty()) {
            return clone $b;
        }

        return clone $a;
    }

    private function unifyArray(ArrayVariableType $a, ArrayVariableType $b): VariableTypeInterface
    {
        // if $a or $b is only generic array (`array`) we return second one
        if ($a->getItemType() === null && $a->getKeyType() === null) {
            return clone $b;
        }
        if ($b->getItemType() === null && $b->getKeyType() === null) {
            return clone $a;
        }

        if (! AbstractVariableType::objectEquals($a->getKeyType(), $b->getKeyType())) {
            throw new IncompatibleVariableTypesException('Array must have same key type');
        }

        return new ArrayVariableType(
            $a->getKeyType() !== null ? clone $a->getKeyType() : null,
            $this->unify($a->getItemType(), $b->getItemType()),
            $a->isNullable()
        );
    }

    private function unifyClass(ClassVariableType $a, ClassVariableType $b): VariableTypeInterface
    {
        if ($a->getClass() !== $b->getClass()) {
            throw new IncompatibleVariableTypesException(\sprintf(
                "Can't merge %s and %s classes",
                $a->getClass(),
                $b->getClass()
            ));
        }

        return clone $a;
    }

    private function unifyUnion(UnionVariableType $a, UnionVariableType $b): VariableTypeInterface
    {
        if ($a->equals($b)) {
            return clone $a;
        }

        throw new DomainException("Can't merge this union types (@TODO)");
    }
}
