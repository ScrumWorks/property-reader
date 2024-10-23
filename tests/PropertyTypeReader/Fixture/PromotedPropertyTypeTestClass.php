<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

final class PromotedPropertyTypeTestClass
{
    /**
     * @param int $int
     * @param bool[] $arr
     * @param array<string, float> $hashmap
     */
    public function __construct(
        private readonly ?string $str,
        private $int,
        private readonly array $arr,
        private $hashmap,
    ) {
    }
}
