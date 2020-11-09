<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use PHPUnit\Framework\TestCase;

class ArrayVariableTypeTest extends TestCase
{
    public function testValidIntStringUnionKey(): void
    {
        $arrayVariableType = new ArrayVariableType(
            new MixedVariableType(),
            new UnionVariableType([
                new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
                new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            ], false),
            true
        );
        $this->assertNotNull($arrayVariableType); // TODO maybe another assertation?
    }

    public function testInvalidNonScalarKey(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Keys can be only scalar types, 'MIXED' given");
        new ArrayVariableType(new MixedVariableType(), new MixedVariableType(), true);
    }

    public function testInvalidScalarKeyWithBadType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Key type can be only string or integer, 'BOOLEAN' given");
        new ArrayVariableType(
            new MixedVariableType(),
            new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, false),
            true
        );
    }

    public function testInvalidNullableScalarKey(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Key can't be nullable");
        new ArrayVariableType(
            new MixedVariableType(),
            new ScalarVariableType(ScalarVariableType::TYPE_STRING, true),
            true
        );
    }

    public function testInvalidNullableUnionKey(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Key can't be nullable");
        new ArrayVariableType(
            new MixedVariableType(),
            new UnionVariableType([
                new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
                new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            ], true),
            true
        );
    }

    public function testInvalidUnionKeyWithNonScalarTypes(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Keys can be only scalar types, 'MIXED' given");
        new ArrayVariableType(
            new MixedVariableType(),
            new UnionVariableType([
                new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
                new MixedVariableType(),
            ], false),
            false
        );
    }
}
