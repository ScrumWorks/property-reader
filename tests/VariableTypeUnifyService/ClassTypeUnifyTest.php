<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader;

use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

class ClassTypeUnifyTest extends AbstractUnifyTest
{
    public function testSameClasses(): void
    {
        $this->assertEquals(
            $this->createClass(ArrayVariableType::class),
            $this->unify(
                $this->createClass(ArrayVariableType::class),
                $this->createClass(ArrayVariableType::class)
            )
        );
    }

    public function testDifferentClasses(): void
    {
        $this->expectException(\Exception::class);
        $this->expectErrorMessage(sprintf(
            "Can't merge %s and %s classes",
            ArrayVariableType::class,
            ScalarVariableType::class
        ));
        $this->unify(
            $this->createClass(ArrayVariableType::class),
            $this->createClass(ScalarVariableType::class)
        );
    }
}
