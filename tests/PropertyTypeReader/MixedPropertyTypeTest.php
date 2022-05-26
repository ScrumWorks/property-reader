<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ReflectionClass;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;

class MixedPropertyTypeTestClass
{
    /**
     * @var mixed
     */
    public $mixed;
}

class MixedPropertyTypeTest extends AbstractPropertyTest
{
    public function testMixedType(): void
    {
        $this->assertPropertyTypeVariableType('mixed', null);
        $this->assertPhpDocVariableType('mixed', new MixedVariableType());
    }

    protected function createReflectionClass(): ReflectionClass
    {
        return new ReflectionClass(MixedPropertyTypeTestClass::class);
    }
}
