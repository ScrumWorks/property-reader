<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

final class GeneralPropertyTestClass
{
    public $property;

    /**
     * @var int
     */
    public $block;

    /** @var int */
    public $inlineBlock;

    /**
     * @var (int|null)[]
     */
    public $withBraces;
}
