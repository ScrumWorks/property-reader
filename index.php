<?php

declare(strict_types=1);

namespace Amateri\PropertyReader;

require_once __DIR__ . '/vendor/autoload.php';

use Nette\SmartObject;
use Nette\Utils\Reflection;
use Nette\Utils\Strings;

interface PropertyTypeInterface
{
}

/**
 * @property-read bool $nullable
 */
abstract class AbstractType implements PropertyTypeInterface
{
    use SmartObject;

    protected bool $nullable;

    public function __construct(bool $nullable)
    {
        $this->nullable = $nullable;
        $this->validate();
    }

    protected function isNullable(): bool
    {
        return $this->nullable;
    }

    protected function validate(): void
    {
    }
}


final class MixedType extends AbstractType
{
    public function __construct()
    {
        parent::__construct(true);
    }
}

/**
 * @property-read string $type
 */
final class ScalarType extends AbstractType
{
    const TYPE_INTEGER = 'INTEGER';
    const TYPE_FLOAT = 'FLOAT';
    const TYPE_BOOLEAN = 'BOOLEAN';
    const TYPE_STRING = 'STRING';

    protected string $type;

    public function __construct(string $type, bool $nullable)
    {
        $this->type = $type;
        parent::__construct($nullable);
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function validate(): void
    {
        parent::validate();
        if (!in_array($this->type, [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_BOOLEAN, self::TYPE_STRING])) {
            throw new \Exception("Invalid scalar type");
        }
    }
}

/**
 * @property-read PropertyTypeInterface $itemType
 * @property-read PropertyTypeInterface $keyType
 */
final class ArrayType extends AbstractType
{
    protected PropertyTypeInterface $itemType;
    protected PropertyTypeInterface $keyType;

    public function __construct(PropertyTypeInterface $itemType, PropertyTypeInterface $keyType, bool $nullable)
    {
        $this->itemType = $itemType;
        $this->keyType = $keyType;
        parent::__construct($nullable);
    }

    protected function getItemType(): PropertyTypeInterface
    {
        return $this->itemType;
    }

    protected function getKeyType(): PropertyTypeInterface
    {
        return $this->keyType;
    }

    protected function validate(): void
    {
        parent::validate();
        if ($this->keyType instanceof MixedType) return;
        if ($this->keyType instanceof ScalarType) {
            if (in_array($this->keyType->type, [ScalarType::TYPE_STRING, ScalarType::TYPE_INTEGER])) return;
        }
        throw new \Exception("Key type must be mixed, string or integer");
    }
}

/**
 * @property-read string $class FQN class name
 */
final class ClassType extends AbstractType
{
    protected string $class;

    public function __construct(string $class, bool $nullable)
    {
        $this->class = $class;
        parent::__construct($nullable);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function validate(): void
    {
        parent::validate();
        if (!class_exists($this->class)) {
            throw new \Exception("Unknown class");
        }
    }
}

/**
 * @property-read PropertyTypeInterface[] $types
 */
final class UnionType extends AbstractType
{
    protected array $types;

    /**
     * @param PropertyTypeInterface[] $types
     * @param bool $nullable
     */
    public function __construct(array $types, bool $nullable)
    {
        $this->types = $types;
        parent::__construct($nullable);
    }

