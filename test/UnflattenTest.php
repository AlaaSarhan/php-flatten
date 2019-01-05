<?php

namespace Sarhan\Flatten\Test;

use PHPUnit\Framework\TestCase;
use Sarhan\Flatten\Flatten;
use Sarhan\Flatten\EmptySeparatorException;
use Sarhan\Flatten\Util\TraversableToArray;

class UnflattenTest extends TestCase
{
    public function scalarOutputProvider()
    {
        return [
            [ ['' => null], [null] ],
            [ ['' => ''], [''] ],
            [ ['' => 0], [0] ],
            [ ['' => 3.14], [3.14] ],
            [ ['' => 'test'], ['test'] ],
            [ ['' => false], [false] ]
        ];
    }

    /**
     * @covers Flatten::unflatten
     * @dataProvider scalarOutputProvider
     */
    public function testUnflattenScalar($input, $expectedOutput)
    {
        $output = $this->unflattenToArray($input);
        $this->assertEquals($expectedOutput, $output);
    }

    public function scalarOutputSeparatorPrefixProvider()
    {
        return [
            [ [':' => null], '-', ':', [null] ],
            [ ['/' => ''], '.', '/', [''] ],
            [ ['global' => 0], '.', 'global', [0] ],
            [ ['local' => 3.14], '.', 'local', [3.14] ],
            [ ['' => 'test'], 'sep', '', ['test'] ],
            [ ['_' => false], '.', '_', [false] ]
        ];
    }

    /**
     * @covers Flatten::unflatten
     * @dataProvider scalarOutputSeparatorPrefixProvider
     */
    public function testUnflattenScalarWithSeparatorAndPrefix($input, $separator, $prefix, $expectedOutput)
    {
        $output = $this->unflattenToArray($input, $separator, $prefix);
        $this->assertEquals($expectedOutput, $output);
    }

    public function arraysProvider()
    {
        return [
            [ [ ], [ ] ],
            [ [ '0' => 0 ], [ 0 ] ],
            [ [ '0' => 1, '1' => 2 ], [ 1, 2 ] ],
            [
                [ '0' => 1, '1' => 2, '2.0' => 3, '2.1' => 4 ],
                [ 1, 2, [ 3, 4 ] ]
            ],
            [
                [ 'a' => 1, '0' => 2, 'b.0' => 3, 'b.c' => 4 ],
                [ 'a' => 1, 2, 'b' => [ 3, 'c' => 4 ] ]
            ],
            [
                [ 'a' => 1, 'b' => 2, 'c.d.0' => 3, 'c.d.1' => 4, 'c.e.f' => 5, 'c.e.g' => 6 ],
                [ 'a' => 1, 'b' => 2, 'c' => [ 'd' => [ 3, 4 ], 'e' => [ 'f' => 5, 'g' => 6 ] ] ]
            ]
        ];
    }

    /**
     * @covers Flatten::unflatten
     * @dataProvider arraysProvider
     */
    public function testUnflattenArrays($input, $expectedOutput)
    {
        $output = $this->unflattenToArray($input);
        $this->assertEquals($expectedOutput, $output);
    }

