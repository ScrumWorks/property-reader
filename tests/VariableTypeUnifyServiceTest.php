<?php

declare(strict_types = 1);

namespace Amateri\PropertyReader\Tests;

use Amateri\PropertyReader\VariableType\ArrayVariableType;
use Amateri\PropertyReader\VariableType\ClassVariableType;
use Amateri\PropertyReader\VariableType\MixedVariableType;
use Amateri\PropertyReader\VariableType\UnionVariableType;
use Amateri\PropertyReader\VariableType\VariableTypeInterface;
use Amateri\PropertyReader\VariableTypeUnifyService;
use PHPUnit\Framework\TestCase;

class VariableTypeUnifyServiceTest extends TestCase
{
    use VariableTypeCreatingTrait;

    private VariableTypeUnifyService $variableTypeUnifyService;

    public function setUp(): void
    {
        $this->variableTypeUnifyService = new VariableTypeUnifyService();
    }

    public function testSame(): void
    {
        // `same` is reflexive
        $this->assertTrue($this->same(null, null));

        // `same` is symmetric
        $this->assertFalse($this->same(null, $this->createInteger(true)));
        $this->assertFalse($this->same($this->createInteger(true),null));

        // nullable must have same values
        $this->assertFalse($this->same($this->createInteger(true), $this->createInteger(false)));

        // scalar values are same when have same types
        $this->assertTrue($this->same($this->createInteger(true), $this->createInteger(true)));
        $this->assertFalse($this->same($this->createInteger(true), $this->createString(true)));

        // array must have same key and item type
        $this->assertTrue(
            $this->same(
                new ArrayVariableType($this->createInteger(true), $this->createString(false), false),
                new ArrayVariableType($this->createInteger(true), $this->createString(false), false),
            )
        );
        $this->assertFalse(
            $this->same(
                new ArrayVariableType($this->createString(true), $this->createString(false), false),
                new ArrayVariableType($this->createInteger(true), $this->createString(false), false),
            )
        );
        $this->assertFalse(
            $this->same(
                new ArrayVariableType($this->createInteger(true), null, false),
                new ArrayVariableType($this->createInteger(true), $this->createString(false), false),
            )
        );

        // objects must have same type
        $this->assertTrue($this->same(new ClassVariableType(ArrayVariableType::class, false), new ClassVariableType(ArrayVariableType::class, false)));
        $this->assertFalse($this->same(new ClassVariableType(ArrayVariableType::class, false), new ClassVariableType(MixedVariableType::class, false)));

        // union types must be equivalent (order doesn't matters)
        $this->assertTrue(
            $this->same(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createString(false),
                    new ArrayVariableType($this->createString(false), null, false),
                ], true),
                new UnionVariableType([
                    new ArrayVariableType($this->createString(false), null, false),
                    $this->createString(false),
                    $this->createInteger(true),
                ], true)
            )
        );
        $this->assertTrue(
            $this->same(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createInteger(true),
                    $this->createInteger(false),
                ], true),
                new UnionVariableType([
                    $this->createInteger(false),
                    $this->createInteger(true),
                ], true)
            )
        );
        $this->assertFalse(
            $this->same(
                new UnionVariableType([
                    $this->createString(true),
                    $this->createInteger(true),
                    $this->createInteger(false),
                ], true),
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createInteger(false),
                ], true)
            )
        );
    }

    public function testUnify(): void
    {
        // `unify` is reflexive, only difference is that unify(null, null) === MixedVariableType
        $this->assertEquals(
            $this->createMixed(),
            $this->unify(null, null)
        );

        // `unify` is symmetric
        $this->assertEquals(
            $this->createInteger(true),
            $this->unify($this->createInteger(true), null)
        );
        $this->assertEquals(
            $this->createInteger(true),
            $this->unify(null, $this->createInteger(true))
        );

        // incompatible types raises exception
        $this->expectException(\Exception::class);
        $this->unify(
            $this->createMixed(),
            $this->createInteger(false)
        );
        $this->assertEquals(
            $this->createInteger(true),
            $this->unify(
                $this->createInteger(true),
                $this->createInteger(false)
            )
        );

        // mixed type
        $this->assertEquals(
            $this->createMixed(),
            $this->unify(
                $this->createMixed(),
                $this->createMixed()
            )
        );

        // scalar type
        $this->assertEquals(
            $this->createInteger(true),
            $this->unify(
                $this->createInteger(true),
                $this->createInteger(false)
            )
        );

        // array type
        // array + int[] = int[]
        $this->assertEquals(
            new ArrayVariableType($this->createInteger(false), null, false), // int[]
            $this->unify(
                new ArrayVariableType($this->createMixed(), null, false), // array
                new ArrayVariableType($this->createInteger(false), null, false), // int[]
            )
        );
        // array<string, string> + string[] causes exception
        $this->expectException(\Exception::class);
        $this->unify(
            new ArrayVariableType($this->createString(false), $this->createString(false), false), // array<string, string>
            new ArrayVariableType($this->createString(false), null, false), // string[]
        );

        // classes must have same class type
        $this->expectException(\Exception::class);
        $this->unify(
            new ClassVariableType(ArrayVariableType::class, true),
            new ClassVariableType(ClassVariableType::class, true),
        );

        // unions must have exactly same elements
        $this->assertEquals(
            new UnionVariableType([
                $this->createInteger(true),
                $this->createString(false),
            ], true),
            $this->unify(
                new UnionVariableType([
                    $this->createInteger(true),
                    $this->createString(false),
                ], true),
                new UnionVariableType([
                    $this->createString(false),
                    $this->createInteger(true),
                ], true)
            )
        );
        $this->expectException(\Exception::class);
        $this->unify(
            new UnionVariableType([
                $this->createInteger(true),
                $this->createString(false),
            ], true),
            new UnionVariableType([
                $this->createString(false),
                $this->createInteger(false),
            ], true)
        );
    }

    private function same(?VariableTypeInterface $a, ?VariableTypeInterface $b): bool
    {
        return $this->variableTypeUnifyService->same($a, $b);
    }

    private function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): VariableTypeInterface
    {
        return $this->variableTypeUnifyService->unify($a, $b);
    }
}
