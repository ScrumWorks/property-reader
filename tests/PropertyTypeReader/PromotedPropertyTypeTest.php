<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture\PromotedPropertyTypeTestClass;

final class PromotedPropertyTypeTest extends AbstractPropertyTestCase
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(PromotedPropertyTypeTestClass::class);
    }

    public function testPhpDocScalar(): void
    {
        $this->assertPhpDocVariableType(
            'int',
            $this->createInteger(),
        );
    }

    public function testPhpDocArray(): void
    {
        $this->assertPhpDocVariableType(
            'arr',
            $this->createSequenceArray($this->createBoolean()),
        );
    }

    public function testPhpDocHashmap(): void
    {
        $this->assertPhpDocVariableType(
            'hashmap',
            $this->createHashmap($this->createString(), $this->createFloat()),
        );
    }

    public function testPropertyTypeScalar(): void
    {
        $this->assertPropertyTypeVariableType(
            'str',
            $this->createString(nullable: true),
        );
    }

    public function testPropertyTypeArray(): void
    {
        $this->assertPropertyTypeVariableType(
            'arr',
            $this->createGenericArray(),
        );
    }
}

