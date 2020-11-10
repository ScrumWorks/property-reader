<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use PHPUnit\Framework\TestCase;

class UnionVariableTypeTest extends TestCase
{
    use VariableTypeCreatingTrait;

    public function testMinimumInputTypes(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Union must have minimal two types, 0 given");
        new UnionVariableType([], false);
    }

    public function testInputTypesMustBeVariableTypeInterface(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Given type 'integer' doesn't implements ScrumWorks\PropertyReader\VariableType\VariableTypeInterface");
        // @phpstan-ignore-next-line
        new UnionVariableType([1, new MixedVariableType()], false);
    }

    public function testEquals(): void
    {
        $this->assertTrue(
            $this->variableTypeEquals(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createString(false),
                    new ArrayVariableType(null, $this->createString(false), false),
                ], true),
                new UnionVariableType([
                    new ArrayVariableType(null, $this->createString(false), false),
                    $this->createString(false),
                    $this->createInteger(true),
                ], true)
            )
        );
        $this->assertTrue(
            $this->variableTypeEquals(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createInteger(true),
                    $this->createInteger(false),
                ], true),
                new UnionVariableType([
                    $this->createInteger(false),
                    $this->createInteger(true),
                ], true)
            )
        );
        $this->assertFalse(
            $this->variableTypeEquals(
                new UnionVariableType([
                    $this->createString(true),
                    $this->createInteger(true),
                    $this->createInteger(false),
                ], true),
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createInteger(false),
                ], true)
            )
        );
    }
}
