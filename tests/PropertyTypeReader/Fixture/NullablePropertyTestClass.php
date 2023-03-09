<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

final class NullablePropertyTestClass
{
    /**
     * @var int
     */
    public int $notNullable;

    /**
     * @var ?int
     */
    public ?int $nullable = null;

    /**
     * @var int|null
     */
    public ?int $nullableSecondVariant = null;

    /**
     * @var ?int|null|null
     */
    public ?int $multipleNullable = null;

    /**
     * @var null
     */
    public $unresolvableNullable;
}
