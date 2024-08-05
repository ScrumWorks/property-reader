<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableTypeUnifyService;
use ScrumWorks\PropertyReader\Exception\IncompatibleVariableTypesException;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;

class GeneralUnifyTest extends AbstractUnifyTestCase
{
    public function testReflexivity(): void
    {
        $this->assertEquals(
            null,
            $this->unify(null, null)
        );

        $this->assertEquals(
            $this->createInteger(),
            $this->unify($this->createInteger(), $this->createInteger())
        );
    }

    public function testSymmetry(): void
    {
        $this->assertEquals(
            $this->createInteger(),
            $this->unify($this->createInteger(), null)
        );
        $this->assertEquals(
            $this->createInteger(),
            $this->unify(null, $this->createInteger())
        );
    }

    public function testIncompatibleTypes(): void
    {
        $this->expectException(IncompatibleVariableTypesException::class);
        $this->expectExceptionMessage("Incompatible types 'MIXED' and 'INTEGER'");
        $this->unify(
            $this->createMixed(),
            $this->createInteger(false)
        );
    }

    public function testIncompatibleNullableTypes(): void
    {
        $this->expectException(IncompatibleVariableTypesException::class);
        $this->expectExceptionMessage("Incompatible nullable settings for 'INTEGER' and 'INTEGER'");
        $this->assertEquals(
            $this->createInteger(true),
            $this->unify(
                $this->createInteger(true),
                $this->createInteger(false)
            )
        );
    }
}