    public function traversablesProvider()
    {
        return [
            [ new \ArrayIterator([ ]), [ ] ],
            [ new \ArrayIterator([ '0' => 0 ]), [ 0 ] ],
            [ new \ArrayIterator([ '0' => 1, '1' => 2 ]), [ 1, 2 ] ],
            [
                new \ArrayIterator([ '0' => 1, '1' => 2, '2.0' => 3, '2.1' => 4 ]),
                [ 1, 2, [ 3, 4 ] ]
            ],
            [
                new \ArrayIterator([ 'a' => 1, '0' => 2, 'b.0' => 3, 'b.c' => 4 ]),
                [ 'a' => 1, 2, 'b' => [ 3, 'c' => 4 ] ]
            ],
            [
                new \ArrayIterator([ 'a' => 1, 'b' => 2, 'c.d.0' => 3, 'c.d.1' => 4, 'c.e.f' => 5, 'c.e.g' => 6 ]),
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => [ 'd' => [ 3, 4 ], 'e' => [ 'f' => 5, 'g' => 6 ] ]
                ]
            ]
        ];
    }

    /**
     * @covers Flatten::unflatten
     * @dataProvider traversablesProvider
     */
    public function testUnflattenTraversable($input, $expectedOutput)
    {
        $output = $this->unflattenToArray($input);
        $this->assertEquals($expectedOutput, $output);
    }

    public function traversablesSeparatorPrefixProvider()
    {
        return [
            [ new \ArrayIterator([ ]), '-', 'global-', [ ] ],
            [ new \ArrayIterator([ 'global-0' => 0 ]), '-', 'global-', [ 0 ] ],
            [ new \ArrayIterator([ 'global-0' => 1, 'global-1' => 2 ]), '-', 'global-', [ 1, 2 ] ],
            [
                new \ArrayIterator([ 'global-0' => 1, 'global-1' => 2, 'global-2-0' => 3, 'global-2-1' => 4 ]),
                '-',
                'global-',
                [ 1, 2, [ 3, 4 ] ]
            ],
            [
                new \ArrayIterator([ 'local/a' => 1, 'local/0' => 2, 'local/b/0' => 3, 'local/b/c' => 4 ]),
                '/',
                'local/',
                [ 'a' => 1, 2, 'b' => [ 3, 'c' => 4 ] ]
            ],
            [
                new \ArrayIterator([ ':a' => 1, ':b' => 2, ':c.d.0' => 3, ':c.d.1' => 4, ':c.e.f' => 5 ]),
                '.',
                ':',
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => [ 'd' => [ 3, 4 ], 'e' => [ 'f' => 5 ] ]
                ]
            ],
            'NUMERIC_NOT_FLATTENED' => [
                [
                    '_' => [1, 2, 100 => [3, 4]],
                    '_numericOnly' => ['A', 'B', 'C', 'D'],
                ],
                '.',
                '_',
                [
                    1,
                    2,
                    100 => [3, 4],
                    'numericOnly' => ['A', 'B', 'C', 'D']
                ]
            ],
            'NUMERIC_NOT_FLATTENED_ROOT' => [
                [
                    '_' => [1, 2, 100 => [3, 4]],
                    '_numericOnly' => ['A', 'B', 'C', 'D'],
                    '_mixed' => ['A', 'B'],
                    '_mixed.digit' => 0,
                    '_multidimensional' => [2 => 'A', 5 => 'B', [8 => 'C', 9 => 'D', 'digit' => 0], []],
                    '_multidimensional.digit' => 1,
                    '_emptyArray' => []
                ],
                '.',
                '_',
                [
                    1,
                    2,
                    100 => [3, 4],
                    'numericOnly' => ['A', 'B', 'C', 'D'],
                    'mixed' => ['A', 'B', 'digit' => 0],
                    'multidimensional' => [2 => 'A', 5 => 'B', [8 => 'C', 9 => 'D', 'digit' => 0], 'digit' => 1, []],
                    'emptyArray' => []
                ]
            ],
            'NUMERIC_NOT_FLATTENED_NO_ROOT' => [
                [
                    '_numericOnly' => ['A', 'B', 'C', 'D'],
                    '_mixed' => ['A', 'B'],
                    '_mixed.digit' => 0,
                    '_multidimensional.digit' => 1,
                    '_multidimensional.chars' => [8 => 'C', 9 => 'D'],
                    '_multidimensional.chars.digit' => 0,
                    '_emptyArray' => []
                ],
                '.',
                '_',
                [
                    'numericOnly' => ['A', 'B', 'C', 'D'],
                    'mixed' => ['A', 'B', 'digit' => 0],
                    'multidimensional' => [ 'chars' => [8 => 'C', 9 => 'D', 'digit' => 0], 'digit' => 1],
                    'emptyArray' => []
                ]
            ]
        ];
    }

    /**
     * @covers Flatten::unflatten
     * @dataProvider traversablesSeparatorPrefixProvider
     */
    public function testUnflattenTraversableWithSeparatorAndPrefix($input, $separator, $prefix, $expectedOutput)
    {
        $output = $this->unflattenToArray($input, $separator, $prefix);
        $this->assertEquals($expectedOutput, $output);
    }

    public function testUnflattenWithEmptySeparatorConfiguration()
    {
        $this->expectException(EmptySeparatorException::class);
        $this->unflattenToArray(['ab' => 0, 'abc' => 1, 'abd' => 2], '', '');
    }

    private function unflattenToArray(
        $input,
        $separator = Flatten::DEFAULT_SEPARATOR,
        $prefix = Flatten::DEFAULT_PREFIX
    ) {
        return (new Flatten($separator, $prefix))->unflattenToArray($input);
    }
}
