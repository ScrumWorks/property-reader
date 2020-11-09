<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use Exception;
use Nette\InvalidStateException;
use Nette\Utils\Reflection;
use Nette\Utils\Strings;
use ReflectionNamedType;
use ReflectionProperty;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

final class PropertyReader implements PropertyReaderInterface
{
    private VariableTypeUnifyServiceInterface $variableTypeUnifyService;

    public function __construct(VariableTypeUnifyServiceInterface $variableTypeUnifyService)
    {
        $this->variableTypeUnifyService = $variableTypeUnifyService;
    }

    public function readUnifiedVariableType(ReflectionProperty $property): VariableTypeInterface
    {
        return $this->variableTypeUnifyService->unify(
            $this->readVariableTypeFromPropertyType($property),
            $this->readVariableTypeFromPhpDoc($property)
        );
    }

    public function readVariableTypeFromPropertyType(ReflectionProperty $property): ?VariableTypeInterface
    {
        if (($propertyType = $property->getType()) instanceof ReflectionNamedType) {
            $type = $propertyType->getName();
            $nullable = $propertyType->allowsNull();

            return $this->parseType(($nullable ? 'null|' : '') . $type, $property);
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

        if (\strpos($type, '(') !== false || \strpos($type, ')') !== false) {
            throw new Exception('Braces are not support in type');
        }

        $type = \preg_replace('/^\?/', 'null|', $type);
        $types = \array_map('trim', \preg_split('/\||<[^>]+>(*SKIP)(*FAIL)/', $type));
        if (\array_search('null', $types, true) !== false) {
            $nullable = true;
            $types = \array_values(\array_filter($types, static fn (string $type) => $type !== 'null'));
        }

        if (! $types) {
            throw new Exception("Unresolvable definition '${type}'");
        }

        if (\count($types) > 1) {
            return new UnionVariableType(
                \array_map(fn (string $type) => $this->parseType($type, $property), $types),
                $nullable
            );
        }

        $type = $types[0];

        if ($result = $this->tryIsMixed($type)) {
            return $result;
        }
        if ($result = $this->tryIsScalar($type, $nullable)) {
            return $result;
        }
        if ($result = $this->tryIsArray($type, $nullable, $property)) {
            return $result;
        }
        if ($result = $this->tryIsObject($this->expandClassName($type, $property), $nullable)) {
            return $result;
        }

        throw new Exception(\sprintf('Unknown type "%s"', $type));
    }

    private function tryIsMixed(string $type): ?VariableTypeInterface
    {
        if ($type === 'mixed') {
            return new MixedVariableType();
        }
        return null;
    }

    private function tryIsScalar(string $type, bool $nullable): ?VariableTypeInterface
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, $nullable);
            case 'float':
                return new ScalarVariableType(ScalarVariableType::TYPE_FLOAT, $nullable);
            case 'bool':
            case 'boolean':
                return new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, $nullable);
            case 'string':
                return new ScalarVariableType(ScalarVariableType::TYPE_STRING, $nullable);
        }
        return null;
    }

    private function tryIsArray(string $type, bool $nullable, ReflectionProperty $property): ?VariableTypeInterface
    {
        if ($type === 'array') {
            return new ArrayVariableType(new MixedVariableType(), null, $nullable);
        }
        if (\substr($type, -2) === '[]') {
            $itemType = $this->parseType(\substr($type, 0, -2), $property);
            return new ArrayVariableType($itemType, null, $nullable);
        }
        if ($match = Strings::match($type, '~^array<((?P<key>[^,]+)\s*,\s*)?(?P<type>.+)>$~')) {
            $itemType = $this->parseType($match['type'], $property);
            $keyType = $match['key'] ? $this->parseType($match['key'], $property) : null;
            return new ArrayVariableType($itemType, $keyType, $nullable);
        }

        return null;
    }

    private function tryIsObject(string $type, bool $nullable): ?VariableTypeInterface
    {
        if (\class_exists($type)) {
            return new ClassVariableType($type, $nullable);
        }

        return null;
    }

    private function expandClassName(string $str, ReflectionProperty $property): string
    {
        if (\class_exists($str)) {
            return $str;
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
            return $m[1] ? \trim($m[1]) : '';
        }
        return null;
    }
}
