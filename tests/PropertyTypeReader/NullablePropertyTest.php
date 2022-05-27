<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ReflectionClass;
use ScrumWorks\PropertyReader\Exception\LogicException;
use ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture\NullablePropertyTestClass;

class NullablePropertyTest extends AbstractPropertyTest
{
    public function testNotNullable(): void
    {
        $property = $this->reflection->getProperty('notNullable');
        $this->assertEquals(false, $this->readFromPropertyType($property) ->isNullable());
        $this->assertEquals(false, $this->readFromPhpDoc($property) ->isNullable());
    }

    public function testNullableWithPhpSyntax(): void
    {
        // ?var syntax
        $property = $this->reflection->getProperty('nullable');
        $this->assertEquals(true, $this->readFromPropertyType($property) ->isNullable());
        $this->assertEquals(true, $this->readFromPhpDoc($property) ->isNullable());
    }

    public function testNullableWithUnionSyntax(): void
    {
        // var|null syntax
        $property = $this->reflection->getProperty('nullableSecondVariant');
        $this->assertEquals(true, $this->readFromPhpDoc($property) ->isNullable());
    }

    public function testMultipleNullableIsIgnored(): void
    {
        $property = $this->reflection->getProperty('multipleNullable');
        $this->assertEquals(true, $this->readFromPhpDoc($property) ->isNullable());
    }

    public function testNullableBadDefinition(): void
    {
        // property with only nullable definition
        $property = $this->reflection->getProperty('unresolvableNullable');
        $this->expectException(LogicException::class);
        $this->expectErrorMessage("Unresolvable definition 'null'");
        $this->readFromPhpDoc($property);
    }

    protected function createReflectionClass(): ReflectionClass
    {
        return new ReflectionClass(NullablePropertyTestClass::class);
    }
}
