<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use PHPUnit\Framework\TestCase;

class ScalarVariableTypeTest extends TestCase
{
    public function testBadParameterType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Unknown 'some-not-exists-constant' scalar type given");
        new ScalarVariableType('some-not-exists-constant', false);
    }
}
