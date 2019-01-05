<?php

namespace Sarhan\Flatten\Test;

use PHPUnit\Framework\TestCase;
use Sarhan\Flatten\Flatten;
use Sarhan\Flatten\Util\TraversableToArray;

class FlattenTest extends TestCase
{
    public function scalarProvider()
    {
        return [
            [ null, ['' => null] ],
            [ '', ['' => ''] ],
            [ 0, ['' => 0] ],
            [ 3.14, ['' => 3.14] ],
            [ 'test', ['' => 'test'] ],
            [ false, ['' => false] ]
        ];
    }

    /**
     * @covers Flatten::flatten
     * @dataProvider scalarProvider
     */
    public function testFlattenScalar($input, $expectedOutput)
    {
        $output = $this->flattenToArray($input);

        $this->assertEquals($expectedOutput, $output);
    }

    public function scalarSeparatorPrefixProvider()
    {
        return [
            [ null, '-', ':', [':' => null] ],
            [ '', '', '/', ['/' => ''] ],
            [ 0, '.', 'global', ['global' => 0] ],
            [ 3.14, '', 'local', ['local' => 3.14] ],
            [ 'test', 'sep', '', ['' => 'test'] ],
            [ false, '', '_', ['_' => false] ]
        ];
    }

    /**
     * @covers Flatten::flatten
     * @dataProvider scalarSeparatorPrefixProvider
     */
    public function testFlattenScalarWithSeparatorAndPrefix($input, $separator, $prefix, $expectedOutput)
    {
        $output = $this->flattenToArray($input, $separator, $prefix);

        $this->assertEquals($expectedOutput, $output);
    }

    public function arraysProvider()
    {
        return [
            [ [ ], [ ] ],
            [ [ 0 ], [ '0' => 0 ] ],
            [ [ 1, 2 ], [ '0' => 1, '1' => 2 ] ],
            [
                [ 1, 2, [ 3, 4 ] ],
                [ '0' => 1, '1' => 2, '2.0' => 3, '2.1' => 4 ]
            ],
            [
                [ 'a' => 1, 2, 'b' => [ 3, 'c' => 4 ] ],
                [ 'a' => 1, '0' => 2, 'b.0' => 3, 'b.c' => 4 ]
            ],
            [
                [ 'a' => 1, 'b' => 2, 'c' => [ 'd' => [ 3, 4 ], 'e' => [ 'f' => 5, 'g' => 6 ] ] ],
                [ 'a' => 1, 'b' => 2, 'c.d.0' => 3, 'c.d.1' => 4, 'c.e.f' => 5, 'c.e.g' => 6 ]
            ]
        ];
    }

    /**
     * @covers Flatten::flatten
     * @dataProvider arraysProvider
     */
    public function testFlattenArrays($input, $expectedOutput)
    {
        $output = $this->flattenToArray($input);

        $this->assertEquals($expectedOutput, $output);
    }

    public function traversablesProvider()
    {
        return [
            [ new \ArrayIterator([ ]), [ ] ],
            [ new \ArrayIterator([ 0 ]), [ '0' => 0 ] ],
            [ new \ArrayIterator([ 1, 2 ]), [ '0' => 1, '1' => 2 ] ],
            [
                new \ArrayIterator([ 1, 2, [ 3, 4 ] ]),
                [ '0' => 1, '1' => 2, '2.0' => 3, '2.1' => 4 ]
            ],
            [
                new \ArrayIterator([ 'a' => 1, 2, 'b' => new \ArrayIterator([ 3, 'c' => 4 ]) ]),
                [ 'a' => 1, '0' => 2, 'b.0' => 3, 'b.c' => 4 ]
            ],
            [
                new \ArrayIterator([
                    'a' => 1,
                    'b' => 2,
                    'c' => [ 'd' => [ 3, 4 ], 'e' => new \ArrayIterator([ 'f' => 5, 'g' => 6 ]) ]
                ]),
                [ 'a' => 1, 'b' => 2, 'c.d.0' => 3, 'c.d.1' => 4, 'c.e.f' => 5, 'c.e.g' => 6 ]
            ]
        ];
    }

    /**
     * @covers Flatten::flatten
     * @dataProvider traversablesProvider
     */
    public function testFlattenTraversable($input, $expectedOutput)
    {
        $output = $this->flattenToArray($input);
        $this->assertEquals($expectedOutput, $output);
    }

