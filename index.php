<?php

declare(strict_types=1);

use Amateri\PropertyReader\PropertyReader;
use Amateri\PropertyReader\PropertyWriter;

require_once __DIR__ . '/vendor/autoload.php';

$propertyReader = new PropertyReader();
$propertyWriter = new PropertyWriter();

class Test
{
    public int $propertyTest;

    /**
     * @var string
     */
    public $phpdocTest;

    /**
     * @var int[]
     */
    public array $array;

    /**
     * @var PropertyReader|null
     */
    public ?PropertyReader $class;

    /**
     * @var int[][]
     */
    public array $shitArray;

    /**
     * @var array<int, string>
     */
    public array $dictionary;

    /**
     * @var ?int|string|PropertyWriter|int[]|Test[][]
     */
    public $union;

}

$test = new Test();
$reflection = new \ReflectionObject($test);

foreach ($reflection->getProperties() as $property) {
    print "PARAMETER " . $property->getName() . "\n";
    $fromProperty = $propertyReader->readVariableTypeFromPropertyType($property);
    $fromPhpDoc = $propertyReader->readVariableTypeFromPhpDoc($property);
    printf("   from property: %s\n", $fromProperty ? $propertyWriter->variableTypeToString($fromProperty) : '-');
    printf("   from property: %s (php compatible)\n", $fromProperty ? $propertyWriter->variableTypeToString($fromProperty, true) : '-');
    printf("     from phpdoc: %s\n", $fromPhpDoc ? $propertyWriter->variableTypeToString($fromPhpDoc) : '-');
    printf("     from phpdoc: %s (php compatible)\n", $fromPhpDoc ? $propertyWriter->variableTypeToString($fromPhpDoc, true) : '-');
    print "\n";
}

