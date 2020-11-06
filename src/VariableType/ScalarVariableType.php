<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

/**
 * @property-read string $type
 */
final class ScalarVariableType extends AbstractVariableType
{
    const TYPE_INTEGER = 'INTEGER';
    const TYPE_FLOAT = 'FLOAT';
    const TYPE_BOOLEAN = 'BOOLEAN';
    const TYPE_STRING = 'STRING';

    protected string $type;

    public function __construct(string $type, bool $nullable)
    {
        $this->type = $type;
        parent::__construct($nullable);
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function validate(): void
    {
        if (!in_array($this->type, [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_BOOLEAN, self::TYPE_STRING])) {
            throw new \Exception("Unknown '{$this->type}' scalar type given");
        }
    }

    public function __toString(): string
    {
        return 'SCALAR[' . $this->type . ']';
    }
}
