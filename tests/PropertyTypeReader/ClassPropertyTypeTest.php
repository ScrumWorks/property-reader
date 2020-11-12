<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\PropertyTypeReader;

class ClassPropertyTypeTestClass
{
    /**
     * @var PropertyTypeReader
     */
    public PropertyTypeReader $class;

    /**
     * @var \DateTimeInterface
     */
    public \DateTimeInterface $interface;

    /**
     * @phpstan-ignore-next-line
     * @var SomeNotExistsClass
     */
    public $notExistsClass;
}

class ClassPropertyTypeTest extends AbstractPropertyTest
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(ClassPropertyTypeTestClass::class);
    }

    public function testClassType(): void
    {
        $this->assertPropertyTypeVariableType(
            'class',
            $this->createClass(PropertyTypeReader::class)
        );
        $this->assertPhpDocVariableType(
            'class',
            $this->createClass(PropertyTypeReader::class)
        );
    }

    public function testInterfaceType(): void
    {
        $this->assertPropertyTypeVariableType(
            'interface',
            $this->createClass(\DateTimeInterface::class)
        );
        $this->assertPhpDocVariableType(
            'interface',
            $this->createClass(\DateTimeInterface::class)
        );
    }

    public function testNotExistingClass(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown type "SomeNotExistsClass"');
        $property = $this->getPropertyReflection('notExistsClass');
        $this->readFromPhpDoc($property);
    }
}

