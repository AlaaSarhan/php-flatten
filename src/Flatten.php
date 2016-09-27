<?php
namespace Sarhan;

/**
 * @author Alaa Sarhan <sarhan.alaa@gmail.com>
 * @license LGPL
 */
class Flatten
{
    /**
     * Flattens a variable, possible traversable, into a one-dimensional array, recursively.
     * 
     * Each key in the returned one-dimensional array is the join of all keys leading to each value, in all dimensions,
     * separated by a given separator. That is a fully-qualified key.
     * 
     * Non-traversable values will be returned as-is, after being put into the final array with the fully-qualified key.
     * 
     * An initial prefix can be optionally to namespace all returned keys using that prefix.
     * 
     * @param mixed $var
     * @param string $separator
     * @param string $prefix
     * @return array One-dimensional array containing all values from all possible traversable dimensions in given input.
     */
    public static function flatten($var, $separator = '.', $prefix = '')
    {
        $flattened = [];
        foreach (self::flattenGenerator($var, $separator, $prefix) as $key => $value) {
            $flattened[$key] = $value;
        }
        return $flattened;
    }
    
    private static function flattenGenerator($var, $separator, $prefix = '')
    {
        if (self::canTraverse($var)) {
            $prefix .= (empty($prefix) ? '' : $separator);
            foreach ($var as $key => $value) {
                foreach (self::flattenGenerator($value, $separator, $prefix . $key) as $k => $v) {
                    yield $k => $v;
                }
            }
        } else {
            yield $prefix => $var;
        }
    }
    
    private static function canTraverse($var)
    {
        return is_array($var) || ($var instanceof \Traversable);
    }
}
