# PHP Property Reader

[![Build Status](https://github.com/ScrumWorks/property-reader/workflows/build/badge.svg?branch=master)](https://github.com/ScrumWorks/property-reader)

## Installation
```
composer require scrumworks/property-reader
```

## Documentation

Class property can be translated to these variants:

### null

`null` is returned for properties without any information.

It's generally `mixed` type, but it's acting differently f.e. in array types.

```php
public $var;
```
### MixedVariableType

It's returned for variables with `mixed` directly information.

```php
/**
 * @var mixed
 */
public $var;
```

### ScalarVariableType

Supports this basic scalar types:
  - `int`, `integer`
  - `float`
  - `bool`, `boolean`
  - `string`

```php
/**
 * @var integer
 */
public int $var;
```
### ArrayVariableType

Arrays are considered to be seqential array or hashmap.

Arrays are translated in this way: (we use definition `array<key, type>`)
- generic `array` has type `array<null, null>`
- seqential `int[]` has type `array<null, int>`
- hashmap `array<string, string>` has type `array<string, string>`

In general - `null` in `key` is proposing seqential array, other types (only `integer` and  `string` are supported) are
propose hashmap. Only difference is `key == value == null`, then it's
generic array.

**Warning** - `mixed[]` has different type than `array`

We also support nested arrays like `int[][]` or `array<string, string>[]`

```php
/**
 * @var int[]
 */
public array $var;
```

### ClassVariableType

```php
/**
 * @var SomeClass
 */
public SomeClass $var;
```

### UnionVariableType

```php
/**
 * @var int|string
 */
public $var;
```

### Nullablity of types

Every type can by set to be nullable in this ways:
- `?int`
- `int|null`

Types `null` and `MixedVariableType` are nullable by default.

**Warning** - `?int|string` isn't `(?int)|string` but `int|string|null`

## Example usage

```php
<?php

use ScrumWorks\PropertyReader\PropertyTypeReader;
use ScrumWorks\PropertyReader\VariableTypeWriter;
use ScrumWorks\PropertyReader\VariableTypeUnifyService;

class Example
{
    public $untyped;

    public int $integer;

    /**
     * @var ?string
     */
    public $nullableString;

    /**
     * @var array<string, string[]>
     */
    public array $hashmap;

    public VariableTypeWriter $class;

    /**
     * @var int|int[]|null
     */
    public $union;
}

$reflection = new ReflectionClass(Example::class);

$variableTypeUnifyService = new VariableTypeUnifyService();
$propertyTypeReader = new PropertyTypeReader($variableTypeUnifyService);
$variableTypeWriter = new VariableTypeWriter();

foreach ($reflection->getProperties() as $propertyReflection) {
    $variableType = $propertyTypeReader->readUnifiedVariableType($propertyReflection);
    printf(
        "%s: %s\n",
        $propertyReflection->getName(),
        $variableTypeWriter->variableTypeToString($variableType)
    );
}
```
will result to
```ini
untyped: mixed
integer: int
nullableString: ?string
hashmap: array<string, string[]>
class: ScrumWorks\PropertyReader\VariableTypeWriter
union: ?int|int[]
```

### `VariableType` API

```php
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

// load object...

/** @var ArrayVariableType $hashmapType */
$hashmapType = $propertyTypeReader->readUnifiedVariableType($reflection->getProperty('hashmap'));
assert($hashmapType->isNullable() === false);
assert($hashmapType->getKeyType() instanceof ScalarVariableType);
assert($hashmapType->getKeyType()->getType() === ScalarVariableType::TYPE_STRING);
```

## Testing
You can run the tests with:

```
composer run-script test
```

## Contribution Guide
Feel free to open an Issue or add a Pull request.

## Credits
People:
- [Tomas Lang](https://github.com/detrandix)
- [Adam Lutka](https://github.com/AdamLutka)
