<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

final class ScalarPropertyTypeTestClass
{
    /** @var int */
    public int $integer;

    /** @var integer */
    public int $integerAlternative;

    /** @var positive-int */
    public int $positiveInteger;

    /** @var negative-int */
    public int $negativeInteger;

    /** @var non-positive-int */
    public int $nonPositiveInteger;

    /** @var non-negative-int */
    public int $nonNegativeInteger;

    /** @var int<-5, 20> */
    public int $rangeInteger;

    /** @var float */
    public float $float;

    /** @var bool */
    public bool $boolean;

    /** @var boolean */
    public bool $booleanAlternative;

    /** @var string */
    public string $string;
}
