<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use PHPUnit\Framework\TestCase;

class ClassVariableTypeTest extends TestCase
{
    public function testValidClass(): void
    {
        $classVariableType = new ClassVariableType(ClassVariableTypeTest::class, true);
        $this->assertEquals(ClassVariableTypeTest::class, $classVariableType->getClass());
    }

    public function testInvalidClass(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Unknown class 'some-not-existing-class' given");
        new ClassVariableType('some-not-existing-class', true);
    }
}
