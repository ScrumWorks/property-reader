<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

final class UnionPropertyTypeTestClass
{
    /**
     * @var int|string
     */
    public $phpDocUnion;

    /**
     * @phpstan-ignore-next-line
     * @var ?bool|float
     */
    public $phpDocUnionNullable;

    public int|string $propertyTypeUnion;

    public bool|UnionPropertyTypeTestClass|null $propertyTypeUnionNullable;
}
