<?php

namespace Sarhan\Flatten;

/**
 * @author Alaa Sarhan <sarhan.alaa@gmail.com>
 * @license LGPL
 */
class Flatten
{
    /**
     * Turn off flattening values with integer keys.
     */
    const FLAG_NUMERIC_NOT_FLATTENED = 0b1;
    
    /**
     * Flattens a variable, possibly traversable, into a one-dimensional array, recursively.
     *
     * Each key (fully-qualified key or FQK) in the returned one-dimensional array is the join of all keys leading to
     * each (non-traversable) value, in all dimensions, separated by a given separator.
     *
     * An initial prefix can be optionally provided, but it will not be separated with the specified separator.
     *
     * @param mixed $var
     * @param string $separator
     * @param string $prefix
     * @param int $flags
     * @return array 1-dimensional array containing all values from all possible traversable dimensions in given input.
     * @see Flatten::FLAG_NUMERIC_NOT_FLATTENED
     */
    public static function flatten($var, $separator = '.', $prefix = '', $flags = 0)
    {
        $flattened = [];
        foreach (self::flattenGenerator($var, $separator, '', $flags) as $key => $value) {
            $flattened[$prefix . $key] = $value;
        }
        return $flattened;
    }

    private static function flattenGenerator($var, $separator, $prefix = '', $flags = 0)
    {
        if (!self::canTraverse($var)) {
            yield $prefix => $var;
            return;
        }
        
        if ($flags & self::FLAG_NUMERIC_NOT_FLATTENED) {
            list ($values, $var) = self::filterNumericKeysAndGetValues($var);
            if (!empty($values) || empty($var)) {
                yield $prefix => $values;
            }
        }

        $prefix .= ($prefix === '' ? '' : $separator);
        foreach ($var as $key => $value) {
            foreach (self::flattenGenerator($value, $separator, $prefix . $key, $flags) as $k => $v) {
                yield $k => $v;
            }
        }
    }
    
    private static function canTraverse($var)
    {
        return is_array($var) || ($var instanceof \Traversable);
    }
    
    private static function filterNumericKeysAndGetValues($var)
    {
        $values = [];
        $var = array_filter($var, function ($value, $key) use (&$values) {
            if (is_int($key)) {
                $values[$key] = $value;
                return false;
            }
            return true;
        }, ARRAY_FILTER_USE_BOTH);
        return [$values, $var];
    }
}
