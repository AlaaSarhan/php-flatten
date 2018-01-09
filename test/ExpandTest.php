<?php

use PHPUnit\Framework\TestCase;
use Sarhan\Expand;

class ExpandTest extends TestCase
{
    public function provideInflationData()
    {
        return [
            'simple non-nested' => [
                [
                    'foo' => 'bar',
                ],
                [
                    'foo' => 'bar',
                ],
            ],
            'simple nested' => [
                [
                    'foo.first' => 'bar',
                ],
                [
                    'foo' => [
                        'first' => 'bar',
                    ],
                ],
            ],
            'nested with different keys on one level' =>[
                [
                    'foo.first' => 'bar',
                    'foo.second' => 'bla',
                ],
                [
                    'foo' => [
                        'first' => 'bar',
                        'second' => 'bla',
                    ],
                ],
            ],

            'nested on multiple levels' => [
                [
                    'foo.first.second' => 'bar',
                ],
                [
                    'foo' => [
                        'first' => ['second' => 'bar'],
                    ],
                ],
            ],
            'nested with conflicting array and simple value for one key favours array' =>[
                [
                    'foo' => 'bloo',
                    'foo.first' => 'bar',
                    'foo.second' => 'bla',
                ],
                [
                    'foo' => [
                        'first' => 'bar',
                        'second' => 'bla',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideInflationData
     */
    public function testInflate($input, $expectedOutput)
    {
        $actualOutput = Expand::inflate($input);

        self::assertEquals($expectedOutput, $actualOutput);
    }
}
