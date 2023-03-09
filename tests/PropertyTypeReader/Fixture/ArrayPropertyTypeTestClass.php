<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

final class ArrayPropertyTypeTestClass
{
    /** @var int[] */
    public array $array;

    /** @var array<int> */
    public array $arrayAlternative;

    /** @var array */
    public array $genericArray;

    /** @var int[][] */
    public array $nestedArray;

    /** @var array<array<int>> */
    public array $nestedArrayAlternative;

    /** @var array<string, string> */
    public array $hashmap;

    /** @var array<string, array<int, string>> */
    public array $nestedHashmap;

    /** @var array<int|string, ?int[][]> */
    public array $complicatedArray;
}
