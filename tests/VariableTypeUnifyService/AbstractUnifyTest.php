<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests\VariableTypeUnifyService;
use PHPUnit\Framework\TestCase;
use ScrumWorks\PropertyReader\Tests\VariableTypeCreatingTrait;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;
use ScrumWorks\PropertyReader\VariableTypeUnifyService;

abstract class AbstractUnifyTest extends TestCase
{
    use VariableTypeCreatingTrait;

    private VariableTypeUnifyService $variableTypeUnifyService;

    public function setUp(): void
    {
        $this->variableTypeUnifyService = new VariableTypeUnifyService();
    }

    protected function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): ?VariableTypeInterface
    {
        return $this->variableTypeUnifyService->unify($a, $b);
    }
}
