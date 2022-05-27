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
    public ?int $nullable;

    /**
     * @var int|null
     */
    public ?int $nullableSecondVariant;

    /**
     * @phpstan-ignore-next-line
     * @var ?int|null|null
     */
    public ?int $multipleNullable;

    /**
     * @var null
     */
    public $unresolvableNullable;
}
