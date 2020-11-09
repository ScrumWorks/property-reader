<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use Exception;

/**
 * @property-read string $class FQN class name
 */
final class ClassVariableType extends AbstractVariableType
{
    protected string $class;

    public function __construct(string $class, bool $nullable)
    {
        $this->class = $class;

        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'CLASS[' . $this->class . ']';
    }

    protected function getClass(): string
    {
        return $this->class;
    }

    protected function validate(): void
    {
        if (! \class_exists($this->class)) {
            throw new Exception("Unknown class '{$this->class}' given");
        }
    }
}
