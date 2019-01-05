<?php

namespace Sarhan\Flatten\Test;

use PHPUnit\Framework\TestCase;
use Sarhan\Flatten\Util\TraversableToArray;

class TraversableToArrayTest extends TestCase
{
    public function traversablesProvider()
    {
        // Iterator example
        $iteratorsInput = new \AppendIterator();
        $iteratorsInput->append(new \ArrayIterator([
            'a',
            'b',
            100 => 'c',
            'd' => [1, 2, 3],
            'e'
        ]));
        $iteratorsInput->append(new \ArrayIterator([
            'A',
            new \ArrayIterator(['B', 'bB']),
            100 => ['d', 'e', 'f'],
            'E',
            'D' => 'd'
        ]));

        // Generator example
        $innerGenerator = function () {
            yield 0 => 'B';
            yield 1 => 'bB';
        };

        $generatorInput = function () use ($innerGenerator) {
            yield 0 => 'a';
            yield 0 => 'A';
            yield 1 => 'b';
            yield 1 => $innerGenerator();
            yield 100 => 'c';
            yield 100 => ['d', 'e', 'f'];
            yield 'd' => [1, 2, 3];
            yield 101 => 'e';
            yield 101 => 'E';
            yield 'D' => 'd';
        };

        $expectedOutput = [
            [['a', 'A']],
            [['b', 'B'], 'bB'],
            100 => [['c', 'd'], 'e', 'f'],
            'd' => [1, 2, 3],
            [['e', 'E']],
            'D' => 'd'
        ];

        return [
            'Iterator' => [$iteratorsInput, $expectedOutput],
            'Generator' => [$generatorInput(), $expectedOutput]
        ];
    }

    /**
     * @dataProvider traversablesProvider
     */
    public function testTraversableToArray($input, $expectedOutput)
    {
        $output = TraversableToArray::toArray($input);
        $this->assertEquals($expectedOutput, $output);
    }
}
