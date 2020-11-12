<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableType;

use ScrumWorks\PropertyReader\Exception\InvalidArgumentException;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use PHPUnit\Framework\TestCase;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;

class ClassVariableTypeTest extends TestCase
{
    use VariableTypeCreatingTrait;

    public function testValidClass(): void
    {
        $classVariableType = new ClassVariableType(ClassVariableTypeTest::class, true);
        $this->assertEquals(ClassVariableTypeTest::class, $classVariableType->getClass());
    }

    public function testInvalidClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage("Unknown class/interface 'some-not-existing-class' given");
        new ClassVariableType('some-not-existing-class', true);
    }

    public function testEquals(): void
    {
        $this->assertTrue($this->variableTypeEquals(new ClassVariableType(ArrayVariableType::class, false), new ClassVariableType(ArrayVariableType::class, false)));
        $this->assertFalse($this->variableTypeEquals(new ClassVariableType(ArrayVariableType::class, false), new ClassVariableType(MixedVariableType::class, false)));
    }

    public function testIsClass(): void
    {
        $this->assertTrue((new ClassVariableType(ArrayVariableType::class, false))->isClass());
        $this->assertFalse((new ClassVariableType(ArrayVariableType::class, false))->isInterface());
    }

    public function testIsInterface(): void
    {
        $this->assertTrue((new ClassVariableType(\DateTimeInterface::class, false))->isInterface());
        $this->assertFalse((new ClassVariableType(\DateTimeInterface::class, false))->isClass());
    }
}
