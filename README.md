# PHP Property Reader

[![Build Status](https://github.com/ScrumWorks/property-reader/workflows/build/badge.svg?branch=master)](https://github.com/ScrumWorks/property-reader)

## Installation
```
composer require scrumworks/property-reader
```

## Example

```php
<?php

use ScrumWorks\PropertyReader\PropertyReader;
use ScrumWorks\PropertyReader\PropertyWriter;
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

    public PropertyWriter $propertyWriter;

    /**
     * @var int|int[]|null
     */
    public $union;
}

$reflection = new ReflectionClass(Example::class);

$variableTypeUnifyService = new VariableTypeUnifyService();
$propertyReader = new PropertyReader($variableTypeUnifyService);
$propertyWriter = new PropertyWriter();

foreach ($reflection->getProperties() as $propertyReflection) {
    $variableType = $propertyReader->readUnifiedVariableType($propertyReflection);
    printf(
        "%s: %s\n",
        $propertyReflection->getName(),
        $propertyWriter->variableTypeToString($variableType)
    );
}
```
will result to
```ini
untyped: mixed
integer: int
nullableString: ?string
hashmap: array<string, string[]>
propertyWriter: Amateri\PropertyReader\PropertyWriter
union: ?int|int[]
```

### `VariableType` API

```php
use ScrumWorks\PropertyReader\VariableType\ArrayVariableType;
use ScrumWorks\PropertyReader\VariableType\ScalarVariableType;

// load object...

/** @var ArrayVariableType $hashmapType */
$hashmapType = $propertyReader->readUnifiedVariableType($reflection->getProperty('hashmap'));
assert($hashmapType->nullable === false);
assert($hashmapType->keyType instanceofScalarVariableType);
assert($hashmapType->keyType->type === ScalarVariableType::TYPE_STRING)
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
