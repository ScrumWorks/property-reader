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
                new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
                new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            ], false),
            new MixedVariableType(),
            true
        );
        $this->assertNotNull($arrayVariableType); // TODO maybe another assertation?
    }

    public function testInvalidNonScalarKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Keys can be only scalar types, 'MIXED' given");
        new ArrayVariableType(new MixedVariableType(), new MixedVariableType(), true);
    }

    public function testInvalidScalarKeyWithBadType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Key type can be only string or integer, 'BOOLEAN' given");
        new ArrayVariableType(
            new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, false),
            new MixedVariableType(),
            true
        );
    }

    public function testInvalidNullableScalarKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Key can't be nullable");
        new ArrayVariableType(
            new ScalarVariableType(ScalarVariableType::TYPE_STRING, true),
            new MixedVariableType(),
            true
        );
    }

    public function testInvalidNullableUnionKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Key can't be nullable");
        new ArrayVariableType(
            new UnionVariableType([
                new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
                new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            ], true),
            new MixedVariableType(),
            true
        );
    }

    public function testInvalidUnionKeyWithNonScalarTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Keys can be only scalar types, 'MIXED' given");
        new ArrayVariableType(
            new UnionVariableType([
                new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
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
