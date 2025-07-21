<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use ScrumWorks\PropertyReader\Exception\InvalidStateException;
use ScrumWorks\PropertyReader\Exception\LogicException;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\BooleanVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\FloatVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\IntegerVariableType;
use ScrumWorks\PropertyReader\VariableType\Scalar\StringVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

final class PropertyTypeReader implements PropertyTypeReaderInterface
{
    private const NULL_TYPE = 'null';

    public function __construct(
        private readonly VariableTypeUnifyServiceInterface $variableTypeUnifyService
    ) {
    }

    public function readUnifiedVariableType(ReflectionProperty $property): ?VariableTypeInterface
    {
        return $this->variableTypeUnifyService->unify(
            $this->readVariableTypeFromPropertyType($property),
            $this->readVariableTypeFromPhpDoc($property)
        );
    }

    public function readVariableTypeFromPropertyType(ReflectionProperty $property): ?VariableTypeInterface
    {
        $propertyType = $property->getType();
        if ($propertyType instanceof ReflectionNamedType) {
            return $this->createFromTypeNames([$propertyType->getName()], $propertyType->allowsNull(), $property);
        } elseif ($propertyType instanceof ReflectionUnionType) {
            $types = [];
            $nullable = false;
            foreach ($propertyType->getTypes() as $type) {
                if ($type->getName() === self::NULL_TYPE) {
                    $nullable = true;
                } else {
                    $types[] = $type->getName();
                }
                $nullable = $nullable || $type->allowsNull();
            }

            return $this->createFromTypeNames($types, $nullable, $property);
        }

        return null;
    }

    public function readVariableTypeFromPhpDoc(ReflectionProperty $property): ?VariableTypeInterface
    {
        if ($property->isPromoted()) {
            $construct = $property->getDeclaringClass()
                ->getConstructor();
            if ($construct === null) {
                return null;
            }

            $type = $this->parseAnnotation($construct, 'param', '$' . $property->getName());
        } else {
            $type = $this->parseAnnotation($property, 'var');
        }

        return $type ? $this->parseType($type, $property) : null;
    }

    private function parseType(string $type, ReflectionProperty $property): VariableTypeInterface
    {
        $nullable = false;

        if (\str_contains($type, '(') || \str_contains($type, ')')) {
            throw new LogicException('Braces are not support in type');
        }

        $type = \preg_replace('/^\?/', self::NULL_TYPE . '|', $type);
        $types = \array_map('trim', \preg_split('/\||<[^>]+>(*SKIP)(*FAIL)/', (string) $type));
        if (\in_array(self::NULL_TYPE, $types, true)) {
            $nullable = true;
            $types = \array_values(\array_filter($types, static fn (string $type): bool => $type !== self::NULL_TYPE));
        }

        if ($types === []) {
            throw new LogicException("Unresolvable definition '{$type}'");
        }

        return $this->createFromTypeNames($types, $nullable, $property);
    }

    /**
     * @param string[] $types
     */
    private function createFromTypeNames(
        array $types,
        bool $nullable,
        ReflectionProperty $property,
    ): VariableTypeInterface {
        if (\count($types) > 1) {
            return new UnionVariableType(
                \array_map(fn (string $type): VariableTypeInterface => $this->parseType($type, $property), $types),
                $nullable
            );
        }

        $type = $types[0];

        if (($result = $this->tryCreateMixed($type)) !== null) {
            return $result;
        }
        if (($result = $this->tryCreateScalar($type, $nullable)) !== null) {
            return $result;
        }
        if (($result = $this->tryCreateArray($type, $nullable, $property)) !== null) {
            return $result;
        }
        if (($result = $this->tryCreateObject($this->expandClassName($type, $property), $nullable)) !== null) {
            return $result;
        }

        throw new LogicException(\sprintf('Unknown type "%s"', $type));
    }

    private function tryCreateMixed(string $type): ?VariableTypeInterface
    {
        if ($type === 'mixed') {
            return new MixedVariableType();
        }
        return null;
    }

