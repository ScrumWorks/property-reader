<?php

declare(strict_types = 1);

namespace Amateri\PropertyReader\Tests\VariableType;

use Amateri\PropertyReader\VariableType\MixedVariableType;
use Amateri\PropertyReader\VariableType\UnionVariableType;
use PHPUnit\Framework\TestCase;

class UnionVariableTypeTest extends TestCase
{
    public function testMinimumInputTypes(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Union must have minimal two types, 0 given");
        new UnionVariableType([], false);
    }

    public function testInputTypesMustBeVariableTypeInterface(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Given type 'integer' doesn't implements Amateri\PropertyReader\VariableType\VariableTypeInterface");
        // @phpstan-ignore-next-line
        new UnionVariableType([1, new MixedVariableType()], false);
    }
}
