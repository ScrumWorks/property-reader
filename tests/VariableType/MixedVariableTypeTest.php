<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use PHPUnit\Framework\TestCase;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;

class MixedVariableTypeTest extends TestCase
{
    public function testIsNullable(): void
    {
        $mixedVariableType = new MixedVariableType();
        $this->assertTrue($mixedVariableType->isNullable());
    }
}