    private function tryCreateScalar(string $type, bool $nullable): ?VariableTypeInterface
    {
        [$templateType, $min, $max] = $this->tryParseTemplates($type);
        if (
            $templateType === 'int'
            && $min && $max
            && \preg_match('~^-?\d+|min$~', $min)
            && \preg_match('~^-?\d+|max$~', $max)
        ) {
            return new IntegerVariableType(
                $nullable,
                null,
                $min === 'min' ? null : (int) $min,
                $max === 'max' ? null : (int) $max,
            );
        }

        return match ($type) {
            'int', 'integer' => new IntegerVariableType($nullable, null),
            'positive-int' => new IntegerVariableType($nullable, $type, minValue: 1),
            'negative-int' => new IntegerVariableType($nullable, $type, maxValue: -1),
            'non-positive-int' => new IntegerVariableType($nullable, $type, maxValue: 0),
            'non-negative-int' => new IntegerVariableType($nullable, $type, minValue: 0),
            'non-zero-int' => new IntegerVariableType($nullable, $type),
            'float' => new FloatVariableType($nullable, null),
            'bool', 'boolean' => new BooleanVariableType($nullable, null),
            'string' => new StringVariableType($nullable, null),
            'non-empty-string', 'numeric-string', 'callable-string',
            'non-falsy-string', 'lowercase-string', 'class-string' => new StringVariableType($nullable, $type),
            default => null,
        };
    }

    private function tryCreateArray(string $type, bool $nullable, ReflectionProperty $property): ?VariableTypeInterface
    {
        if ($type === 'array') {
            return new ArrayVariableType(null, null, $nullable, null);
        }
        if (\str_ends_with($type, '[]')) {
            $itemType = $this->parseType(\substr($type, 0, -2), $property);
            return new ArrayVariableType(null, $itemType, $nullable, null);
        }

        [$templateType, $key, $value] = $this->tryParseTemplates($type);
        $typeExtension = $templateType === 'array' ? null : $templateType;

        return match ($templateType) {
            'non-empty-list', 'list' => $key === null && $value !== null
                ? new ArrayVariableType(null, $this->parseType($value, $property), $nullable, $typeExtension)
                : null,
            'non-empty-array', 'array' => new ArrayVariableType(
                $key ? $this->parseType($key, $property) : null,
                $value ? $this->parseType($value, $property) : null,
                $nullable,
                $typeExtension,
            ),
            default => null,
        };
    }

    private function tryCreateObject(string $type, bool $nullable): ?VariableTypeInterface
    {
        if (\class_exists($type) || \interface_exists($type)) {
            return new ClassVariableType($type, $nullable, null);
        }

        return null;
    }

    private function expandClassName(string $str, ReflectionProperty $property): string
    {
        if (\class_exists($str) || \interface_exists($str)) {
            return \ltrim($str, '\\');
        }
        return Reflection::expandClassName($str, Reflection::getPropertyDeclaringClass($property));
    }

    /**
     * @return array{0: string, 1: string|null, 2: string|null}
     */
    private function tryParseTemplates(string $type): array
    {
        $match = Strings::match($type, '~^(?P<type>[^<]+)\s*<((?P<t1>[^,]+)\s*,\s*)?(?P<t2>.+)>$~');
        if ($match) {
            return [
                $match['type'],
                $match['t1'] === '' ? null : $match['t1'],
                $match['t2'] === '' ? null : $match['t2'],
            ];
        }

        return [$type, null, null];
    }

    private function parseAnnotation(
        ReflectionProperty|ReflectionMethod $ref,
        string $name,
        string $paramName = '',
    ): ?string {
        if (! Reflection::areCommentsAvailable()) {
            throw new InvalidStateException('You have to enable phpDoc comments in opcode cache.');
        }
        $re = '#[\s*]@' . \preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s].*))?\s*' . \preg_quote($paramName) . '#';
        if ($ref->getDocComment() && \preg_match($re, \trim($ref->getDocComment(), '/*'), $m)) {
            return $m[1] !== '' && $m[1] !== '0' ? \trim($m[1]) : '';
        }
        return null;
    }
}
