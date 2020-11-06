<?php

declare(strict_types=1);

use Amateri\PropertyReader\PropertyReader;

require_once __DIR__ . '/vendor/autoload.php';

$propertyReader = new PropertyReader();

class Test
{
    public int $test;

    /**
     * @var boolean
     */
    public array $test2;
}

$test = new Test();
$reflection = new \ReflectionObject($test);

foreach ($reflection->getProperties() as $property) {
    //var_dump($propertyReader->readVariableTypeFromPropertyType($property));
    var_dump($propertyReader->readVariableTypeFromPhpDoc($property));
}

