<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\Exception\LogicException;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

class GeneralPropertyTestClass
{
    public $property;

    /**
     * @var int
     */
    public $block;

    /** @var int */
    public $inlineBlock;

    /**
     * @var (int|null)[]
     */
    public $withBraces;
}

class GeneralPropertyTest extends AbstractPropertyTest
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(GeneralPropertyTestClass::class);
    }

    public function testEmptyDefinition(): void
    {
        $this->assertPropertyTypeVariableType('property', null);
        $this->assertPhpDocVariableType('property', null);
    }

    public function testAlternativePhpDocDefinitions(): void
    {
        // block definition
        $this->assertPhpDocVariableType('block', $this->createInteger(false));
        // inline definition
        $this->assertPhpDocVariableType('inlineBlock', $this->createInteger(false));
    }

    public function testNotAllowedBraces(): void
    {
        $property = $this->reflection->getProperty('withBraces');
        $this->expectException(LogicException::class);
        $this->expectErrorMessage('Braces are not support in type');
        $this->readFromPhpDoc($property);
    }
}
