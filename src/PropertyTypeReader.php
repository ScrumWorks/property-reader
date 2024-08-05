<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use Nette\Utils\Reflection;
use Nette\Utils\Strings;
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
        $type = $this->parseAnnotation($property, 'var');
        if (! $type) {
            return null;
        }
        return $this->parseType($type, $property);
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
        return match ($type) {
            'int', 'integer' => new IntegerVariableType($nullable),
            'float' => new FloatVariableType($nullable),
            'bool', 'boolean' => new BooleanVariableType($nullable),
            'string' => new StringVariableType($nullable, canBeEmpty: true),
            'non-empty-string' => new StringVariableType($nullable, canBeEmpty: false),
            default => null,
        };
    }

    private function tryCreateArray(string $type, bool $nullable, ReflectionProperty $property): ?VariableTypeInterface
    {
        if ($type === 'array') {
            return new ArrayVariableType(null, null, $nullable);
        }
        if (\str_ends_with($type, '[]')) {
            $itemType = $this->parseType(\substr($type, 0, -2), $property);
            return new ArrayVariableType(null, $itemType, $nullable);
        }
        if ($match = Strings::match($type, '~^array<((?P<key>[^,]+)\s*,\s*)?(?P<type>.+)>$~')) {
            $itemType = $this->parseType($match['type'], $property);
            $keyType = $match['key'] ? $this->parseType($match['key'], $property) : null;
            return new ArrayVariableType($keyType, $itemType, $nullable);
        }

        return null;
    }

    private function tryCreateObject(string $type, bool $nullable): ?VariableTypeInterface
    {
        if (\class_exists($type) || \interface_exists($type)) {
            return new ClassVariableType($type, $nullable);
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

    private function parseAnnotation(ReflectionProperty $ref, string $name): ?string
    {
        if (! Reflection::areCommentsAvailable()) {
            throw new InvalidStateException('You have to enable phpDoc comments in opcode cache.');
        }
        $re = '#[\s*]@' . \preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s].*))?#';
        if ($ref->getDocComment() && \preg_match($re, \trim($ref->getDocComment(), '/*'), $m)) {
            return $m[1] !== '' && $m[1] !== '0' ? \trim($m[1]) : '';
        }
        return null;
    }
}
