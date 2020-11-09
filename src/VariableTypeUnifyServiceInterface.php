<?php

declare(strict_types=1);

namespace Amateri\PropertyReader;

use Amateri\PropertyReader\VariableType\VariableTypeInterface;

interface VariableTypeUnifyServiceInterface
{
    public function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): ?VariableTypeInterface;
}
