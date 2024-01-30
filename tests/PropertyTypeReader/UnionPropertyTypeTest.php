<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture\UnionPropertyTypeTestClass;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;

final class UnionPropertyTypeTest extends AbstractPropertyTestCase
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(UnionPropertyTypeTestClass::class);
    }

    public function testPhpDocUnion(): void
    {
        $this->assertPhpDocVariableType(
            'phpDocUnion',
            new UnionVariableType([
                $this->createInteger(),
                $this->createString(),
            ], false)
        );
    }

    public function testPhpDocUnionWithNullable(): void
    {
        $this->assertPhpDocVariableType(
            'phpDocUnionNullable',
            new UnionVariableType([
                $this->createBoolean(),
                $this->createFloat()
            ], true)
        );
    }

    public function testPropertyTypeUnion(): void
    {
        $this->assertPropertyTypeVariableType(
            'propertyTypeUnion',
            new UnionVariableType([
                $this->createString(),
                $this->createInteger(),
            ], false)
        );
    }

    public function testPropertyTypeUnionWithNullable(): void
    {
        $this->assertPropertyTypeVariableType(
            'propertyTypeUnionNullable',
            new UnionVariableType([
                $this->createClass(UnionPropertyTypeTestClass::class),
                $this->createBoolean(),
            ], true)
        );
    }
}

