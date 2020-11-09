<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader;

use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;

interface VariableTypeUnifyServiceInterface
{
    public function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): ?VariableTypeInterface;
}
