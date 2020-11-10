<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;

class UnionPropertyTypeTestClass
{
    /**
     * @var int|string
     */
    public $union;

    /**
     * @phpstan-ignore-next-line
     * @var ?bool|float
     */
    public $unionNullable;
}

class UnionPropertyTypeTest extends AbstractPropertyTest
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(UnionPropertyTypeTestClass::class);
    }

    public function testUnion(): void
    {
        $this->assertPhpDocVariableType(
            'union',
            new UnionVariableType([
                $this->createInteger(),
                $this->createString()
            ], false)
        );
    }

    public function testUnionWithNullable(): void
    {
        $this->assertPhpDocVariableType(
            'unionNullable',
            new UnionVariableType([
                $this->createBoolean(),
                $this->createFloat()
            ], true)
        );
    }
}

