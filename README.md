# php-flatten

A utility function to mainly flatten multidimensional-arrays and traversables into a one-dimensional array, preserving keys
and joining them with a customizable separator to from fully-qualified keys in the final array.

## Installation

```
  composer require sarhan/php-flatten
```

## Examples

```php
use Sarhan\Flatten;

$multiArray = [
    'say' => 'what',
    'hi' => [ 'de' => 'Hallo', 'es' => 'Hola' ]
];

$array = Flatten::flatten($multiArray);

/* print_r($array) gives:

  Array
  (
      [say] => what
      [hi.de] => Hallo
      [hi.es] => Hola
  )
*/
```

Custom Separator and initial prefix
```php
use Sarhan\Flatten;

$allowAccess = [
    'root' => false,
    'var' => [ 'log' => ['nginx' => true, 'apt' => false], 'www' => true ],
];

$allowAccess = Flatten::flatten($allowAccess, '/', '/');

/* var_dump($array) gives:

  array(4) {
    '/root' =>
    bool(false)
    '/var/log/nginx' =>
    bool(true)
    '/var/log/apt' =>
    bool(false)
    '/var/www' =>
    bool(true)
  }
*/
```

Notice that the prefix will not be separated in FQkeys. If it should be separated, separator must be appeneded to the prefix string.
```php
use Sarhan\Flatten;

$api = [
    'category' => [ 'health' => 321, 'sport' => 769, 'fashion' => 888 ],
    'tag' => [ 'soccer' => 7124, 'tennis' => [ 'singles' => 9833, 'doubles' => 27127 ] ],
];

$uris = Flatten::flatten($api, '/', 'https://api.dummyhost.domain/');

/* print_r($uris) gives:

  Array
  (
      [https://api.dummyhost.domain/category/health] => 321
      [https://api.dummyhost.domain/category/sport] => 769
      [https://api.dummyhost.domain/category/fashion] => 888
      [https://api.dummyhost.domain/tag/soccer] => 7124
      [https://api.dummyhost.domain/tag/tennis/singles] => 9833
      [https://api.dummyhost.domain/tag/tennis/doubles] => 27127
  )

*/
```