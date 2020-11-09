<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\VariableType;

use Exception;

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

    public function getType(): string
    {
        return $this->type;
    }

    public function equals(VariableTypeInterface $object): bool
    {
        if (! parent::equals($object)) {
            return false;
        }
        /** @var ScalarVariableType $object */
        return $this->getType() === $object->getType();
    }

    protected function validate(): void
    {
        if (! \in_array($this->type, [self::TYPE_INTEGER, self::TYPE_FLOAT, self::TYPE_BOOLEAN, self::TYPE_STRING])) {
            throw new Exception("Unknown '{$this->type}' scalar type given");
        }
    }
}