    /**
     * @return PropertyTypeInterface[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    public function validate(): void
    {
        parent::validate();
        foreach ($this->types as $type) {
            if (!($type instanceof PropertyTypeInterface)) {
                throw new \Exception('All types must implements PropertyTypeInterface');
            }
        }
    }
}

// property type parser?
// TODO switch composer to 7.1+
final class PropertyReader
{
    public function readTypeFromType(\ReflectionProperty $property): ?PropertyTypeInterface
    {
        if (($propertyType = $property->getType()) instanceof \ReflectionNamedType) {
            $type = $propertyType->getName();
            $nullable = $propertyType->allowsNull();

            return $this->parseType(($nullable ? 'null|' : '') . $type, $property);
        }
        return null;
    }

    public function readTypeFromPhpDoc(\ReflectionProperty $property): ?PropertyTypeInterface
    {
        $type = $this->parseAnnotation($property, 'var');
        if (!$type) {
            return null;
        }
        return $this->parseType($type, $property);
    }

    private function parseType(string $type, \ReflectionProperty $property): ?PropertyTypeInterface
    {
        $nullable = false;

        $type = \preg_replace('/^\?/', 'null|', $type);
        $types = explode('|', $type);
        if (\array_search('null', $types, true)) {
            $nullable = true;
            $types = array_values(array_filter($types, static fn (string $type) => $type !== 'null'));
        }

        if (!$types) {
            throw new \Exception('Unresolve type');
        }

        if (count($types) > 1)  {
            return new UnionType(
                array_map(fn (string $type) => $this->parseType($type, $property), $types),
                $nullable
            );
        }

        $type = $types[0];

        if ($result = $this->tryIsScalar($type, $nullable)) {
            return $result;
        }
        if ($result = $this->tryIsArray($type, $nullable, $property)) {
            return $result;
        }
        if ($result = $this->tryIsObject($this->expandClassName($type, $property), $nullable)) {
            return $result;
        }

        throw new \Exception(sprintf('Unknown type "%s"', $type));
    }

    private function tryIsScalar(string $type, bool $nullable): ?PropertyTypeInterface
    {
        switch ($type) {
            case 'int':
            case 'integer':
                return new ScalarType(ScalarType::TYPE_INTEGER, $nullable);
            case 'float':
                return new ScalarType(ScalarType::TYPE_FLOAT, $nullable);
            case 'bool':
            case 'boolean':
                return new ScalarType(ScalarType::TYPE_BOOLEAN, $nullable);
            case 'string':
                return new ScalarType(ScalarType::TYPE_STRING, $nullable);
        }
        return null;
    }

    private function tryIsArray(string $type, bool $nullable, \ReflectionProperty $property): ?PropertyTypeInterface
    {
        if ($type === 'array') {
            return new ArrayType(new MixedType(), new MixedType(), $nullable);
        }
        if (substr($type, -2) === '[]') {
            $itemType = $this->parseType(substr($type, 0, -2), $property);
            return new ArrayType(
                $itemType,
                new MixedType(),
                $nullable
            );
        }
        if ($match = Strings::match($type, '~^array<((?P<key>[^,]+)\s*,\s*)?(?P<type>[^,]+)>$~')) {
            $itemType = $this->parseType($match['type'], $property);
            $keyType = $match['key'] ? $this->parseType($match['key'], $property) : new MixedType();
            return new ArrayType(
                $itemType,
                $keyType,
                $nullable
            );
        }

        return null;
    }

    private function tryIsObject(string $type, bool $nullable): ?PropertyTypeInterface
    {
        if (class_exists($type)) {
            return new ClassType($type, $nullable);
        }

        return null;
    }

    private function expandClassName(string $str, \ReflectionProperty $property): string
    {
        return Reflection::expandClassName($str, Reflection::getPropertyDeclaringClass($property));
    }

    private function parseAnnotation(\ReflectionProperty $ref, string $name): ?string
    {
        if (!Reflection::areCommentsAvailable()) {
            throw new \Nette\InvalidStateException('You have to enable phpDoc comments in opcode cache.');
        }
        $re = '#[\s*]@' . preg_quote($name, '#') . '(?=\s|$)(?:[ \t]+([^@\s].*))?#';

        if ($ref->getDocComment() && preg_match($re, trim($ref->getDocComment(), '/*'), $m)) {
            return $m[1] ?? '';
        }
        return null;
    }
}

$propertyReader = new PropertyReader();

class Test
{
    public int $test;

    /**
     * @var boolean
     */
    public array $test2;
}

$test = new Test();
$reflection = new \ReflectionObject($test);

foreach ($reflection->getProperties() as $property) {
    var_dump($propertyReader->readTypeFromPhpDoc($property));
    //var_dump($propertyReader->readTypeFromType($property));
}

