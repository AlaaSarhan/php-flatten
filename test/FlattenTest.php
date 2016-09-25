<?php

use PHPUnit\Framework\TestCase;
use Sarhan\Flatten;

class FlattenTest extends TestCase
{
    public function scalarProvider()
    {
        return [
            [ null, null ],
            [ '', '' ],
            [ 0, 0 ],
            [ 3.14, 3.14 ],
            [ 'test', 'test' ],
            [ false, false ]
        ];
    }
    
    /**
     * @covers Flatten::flatten
     * @dataProvider scalarProvider
     */
    public function testFlattenScalar($input, $expectedOutput)
    {
        $output = Flatten::flatten($input);
        
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
        $output = Flatten::flatten($input);
        
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
                new \ArrayIterator([ 'a' => 1, 'b' => 2, 'c' => [ 'd' => [ 3, 4 ], 'e' => new \ArrayIterator([ 'f' => 5, 'g' => 6 ]) ] ]),
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
        $output = Flatten::flatten($input);
        $this->assertEquals($expectedOutput, $output);
    }
    
    public function traversablesSeparatorPrefixProvider()
    {
        return [
            [ new \ArrayIterator([ ]), '-', 'global', [ ] ],
            [ new \ArrayIterator([ 0 ]), '-', 'global', [ 'global-0' => 0 ] ],
            [ new \ArrayIterator([ 1, 2 ]), '-', 'global', [ 'global-0' => 1, 'global-1' => 2 ] ],
            [
                new \ArrayIterator([ 1, 2, [ 3, 4 ] ]),
                '-',
                'global',
                [ 'global-0' => 1, 'global-1' => 2, 'global-2-0' => 3, 'global-2-1' => 4 ]
            ],
            [
                new \ArrayIterator([ 'a' => 1, 2, 'b' => new \ArrayIterator([ 3, 'c' => 4 ]) ]),
                '/',
                'local',
                [ 'local/a' => 1, 'local/0' => 2, 'local/b/0' => 3, 'local/b/c' => 4 ]
            ],
            [
                new \ArrayIterator([ 'a' => 1, 'b' => 2, 'c' => [ 'd' => [ 3, 4 ], 'e' => new \ArrayIterator([ 'f' => 5, 'g' => 6 ]) ] ]),
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
    public function testFlattenTraversableWithSeparatorAndPrefix($var, $separator, $prefix, $expectedOutput)
    {
        $output = Flatten::flatten($var, $separator, $prefix);
        $this->assertEquals($expectedOutput, $output);
    }
}
