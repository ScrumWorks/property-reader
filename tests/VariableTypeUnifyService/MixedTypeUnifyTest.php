<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\VariableTypeUnifyService;

class MixedTypeUnifyTest extends AbstractUnifyTest
{
    public function testMixed(): void
    {
        $this->assertEquals($this->createMixed(), $this->unify($this->createMixed(), $this->createMixed()));
    }
}
