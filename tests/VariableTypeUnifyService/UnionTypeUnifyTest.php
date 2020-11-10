<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\VariableType\UnionVariableType;

class UnionTypeUnifyTest extends AbstractUnifyTest
{
    public function testSameUnions(): void
    {
        $this->assertEquals(
            new UnionVariableType([
                $this->createInteger(true),
                $this->createString(false),
            ], true),
            $this->unify(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createString(false),
                ], true),
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createString(false),
                ], true)
            )
        );

        // in different order
        $this->assertEquals(
            new UnionVariableType([
                $this->createInteger(true),
                $this->createString(false),
            ], true),
            $this->unify(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createString(false),
                ], true),
                new UnionVariableType([
                    $this->createString(false),
                    $this->createInteger(true),
                ], true)
            )
        );
    }

    public function testIncompatibleUnions(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Can't merge this union types (@TODO)");
        $this->unify(
            new UnionVariableType([
                $this->createInteger(true),
                $this->createString(false),
            ], true),
            new UnionVariableType([
                $this->createString(false),
                $this->createInteger(false),
            ], true)
        );
    }
}
