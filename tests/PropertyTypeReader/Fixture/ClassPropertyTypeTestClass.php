<?php

declare(strict_types=1);

namespace ScrumWorks\PropertyReader\Tests\PropertyTypeReader\Fixture;

use ScrumWorks\PropertyReader\PropertyTypeReader;

final class ClassPropertyTypeTestClass
{
    /**
     * @var PropertyTypeReader
     */
    public PropertyTypeReader $class;

    /**
     * @var \DateTimeInterface
     */
    public \DateTimeInterface $interface;

    /**
     * @var SomeNotExistsClass
     */
    public $notExistsClass;
}
