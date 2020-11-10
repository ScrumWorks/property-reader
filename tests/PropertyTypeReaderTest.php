<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests;

use ScrumWorks\PropertyReader\PropertyTypeReader;
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;
use ScrumWorks\PropertyReader\VariableTypeUnifyServiceInterface;
use PHPUnit\Framework\TestCase;

// We must use normal class instead of anonymous class, because
// anonymous classes are not support by Nette\Utils\Reflextion
class TestClass
{
    /**
     * @var PropertyTypeReaderTest
     */
    public PropertyTypeReaderTest $class;
}

class PropertyTypeReaderTest extends TestCase
{
    use VariableTypeCreatingTrait;

    private PropertyTypeReader $propertyTypeReader;

    public function setUp(): void
    {
        $variableTypeUnifyServiceMock = new class implements VariableTypeUnifyServiceInterface {
            function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): ?VariableTypeInterface
            {
                return null;
            }
        };
        $this->propertyTypeReader = new PropertyTypeReader($variableTypeUnifyServiceMock);
    }

    public function testEmptyDefinition(): void
    {
        $class = new class {
            public $property;
        };
        $reflection = new \ReflectionObject($class);

        $property = $reflection->getProperty('property');
        $this->assertEquals(
            null,
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            null,
            $this->readFromPhpDoc($property)
        );
    }

    public function testAlternativePhpDocDefinitions(): void
    {
        $class = new class {
            /**
             * @var int
             */
            public $block;

            /** @var int */
            public $inlineBlock;
        };

        $reflection = new \ReflectionObject($class);

        // block
        $property = $reflection->getProperty('block');
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            $this->readFromPhpDoc($property)
        );

        // inline block
        $property = $reflection->getProperty('inlineBlock');
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            $this->readFromPhpDoc($property)
        );
    }

    public function testNullable(): void
    {
        $class = new class {
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
        };
        $reflection = new \ReflectionObject($class);

        // not nullable
        $property = $reflection->getProperty('notNullable');
        $this->assertEquals(
            false,
            $this->readFromPropertyType($property)->isNullable()
        );
        $this->assertEquals(
            false,
            $this->readFromPhpDoc($property)->isNullable()
        );

        // ?var syntax
        $property = $reflection->getProperty('nullable');
        $this->assertEquals(
            true,
            $this->readFromPropertyType($property)->isNullable()
        );
        $this->assertEquals(
            true,
            $this->readFromPhpDoc($property)->isNullable()
        );

        // var|null syntax
        $property = $reflection->getProperty('nullableSecondVariant');
        $this->assertEquals(
            true,
            $this->readFromPhpDoc($property)->isNullable()
        );

        // multiple nullable are ignored
        $property = $reflection->getProperty('multipleNullable');
        $this->assertEquals(
            true,
            $this->readFromPhpDoc($property)->isNullable()
        );

        // property with only nullable definition
        $property = $reflection->getProperty('unresolvableNullable');
        $this->expectException(\Exception::class);
        $this->expectErrorMessage("Unresolvable definition 'null'");
        $this->readFromPhpDoc($property);
    }

    public function testNotAllowedBraces(): void
    {
        $class = new class {
            /**
             * @var (int|null)[]
             */
            public $withBraces;
        };
        $reflection = new \ReflectionObject($class);

        $property = $reflection->getProperty('withBraces');
        $this->expectException(\Exception::class);
        $this->expectErrorMessage('Braces are not support in type');
        $this->readFromPhpDoc($property);
    }

    public function testMixedType(): void
    {
        $class = new class {
            /**
             * @var mixed
             */
            public $mixed;
        };
        $reflection = new \ReflectionObject($class);

        // mixed is always nullable (warning - for `public $var` it's return null!)
        $property = $reflection->getProperty('mixed');
        $this->assertEquals(
            null,
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new MixedVariableType(),
            $this->readFromPhpDoc($property)
        );
    }

    public function testScalarType(): void
    {
        $class = new class {
            /** @var int */
            public int $integer;

            /** @var integer */
            public int $integerAlternative;

            /** @var float */
            public float $float;

            /** @var bool */
            public bool $boolean;

            /** @var boolean */
            public bool $booleanAlternative;

            /** @var string */
            public string $string;
        };
        $reflection = new \ReflectionObject($class);

        // integer type
        $property = $reflection->getProperty('integer');
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            $this->readFromPhpDoc($property)
        );
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_INTEGER, false),
            $this->readFromPhpDoc($reflection->getProperty('integerAlternative'))
        );

        // float type
        $property = $reflection->getProperty('float');
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_FLOAT, false),
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_FLOAT, false),
            $this->readFromPhpDoc($property)
        );

        // boolean type
        $property = $reflection->getProperty('boolean');
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, false),
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, false),
            $this->readFromPhpDoc($property)
        );
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_BOOLEAN, false),
            $this->readFromPhpDoc($reflection->getProperty('booleanAlternative'))
        );

        // string type
        $property = $reflection->getProperty('string');
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new ScalarVariableType(ScalarVariableType::TYPE_STRING, false),
            $this->readFromPhpDoc($property)
        );
    }

    public function testArrayType(): void
    {
        $class = new class {
            /** @var int[] */
            public array $array;

            /** @var array<int> */
            public array $arrayAlternative;

            /** @var array */
            public array $generalArray;

            /** @var int[][] */
            public array $nestedArray;

            /** @var array<string, string> */
            public array $hashmap;

            /** @var array<string, array<int, string>> */
            public array $nestedHashmap;

            /** @var array<int|string, ?int[][]> */
            public array $complicatedArray;
        };
        $reflection = new \ReflectionObject($class);

        // normal array definition (type[])
        $property = $reflection->getProperty('array');
        $this->assertEquals(
            new ArrayVariableType(
                null,
                null,
                false
            ),
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new ArrayVariableType(
                null,
                $this->createInteger(false),
                false
            ),
            $this->readFromPhpDoc($property)
        );

        // array alternative syntax (array<type>)
        $property = $reflection->getProperty('arrayAlternative');
        $this->assertEquals(
            new ArrayVariableType(
                null,
                $this->createInteger(false),
                false
            ),
            $this->readFromPhpDoc($property)
        );

        // general array syntax (array)
        $property = $reflection->getProperty('generalArray');
        $this->assertEquals(
            new ArrayVariableType(
                null,
                null,
                false
            ),
            $this->readFromPhpDoc($property)
        );

        // nested array
        $property = $reflection->getProperty('nestedArray');
        $this->assertEquals(
            new ArrayVariableType(
                null,
                new ArrayVariableType(
                    null,
                    $this->createInteger(false),
                    false
                ),
                false
            ),
            $this->readFromPhpDoc($property)
        );

        // hashmap
        $property = $reflection->getProperty('hashmap');
        $this->assertEquals(
            new ArrayVariableType(
                $this->createString(false),
                $this->createString(false),
                false
            ),
            $this->readFromPhpDoc($property)
        );

        // nested hashmap
        $property = $reflection->getProperty('nestedHashmap');
        $this->assertEquals(
            new ArrayVariableType(
                $this->createString(false),
                new ArrayVariableType(
                    $this->createInteger(false),
                    $this->createString(false),
                    false
                ),
                false
            ),
            $this->readFromPhpDoc($property)
        );

        // complicated array definition
        $property = $reflection->getProperty('complicatedArray');
        $this->assertEquals(
            new ArrayVariableType(
                new UnionVariableType([
                    $this->createInteger(false),
                    $this->createString(false),
                ], false),
                new ArrayVariableType(
                    null,
                    new ArrayVariableType(
                        null,
                        $this->createInteger(false),
                        false
                    ),
                    true
                ),
                false
            ),
            $this->readFromPhpDoc($property)
        );
    }

    public function testClassType(): void
    {
        $class = new TestClass();
        $reflection = new \ReflectionObject($class);

        $property = $reflection->getProperty('class');
        $this->assertEquals(
            new ClassVariableType(PropertyTypeReaderTest::class, false),
            $this->readFromPropertyType($property)
        );
        $this->assertEquals(
            new ClassVariableType(PropertyTypeReaderTest::class, false),
            $this->readFromPhpDoc($property)
        );
    }

    public function testUnionType(): void
    {
        $class = new class {
            /**
             * @var int|string
             */
            public $union;

            /**
             * @phpstan-ignore-next-line
             * @var ?bool|float
             */
            public $unionNullable;
        };
        $reflection = new \ReflectionObject($class);

        $property = $reflection->getProperty('union');
        $this->assertEquals(
            new UnionVariableType([
                $this->createInteger(false),
                $this->createString(false),
            ], false),
            $this->readFromPhpDoc($property)
        );

        $property = $reflection->getProperty('unionNullable');
        $this->assertEquals(
            new UnionVariableType([
                $this->createBoolean(false),
                $this->createFloat(false),
            ], true),
            $this->readFromPhpDoc($property)
        );
    }

    private function readFromPropertyType(\ReflectionProperty $property): ?VariableTypeInterface
    {
        return $this->propertyTypeReader->readVariableTypeFromPropertyType($property);
    }

    private function readFromPhpDoc(\ReflectionProperty $property): ?VariableTypeInterface
    {
        return $this->propertyTypeReader->readVariableTypeFromPhpDoc($property);
    }
}
