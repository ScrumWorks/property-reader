<?php

declare(strict_types = 1);

namespace ScrumWorks\PropertyReader\Tests;

use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ClassVariableType;
use ScrumWorks\PropertyReader\VariableType\MixedVariableType;
use ScrumWorks\PropertyReader\VariableType\UnionVariableType;
use ScrumWorks\PropertyReader\VariableType\VariableTypeInterface;
use ScrumWorks\PropertyReader\VariableTypeUnifyService;
use PHPUnit\Framework\TestCase;

class VariableTypeUnifyServiceTest extends TestCase
{
    use VariableTypeCreatingTrait;

    private VariableTypeUnifyService $variableTypeUnifyService;

    public function setUp(): void
    {
        $this->variableTypeUnifyService = new VariableTypeUnifyService();
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

    private function unify(?VariableTypeInterface $a, ?VariableTypeInterface $b): VariableTypeInterface
    {
        return $this->variableTypeUnifyService->unify($a, $b);
    }
}
