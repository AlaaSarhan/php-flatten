<?php

namespace Sarhan\Flatten\Util;

/**
 * Traverse a generator and convert it into an array.
 *
 * Inner iterators, traversables and generators will be recursively converted into arrays too.
 *
 * Generators yielding duplicate keys with several values will result into an entry
 * in the outer array with the same key and the values collected as an array.
 */
class TraversableToArray
{
    /**
     * @param Array|Traversable $traversable
     * @return array
     */
    public static function toArray($traversable)
    {
        $array = [];
        foreach ($traversable as $key => $value) {
            if (is_array($value) || $value instanceof \Traversable) {
                $value = self::toArray($value);
            }

            if (isset($array[$key])) {
                if (!is_array($array[$key])) {
                    $array[$key] = [$array[$key]];
                }
                if (!is_array($value)) {
                    $value = [$value];
                }
                $value = self::mergeRecrusiveWithKeys($array[$key], $value);
            }

            $array[$key] = $value;
        }
        return $array;
    }

    private static function mergeRecrusiveWithKeys()
    {
        $arrays = func_get_args();
        $res = array_shift($arrays);

        while ($arr = array_shift($arrays)) {
            foreach ($arr as $key => $value) {
                if (!isset($res[$key])) {
                    $res[$key] = $value;
                } else {
                    $newValue = is_array($res[$key]) ? $res[$key] : [$res[$key]];

                    if (is_array($value)) {
                        $newValue = self::mergeRecrusiveWithKeys($newValue, $value);
                    } else {
                        $newValue[] = $value;
                    }

                    $res[$key] = $newValue;
                }
            }
        }
        return $res;
    }
}
