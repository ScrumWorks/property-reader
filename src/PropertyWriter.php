<?php

declare(strict_types=1);

namespace Amateri\PropertyReader;


use Amateri\PropertyReader\VariableType\ArrayVariableType;
use Amateri\PropertyReader\VariableType\ClassVariableType;
use Amateri\PropertyReader\VariableType\MixedVariableType;
use Amateri\PropertyReader\VariableType\ScalarVariableType;
use Amateri\PropertyReader\VariableType\UnionVariableType;
use Amateri\PropertyReader\VariableType\VariableTypeInterface;

final class PropertyWriter
{
    public function variableTypeToString(VariableTypeInterface $variableType, $phpCompatible = false): string
    {
        if ($variableType instanceof MixedVariableType) {
            if ($phpCompatible) {
                return '';
            }
            return 'mixed';
        } elseif ($variableType instanceof ScalarVariableType) {
            switch ($variableType->type) {
                case ScalarVariableType::TYPE_INTEGER: return ($variableType->nullable ? '?' : '') . 'int';
                case ScalarVariableType::TYPE_FLOAT: return ($variableType->nullable ? '?' : '') . 'float';
                case ScalarVariableType::TYPE_BOOLEAN: return ($variableType->nullable ? '?' : '') . 'bool';
                case ScalarVariableType::TYPE_STRING: return ($variableType->nullable ? '?' : '') . 'string';
            }
        } elseif ($variableType instanceof ArrayVariableType) {
            if ($phpCompatible) {
                return 'array';
            }
            if ($variableType->keyType === null) {
                if ($variableType->itemType instanceof MixedVariableType) {
                    return ($variableType->nullable ? '?' : '') . 'array';
                }
                return ($variableType->nullable ? '?' : '') . $this->variableTypeToString($variableType->itemType) . '[]';
            } else {
                return sprintf(
                    '%sarray<%s, %s>',
                    $variableType->nullable ? '?' : '',
                    $this->variableTypeToString($variableType->keyType),
                    $this->variableTypeToString($variableType->itemType)
                );
            }
        } elseif ($variableType instanceof ClassVariableType) {
            return ($variableType->nullable ? '?' : '') . $variableType->class;
        } elseif ($variableType instanceof UnionVariableType) {
            if ($phpCompatible) {
                return '';
            }
            return ($variableType->nullable ? '?' : '') . implode('|', array_map(
                fn (VariableTypeInterface $_) => $this->variableTypeToString($_, $phpCompatible),
                $variableType->types
            ));
        }
        return '';
    }
}