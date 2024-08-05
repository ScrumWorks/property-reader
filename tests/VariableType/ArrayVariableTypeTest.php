<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use PHPUnit\Framework\TestCase;

class ArrayVariableTypeTest extends TestCase
{
    use VariableTypeCreatingTrait;

    public function testValidIntStringUnionKey(): void
    {
        $arrayVariableType = new ArrayVariableType(
            new UnionVariableType([
                $this->createString(),
                $this->createInteger(),
            ], false),
            new MixedVariableType(),
            true
        );
        $this->assertNotNull($arrayVariableType); // TODO maybe another assertation?
    }

    public function testInvalidNonScalarKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key type can be only string or integer, 'MIXED' given");
        new ArrayVariableType(new MixedVariableType(), new MixedVariableType(), true);
    }

    public function testInvalidScalarKeyWithBadType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key type can be only string or integer, 'BOOLEAN' given");
        new ArrayVariableType(
            $this->createBoolean(),
            new MixedVariableType(),
            true
        );
    }

    public function testInvalidNullableScalarKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key can't be nullable");
        new ArrayVariableType(
            $this->createString(nullable: true),
            new MixedVariableType(),
            true
        );
    }

    public function testInvalidNullableUnionKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key can't be nullable");
        new ArrayVariableType(
            new UnionVariableType([
                $this->createString(),
                $this->createInteger(),
            ], true),
            new MixedVariableType(),
            true
        );
    }

    public function testInvalidUnionKeyWithNonScalarTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Key type can be only string or integer, 'MIXED' given");
        new ArrayVariableType(
            new UnionVariableType([
                $this->createString(),
                new MixedVariableType(),
            ], false),
            new MixedVariableType(),
            false
        );
    }

    public function testEquals(): void
    {
        $this->assertTrue(
            $this->variableTypeEquals(
                new ArrayVariableType($this->createString(false), $this->createInteger(true), false),
                new ArrayVariableType($this->createString(false), $this->createInteger(true), false),
            )
        );
        $this->assertFalse(
            $this->variableTypeEquals(
                new ArrayVariableType($this->createString(false), $this->createString(true), false),
                new ArrayVariableType($this->createString(false), $this->createInteger(true), false),
            )
        );
        $this->assertFalse(
            $this->variableTypeEquals(
                new ArrayVariableType(null, $this->createInteger(true), false),
                new ArrayVariableType($this->createString(false), $this->createInteger(true), false),
            )
        );
    }

    public function testIsGenericArray(): void
    {
        $array = new ArrayVariableType(null, null, false);
        $this->assertTrue(
            $array->isGenericArray()
        );
    }

    public function testIsSequenceArray(): void
    {
        $array = new ArrayVariableType(null, $this->createInteger(false), false);
        $this->assertTrue(
            $array->isSequenceArray()
        );
    }

    public function testIsHashmap(): void
    {
        $array = new ArrayVariableType($this->createString(false), $this->createInteger(false), false);
        $this->assertTrue(
            $array->isHashmap()
        );

        $array = new ArrayVariableType($this->createString(false), null, false);
        $this->assertTrue(
            $array->isHashmap()
        );
    }
}
