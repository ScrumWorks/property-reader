<?php

declare(strict_types=1);

namespace Amateri\PropertyReader\VariableType;

use Exception;

/**
 * @property-read string $type
 */
final class ScalarVariableType extends AbstractVariableType
{
    public const TYPE_INTEGER = 'INTEGER';

    public const TYPE_FLOAT = 'FLOAT';

    public const TYPE_BOOLEAN = 'BOOLEAN';

    public const TYPE_STRING = 'STRING';

    protected string $type;

    public function __construct(string $type, bool $nullable)
    {
        $this->type = $type;

        parent::__construct($nullable);
    }

    public function __toString(): string
    {
        return 'SCALAR[' . $this->type . ']';
    }

    protected function getType(): string
    {
        return $this->type;
    }

    protected function validate(): void
    {
        if (! \in_array($this->type, [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_BOOLEAN, self::TYPE_STRING])) {
            throw new Exception("Unknown '{$this->type}' scalar type given");
        }
    }
}
