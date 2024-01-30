<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableTypeUnifyService;

use ScrumWorks\PropertyReader\Exception\IncompatibleVariableTypesException;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;

class ArrayTypeUnifyTest extends AbstractUnifyTestCase
{
    public function testGenericPlusGeneric(): void
    {
        // array + array == array
        $this->assertEquals(
            $this->createGenericArray(),
            $this->unify(
                $this->createGenericArray(),
                $this->createGenericArray(),
            )
        );
    }

    public function testGenericPlusSequence(): void
    {
        // array + int[] = int[]
        $this->assertEquals(
            $this->createSequenceArray($this->createInteger()),
            $this->unify(
                $this->createGenericArray(),
                $this->createSequenceArray($this->createInteger()),
            )
        );
    }

    public function testGenericPlusHashmap(): void
    {
        // array + int[] = int[]
        $this->assertEquals(
            $this->createHashmap($this->createString(), $this->createString()),
            $this->unify(
                $this->createGenericArray(),
                $this->createHashmap($this->createString(), $this->createString()),
            )
        );
    }

    public function testIncompatibleArrays(): void
    {
        // array<string, string> + string[] causes exception
        $this->expectException(IncompatibleVariableTypesException::class);
        $this->expectExceptionMessage("Array must have same key type");
        $this->unify(
            $this->createHashmap($this->createString(), $this->createString()),
            $this->createSequenceArray($this->createString())
        );
    }

    public function testIncompatibleArrays2(): void
    {
        // mixed[] + int[] causes exception
        $this->expectException(IncompatibleVariableTypesException::class);
        $this->expectExceptionMessage("Incompatible types 'MIXED' and 'SCALAR[INTEGER]'");
        $this->unify(
            $this->createSequenceArray($this->createMixed()),
            $this->createSequenceArray($this->createInteger())
        );
    }
}
