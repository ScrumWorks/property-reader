<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use PHPUnit\Framework\TestCase;

class MixedVariableTypeTest extends TestCase
{
    public function testIsNullable(): void
    {
        $mixedVariableType = new MixedVariableType();
        $this->assertTrue($mixedVariableType->nullable);
    }
}
