<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableTypeUnifyService;

class ScalarTypeUnifyTest extends AbstractUnifyTest
{
    public function testMixed(): void
    {
        $this->assertEquals(
            $this->createInteger(true),
            $this->unify(
                $this->createInteger(true),
                $this->createInteger(true)
            )
        );
    }
}
