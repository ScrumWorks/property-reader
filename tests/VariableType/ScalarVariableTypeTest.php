<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use PHPUnit\Framework\TestCase;

class ScalarVariableTypeTest extends TestCase
{
    use VariableTypeCreatingTrait;

    public function testEquals(): void
    {
        $this->assertTrue($this->variableTypeEquals($this->createInteger(true), $this->createInteger(true)));
        $this->assertFalse($this->variableTypeEquals($this->createInteger(true), $this->createString(true)));
    }
}