    public function traversablesSeparatorPrefixProvider()
    {
        return [
            [ new \ArrayIterator([ ]), '-', 'global-', [ ] ],
            [ new \ArrayIterator([ 0 ]), '-', 'global-', [ 'global-0' => 0 ] ],
            [ new \ArrayIterator([ 1, 2 ]), '-', 'global-', [ 'global-0' => 1, 'global-1' => 2 ] ],
            [
                new \ArrayIterator([ 1, 2, [ 3, 4 ] ]),
                '-',
                'global-',
                [ 'global-0' => 1, 'global-1' => 2, 'global-2-0' => 3, 'global-2-1' => 4 ]
            ],
            [
                new \ArrayIterator([ 'a' => 1, 2, 'b' => new \ArrayIterator([ 3, 'c' => 4 ]) ]),
                '/',
                'local/',
                [ 'local/a' => 1, 'local/0' => 2, 'local/b/0' => 3, 'local/b/c' => 4 ]
            ],
            [
                new \ArrayIterator([
                    'a' => 1,
                    'b' => 2,
                    'c' => [ 'd' => [ 3, 4 ], 'e' => new \ArrayIterator([ 'f' => 5, 'g' => 6 ]) ]
                ]),
                '',
                ':',
                [ ':a' => 1, ':b' => 2, ':cd0' => 3, ':cd1' => 4, ':cef' => 5, ':ceg' => 6 ]
            ]
        ];
    }

    /**
     * @covers Flatten::flatten
     * @dataProvider traversablesSeparatorPrefixProvider
     */
    public function testFlattenTraversableWithSeparatorAndPrefix($input, $separator, $prefix, $expectedOutput)
    {
        $output = $this->flattenToArray($input, $separator, $prefix);
        $this->assertEquals($expectedOutput, $output);
    }

    public function flattenWithFlagsProvidor()
    {
        return [
            'NUMERIC_NOT_FLATTENED' => [
                [
                    1,
                    2,
                    100 => [3, 4],
                    'numericOnly' => ['A', 'B', 'C', 'D'],
                    'mixed' => ['A', 'B', 'digit' => 0],
                    'multidimensional' => [2 => 'A', 5 => 'B', [8 => 'C', 9 => 'D', 'digit' => 0], 'digit' => 1, []],
                    'emptyArray' => []
                ],
                '.',
                '_',
                Flatten::FLAG_NUMERIC_NOT_FLATTENED,
                [
                    '_' => [1, 2, 100 => [3, 4]],
                    '_numericOnly' => ['A', 'B', 'C', 'D'],
                    '_mixed' => ['A', 'B'],
                    '_mixed.digit' => 0,
                    '_multidimensional' => [2 => 'A', 5 => 'B', [8 => 'C', 9 => 'D', 'digit' => 0], []],
                    '_multidimensional.digit' => 1,
                    '_emptyArray' => []
                ]
            ],
            'NUMERIC_NOT_FLATTENED_PASSIVE' => [
                [
                    'numericOnly' => ['A', 'B', 'C', 'D'],
                    'mixed' => ['A', 'B', 'digit' => 0],
                    'multidimensional' => [ 'chars' => [8 => 'C', 9 => 'D', 'digit' => 0], 'digit' => 1],
                    'emptyArray' => []
                ],
                '.',
                '_',
                Flatten::FLAG_NUMERIC_NOT_FLATTENED,
                [
                    '_numericOnly' => ['A', 'B', 'C', 'D'],
                    '_mixed' => ['A', 'B'],
                    '_mixed.digit' => 0,
                    '_multidimensional.digit' => 1,
                    '_multidimensional.chars' => [8 => 'C', 9 => 'D'],
                    '_multidimensional.chars.digit' => 0,
                    '_emptyArray' => []
                ]
            ]
        ];
    }

    /**
     * @covers Flatten::flatten
     * @dataProvider flattenWithFlagsProvidor
     */
    public function testFlattenWithFlags($input, $separator, $prefix, $flags, $expectedOutput)
    {
        $output = $this->flattenToArray($input, $separator, $prefix, $flags);
        $this->assertEquals($expectedOutput, $output);
    }

    private function flattenToArray(
        $input,
        $separator = Flatten::DEFAULT_SEPARATOR,
        $prefix = Flatten::DEFAULT_PREFIX,
        $flags = Flatten::DEFAULT_FLAGS
    ) {
        return (new Flatten($separator, $prefix, $flags))->flattenToArray($input);
    }
}
