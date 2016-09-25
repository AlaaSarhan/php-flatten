<?php
namespace Sarhan;

class Flatten
{
    public static function flatten($var, $separator = '.', $prefix = '')
    {
        if (!self::canTraverse($var)) {
            return $var;
        }
        
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
