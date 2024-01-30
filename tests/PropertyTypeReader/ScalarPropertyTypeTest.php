<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture\ScalarPropertyTypeTestClass;

final class ScalarPropertyTypeTest extends AbstractPropertyTestCase
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(ScalarPropertyTypeTestClass::class);
    }

    public function testIntegers(): void
    {
        $this->assertPropertyTypeVariableType(
            'integer',
            $this->createInteger(false)
        );
        $this->assertPhpDocVariableType(
            'integer',
            $this->createInteger(false)
        );
        $this->assertPhpDocVariableType(
            'integerAlternative',
            $this->createInteger(false)
        );
    }

    public function testFloats(): void
    {
        $this->assertPropertyTypeVariableType(
            'float',
            $this->createFloat(false)
        );
        $this->assertPhpDocVariableType(
            'float',
            $this->createFloat(false)
        );
    }

    public function testBooleans(): void
    {
        $this->assertPropertyTypeVariableType(
            'boolean',
            $this->createBoolean(false)
        );
        $this->assertPhpDocVariableType(
            'boolean',
            $this->createBoolean(false)
        );
        $this->assertPhpDocVariableType(
            'booleanAlternative',
            $this->createBoolean(false)
        );
    }

    public function testStrings(): void
    {
        $this->assertPropertyTypeVariableType(
            'string',
            $this->createString(false)
        );
        $this->assertPhpDocVariableType(
            'string',
            $this->createString(false)
        );
    }
}

