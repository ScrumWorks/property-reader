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
}

