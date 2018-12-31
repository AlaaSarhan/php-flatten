<?php

namespace Sarhan\Flatten;

/**
 * Flattens values, possibly traversables, into a one-dimensional array, recursively.
 */
class Flatten
{
    const DEFAULT_SEPARATOR = '.';
    const DEFAULT_PREFIX = '';
    const DEFAULT_FLAGS = 0;

    /**
     * Turn off flattening values with integer keys.
     */
    const FLAG_NUMERIC_NOT_FLATTENED = 0b1;

    /**
     * @var string $separator
     */
    private $separator;

    /**
     * @var string $prefix
     */
    private $prefix;

    /**
     * @var int $flags
     */
    private $flags;

    /**
     * @param string $separator
     * @param string $prefix
     * @param int $flags
     * @return Flatten
     * @see Flatten::FLAG_NUMERIC_NOT_FLATTENED
     */
    public function __construct(
        $separator = self::DEFAULT_SEPARATOR,
        $prefix = self::DEFAULT_PREFIX,
        $flags = self::DEFAULT_FLAGS
    ) {
        $this->separator = $separator;
        $this->prefix = $prefix;
        $this->flags = $flags;
    }
    
    /**
     * Flattens a traversable or array into a 1-dimensional array.
     * 
     * Each key (fully-qualified key or FQK) in the returned one-dimensional array is the join of all keys leading to
     * each (non-traversable) value, in all dimensions, separated by the configured separator.
     *
     * The configured prefix will be appended to all FQKs, but it will not be separated with the configured separator.
     * 
     * @param mixed $var
     * @return array 1-dimensional array containing all values from all possible traversable dimensions in given input.
     */
    public function flatten($var)
    {
        $flattened = [];
        foreach ($this->flattenGenerator($var, $this->separator, '', $this->flags) as $key => $value) {
            yield ($this->prefix . $key) => $value;
        }
        return $flattened;
    }

    private function flattenGenerator($var, $separator, $prefix = '', $flags = 0)
    {
        if (!$this->canTraverse($var)) {
            yield $prefix => $var;
            return;
        }
        
        if ($flags & self::FLAG_NUMERIC_NOT_FLATTENED) {
            list ($values, $var) = $this->filterNumericKeysAndGetValues($var);
            if (!empty($values) || empty($var)) {
                yield $prefix => $values;
            }
        }
        
        $prefix .= (empty($prefix) ? '' : $separator);
        foreach ($var as $key => $value) {
            foreach ($this->flattenGenerator($value, $separator, $prefix . $key, $flags) as $k => $v) {
                yield $k => $v;
            }
        }
    }
    
    private function canTraverse($var)
    {
        return is_array($var) || ($var instanceof \Traversable);
    }
    
    private function filterNumericKeysAndGetValues($var)
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
