# php-flatten

[![Latest Version](https://img.shields.io/github/release/AlaaSarhan/php-flatten.svg?style=flat-square)](https://github.com/AlaaSarhan/php-flatten/releases)
[![Software License](https://img.shields.io/badge/license-LGPL_v3.0-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://travis-ci.org/AlaaSarhan/php-flatten.svg?branch=master)](https://travis-ci.org/AlaaSarhan/php-flatten)
[![Total Downloads](https://img.shields.io/packagist/dt/sarhan/php-flatten.svg?style=flat-square)](https://packagist.org/packages/sarhan/php-flatten)

A utility function to mainly flatten multidimensional-arrays and traversables into a one-dimensional array, preserving keys
and joining them with a customizable separator to from fully-qualified keys in the final array.

## Installation

```bash
  composer require sarhan/php-flatten
```

## Usage

**Example 1**

```php
use Sarhan\Flatten\Flatten;

$multiArray = [
    'say' => 'what',
    'hi' => [ 'de' => 'Hallo', 'es' => 'Hola' ]
];

/*
Flatten::__construct(
    string $separator = '.',
    string $prefix = '',
    int $flags = 0
)
*/
$flatten = new Flatten();

// Flatten::flattenToArray is provided for convinience. It internally
// calls Flatten::flatten and converts it's output, which is a 1-dimensional
// iterator, into a 1-dimensional array.
$flattened = $flatten->flattenToArray($multiArray);

// Flatten::unflattenToArray is provided for convinience. It internally
// calls Flatten::unflatten and converts it's output, which is a recursive
// generator structure, into a multi-dimensional array.
$unflattened = $flatten->unflattenToArray($flattened);

/*
assert($flattened == [
    'say' => what
    'hi.de' => Hallo
    'hi.es' => Hola
]);

assert($unflattened == $multiArray);
*/
```

**Example 2**

Custom Separator and initial prefix
```php
use Sarhan\Flatten\Flatten;

$allowAccess = [
    'root' => false,
    'var' => [ 'log' => ['nginx' => true, 'apt' => false], 'www' => true ],
];

$flatten = new Flatten(
  '/',  // separator
  '/'   // prefix
);

$flattened = $flatten->flattenToArray($allowAccess);

$unflattened = $flatten->unflattenToArray($flattened);

/*
assert($flatten == [
    '/root' => false,
    '/var/log/nginx' => true,
    '/var/log/apt' => false,
    '/var/www' => true
]);

assert($unflattened == $allowAccess);
*/
```

**Example 3**

Notice that the prefix will not be separated in FQkeys. If it should be separated, separator must be appeneded to the prefix string.

```php
use Sarhan\Flatten\Flatten;

$api = [
    'category' => [ 'health' => 321, 'sport' => 769, 'fashion' => 888 ],
    'tag' => [ 'soccer' => 7124, 'tennis' => [ 'singles' => 9833, 'doubles' => 27127 ] ],
];

$flatten = new Flatten('/', 'https://api.dummyhost.domain/');

$flattened = $flatten->flattenToArray($api);

$unflattened = $flatten->unflattenToArray($flattened);

/*
assert($flattened == [
    'https://api.dummyhost.domain/category/health' => 321,
    'https://api.dummyhost.domain/category/sport' => 769,
    'https://api.dummyhost.domain/category/fashion' => 888,
    'https://api.dummyhost.domain/tag/soccer' => 7124,
    'https://api.dummyhost.domain/tag/tennis/singles' => 9833,
    'https://api.dummyhost.domain/tag/tennis/doubles' => 27127
]);

assert($unflattened == $api);
*/
```

**Example 4**

Numeric keys are treated as associative keys.

**Note:** This behavior can be changed using flags. See [FLAG_NUMERIC_NOT_FLATTENED](#numeric_not_flattened)

```php
use Sarhan\Flatten\Flatten;

$nutrition = [
    'nutrition',
    'fruits' => [ 'oranges', 'apple', 'banana' ],
    'veggies' => ['lettuce', 'broccoli'],
];

$flatten = new Flatten('-');

$flattened = $flatten->flattenToArray($nutrition);

$unflattened = $flatten->unflattenToArray($flattened);

/*
assert($flattened == [
    '0' => 'nutrition',
    'fruits-0' => 'oranges',
    'fruits-1' => 'apple',
    'fruits-2' => 'banana',
    'veggies-0' => 'lettuce',
    'veggies-1' => 'broccoli'
]);

assert($unflattened == $nutrition);
*/
```

### Flags

<a name="numeric_not_flattened"></a>**FLAG_NUMERIC_NOT_FLATTENED**

Turns off flattening values with numeric (integer) keys.

Those values will be wrapped in an array (preserving their keys) and associated to the parent FQK.

```php
use Sarhan\Flatten\Flatten;

$examples = [
    'templates' => [
      ['lang' => 'js', 'template' => "console.log('%s');"],
      ['lang' => 'php', 'template' => 'echo "%s";']
    ],
    'values' => [3 => 'hello world', 5 => 'what is your name?']
];

$flatten = new Flatten(
  '.',
  'examples.',
  Flatten::FLAG_NUMERIC_NOT_FLATTENED
);

$flattened = $flatten->flattenToArray($examples);

$unflattened = $flatten->unflattenToArray($flattened);

/*
assert($flattened == [
    'examples.templates' => [
        [
            'lang' => 'js',
            'template' => 'console.log(\'%s\')';
        ],
        [
            'lang' => 'php',
            'template' => 'echo "%s"'
        ]
    ],
    'examples.values' => [
        3 => 'hello world',
        5 => 'what is your name?'
    ]
]);

assert($unflattened == $examples);
*/
```
Top level numeric (integer) keys will also be returned into an array assigned to the passed prefix.

```php
use Sarhan\Flatten\Flatten;

$seats = [
  'A1',
  'A2',
  'B1',
  'B2',
  '_reserved' => ['A1', 'B1'],
  '_blocked' => ['B2']
];

$flatten = new Flatten(
  '_',
  'seats',
  Flatten::FLAG_NUMERIC_NOT_FLATTENED
);

$flattened = $flatten->flattenToArray($seats);

$unflattened = $flatten->unflattenToArray($flattened);

/*
assert($flattened == [
    'seats' => ['A1', 'A2', 'B1', 'B2'],
    'seats_reserved' => ['A1', 'B1'],
    'seats_blocked' => ['B2']
]);

assert($unflattened == $seats);
*/
```
