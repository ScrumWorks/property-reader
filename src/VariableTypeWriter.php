<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\BooleanVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\FloatVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\IntegerVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\StringVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

final class VariableTypeWriter
{
    public function variableTypeToString(?VariableTypeInterface $variableType, $phpCompatible = false): string
    {
        if ($variableType === null || $variableType instanceof MixedVariableType) {
            if ($phpCompatible) {
                return '';
            }
            return 'mixed';
        } elseif ($variableType instanceof IntegerVariableType) {
            return ($variableType->isNullable() ? '?' : '') . 'int';
        } elseif ($variableType instanceof FloatVariableType) {
            return ($variableType->isNullable() ? '?' : '') . 'float';
        } elseif ($variableType instanceof BooleanVariableType) {
            return ($variableType->isNullable() ? '?' : '') . 'bool';
        } elseif ($variableType instanceof StringVariableType) {
            return ($variableType->isNullable() ? '?' : '') . 'string';
        } elseif ($variableType instanceof ArrayVariableType) {
            if ($phpCompatible) {
                return 'array';
            }
            if ($variableType->getKeyType() === null) {
                if ($variableType->getItemType() === null) {
                    return ($variableType->isNullable() ? '?' : '') . 'array';
                }
                return ($variableType->isNullable() ? '?' : '') . $this->variableTypeToString(
                    $variableType->getItemType()
                ) . '[]';
            }
            return \sprintf(
                '%sarray<%s, %s>',
                $variableType->isNullable() ? '?' : '',
                $this->variableTypeToString($variableType->getKeyType()),
                $this->variableTypeToString($variableType->getItemType())
            );
        } elseif ($variableType instanceof ClassVariableType) {
            return ($variableType->isNullable() ? '?' : '') . $variableType->getClass();
        } elseif ($variableType instanceof UnionVariableType) {
            if ($phpCompatible) {
                return '';
            }
            return ($variableType->isNullable() ? '?' : '') . \implode('|', \array_map(
                fn (VariableTypeInterface $_): string => $this->variableTypeToString($_, $phpCompatible),
                $variableType->getTypes()
            ));
        }
        return '';
    }
}
