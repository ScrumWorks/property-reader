<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture\ArrayPropertyTypeTestClass;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;

final class ArrayPropertyTypeTest extends AbstractPropertyTestCase
{
    protected function createReflectionClass(): \ReflectionClass
    {
        return new \ReflectionClass(ArrayPropertyTypeTestClass::class);
    }

    public function testSequenceArray(): void
    {
        // normal array definition (type[])
        $this->assertPropertyTypeVariableType(
            'array',
            $this->createGenericArray()
        );
        $this->assertPhpDocVariableType(
            'array',
            $this->createSequenceArray($this->createInteger())
        );

        // array alternative syntax (array<type>)
        $this->assertPhpDocVariableType(
            'arrayAlternative',
            $this->createSequenceArray($this->createInteger())
        );
    }

    public function testGenericArray(): void
    {
        // generic array syntax (array)
        $this->assertPhpDocVariableType(
            'genericArray',
            $this->createGenericArray()
        );
    }

    public function testNestedArray(): void
    {
        // nested array (aka int[][])
        $this->assertPhpDocVariableType(
            'nestedArray',
            $this->createSequenceArray(
                $this->createSequenceArray(
                    $this->createInteger()
                )
            )
        );

        // nested array alternative syntax (aka array<array<int>>)
        $this->assertPhpDocVariableType(
            'nestedArrayAlternative',
            $this->createSequenceArray(
                $this->createSequenceArray(
                    $this->createInteger()
                )
            )
        );
    }

    public function testHashmap(): void
    {
        // hashmap (aka array<string, string>)
        $this->assertPhpDocVariableType(
            'hashmap',
            $this->createHashmap(
                $this->createString(),
                $this->createString()
            )
        );
    }

    public function testNestedHashmap(): void
    {
        // nested hashmap (aka array<string, array<int, string>>)
        $this->assertPhpDocVariableType(
            'nestedHashmap',
            $this->createHashmap(
                $this->createString(),
                $this->createHashmap(
                    $this->createInteger(),
                    $this->createString()
                )
            )
        );
    }

    public function testComplicatedArray(): void
    {
        // complicated array: array<int|string, ?int[][]>
        $this->assertPhpDocVariableType(
            'complicatedArray',
            $this->createHashmap(
                new UnionVariableType([
                    $this->createInteger(),
                    $this->createString()
                ], false),
                $this->createSequenceArray(
                    $this->createSequenceArray(
                        $this->createInteger()
                    ),
                    true
                )
            )
        );
    }
}

