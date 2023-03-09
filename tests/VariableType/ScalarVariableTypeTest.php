<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use PHPUnit\Framework\TestCase;
use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

class ScalarVariableTypeTest extends TestCase
{
    use VariableTypeCreatingTrait;

    public function testBadParameterType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Unknown 'some-not-exists-constant' scalar type given");
        new ScalarVariableType('some-not-exists-constant', false);
    }

    public function testEquals(): void
    {
        $this->assertTrue($this->variableTypeEquals($this->createInteger(true), $this->createInteger(true)));
        $this->assertFalse($this->variableTypeEquals($this->createInteger(true), $this->createString(true)));
    }
}
