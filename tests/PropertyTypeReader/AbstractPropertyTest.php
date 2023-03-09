<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ScrumWorks\PropertyReader\PropertyTypeReader;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;
use ScrumWorks\PropertyReader\VariableTypeUnifyServiceInterface;

abstract class AbstractPropertyTest extends TestCase
{
    use VariableTypeCreatingTrait;

    protected PropertyTypeReader $propertyTypeReader;

    protected ReflectionClass $reflection;

    protected function setUp(): void
    {
        $variableTypeUnifyServiceMock = new class() implements VariableTypeUnifyServiceInterface {
            public function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): ?VariableTypeInterface
            {
                return null;
            }
        };
        $this->propertyTypeReader = new PropertyTypeReader($variableTypeUnifyServiceMock);
        $this->reflection = $this->createReflectionClass();
    }

    abstract protected function createReflectionClass(): ReflectionClass;

    protected function getPropertyReflection(string $propertyName): ReflectionProperty
    {
        try {
            return $this->reflection->getProperty($propertyName);
        } catch (ReflectionException $e) {
            $this->fail(\sprintf(
                "Expected property '%s' not exists on class %s",
                $propertyName,
                $this->reflection->getName()
            ));
        }
    }

    protected function assertPropertyTypeVariableType(string $propertyName, ?VariableTypeInterface $expected) {
        $property = $this->getPropertyReflection($propertyName);
        $this->assertEquals($expected, $this->readFromPropertyType($property));
    }

    protected function assertPhpDocVariableType(string $propertyName, ?VariableTypeInterface $expected) {
        $property = $this->getPropertyReflection($propertyName);
        $this->assertEquals($expected, $this->readFromPhpDoc($property));
    }

    protected function readFromPropertyType(ReflectionProperty $property): ?VariableTypeInterface
    {
        return $this->propertyTypeReader->readVariableTypeFromPropertyType($property);
    }

    protected function readFromPhpDoc(ReflectionProperty $property): ?VariableTypeInterface
    {
        return $this->propertyTypeReader->readVariableTypeFromPhpDoc($property);
    }
}
