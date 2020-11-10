<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

class MixedPropertyTypeTestClass
{
    /**
     * @var mixed
     */
    public $mixed;
}

class MixedPropertyTypeTest extends AbstractPropertyTest
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(MixedPropertyTypeTestClass::class);
    }

    public function testMixedType(): void
    {
        $this->assertPropertyTypeVariableType('mixed', null);
        $this->assertPhpDocVariableType('mixed', new MixedVariableType());
    }
}

