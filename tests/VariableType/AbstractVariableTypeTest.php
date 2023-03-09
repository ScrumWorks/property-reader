<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use PHPUnit\Framework\TestCase;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;

class AbstractVariableTypeTest extends TestCase
{
    use VariableTypeCreatingTrait;

    public function testObjectEquals(): void
    {
        // `equals` is reflexive
        $this->assertTrue($this->variableTypeEquals(null, null));

        // `equals` is symmetric
        $this->assertFalse($this->variableTypeEquals(null, $this->createInteger(true)));
        $this->assertFalse($this->variableTypeEquals($this->createInteger(true), null));

        // nullable must have same values
        $this->assertFalse($this->variableTypeEquals($this->createInteger(true), $this->createInteger(false)));

        // objects must have same type
        $this->assertFalse($this->variableTypeEquals($this->createInteger(true), $this->createMixed()));
    }
}
