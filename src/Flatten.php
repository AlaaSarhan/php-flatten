<?php

namespace Sarhan\Flatten;

/**
 * Flattens values, possibly traversables, into a one-dimensional array, recursively.
 *
 * Provides a reverse method to unflatten previously flattened value into it's original form.
 */
class Flatten
{
    const DEFAULT_SEPARATOR = '.';
    const DEFAULT_PREFIX = '';
    const DEFAULT_FLAGS = 0;

    /**
     * Turn off flattening values with integer keys.
     *
     * This flag has no effect on unflattening (the reverse function).
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
     * Flattens an iterable into a 1-dimensional generator.
     *
     * Each key (fully-qualified key or FQK) in the returned one-dimensional array is the join of all keys leading to
     * each (non-traversable) value, in all dimensions, separated by the configured separator.
     *
     * The configured prefix will be appended to all FQKs, but it will not be separated with the configured separator.
     *
     * @param mixed $var
     * @return 1-dimensional generator.
     */
    public function flatten($var)
    {
        foreach ($this->flattenGenerator($var, '') as $key => $value) {
            yield ($this->prefix . $key) => $value;
        }
    }

    /**
     * Flattens an iterable into a 1-dimensional array.
     *
     * @param mixed $var
     * @return  array
     * @see flatten
     */
    public function flattenToArray($var)
    {
        return iterator_to_array($this->flatten($var));
    }

    /**
     * Unflattens a 1-dimensional iterable into a multi-dimensional generator.
     *
     * Fully Qualitifed Keys (FQKs) in the input array will be split by the
     * configured separator, and resulting splits will form keys for each level
     * down the resulting multi-dimensional array.
     *
     * The configured prefix will be removed from FQKs in the input array first.
     *
     * The resulting generator can be recursively converted into a final array
     * using the utility class `TraversableToArray` which accounts for the fact
     * that generatos may yield same key with different values and combine these
     * values into an array under that key as expected.
     *
     * @param mixed $var
     * @return multi-dimensional generator.
     * @see Util\TraversableToArray
     * @see unflattenToArray
     * @throws EmptySeparatorException when the configured separator is empty
     */
    public function unflatten($var)
    {
        if (empty($this->separator)) {
            throw new EmptySeparatorException();
        }

        if (!$this->canTraverse($var)) {
            yield $var;
        }

        foreach ($var as $key => $value) {
            $key = substr($key, strlen($this->prefix));

            if (!empty($key)) {
                $value = $this->unflattenGenerator($key, $value);
            }

            if ($this->canTraverse($value)) {
                foreach ($value as $k => $v) {
                    yield $k => $v;
                }
            } else {
                yield $value;
            }
        }
    }

    /**
     * Unflattens a 1-dimensional iterable into a multi-dimensional array.
     *
     * @param mixed $var
     * @return  array
     * @see unflatten
     */
    public function unflattenToArray($var)
    {
        return Util\TraversableToArray::toArray($this->unflatten($var));
    }

    private function flattenGenerator($var, $prefix)
    {
        if (!$this->canTraverse($var)) {
            yield $prefix => $var;
            return;
        }

        if ($this->flags & self::FLAG_NUMERIC_NOT_FLATTENED) {
            list ($values, $var) = $this->filterNumericKeysAndGetValues($var);
            if (!empty($values) || empty($var)) {
                yield $prefix => $values;
            }
        }

        $prefix .= (empty($prefix) ? '' : $this->separator);
        foreach ($var as $key => $value) {
            foreach ($this->flattenGenerator($value, $prefix . $key) as $k => $v) {
                yield $k => $v;
            }
        }
    }

    private function unflattenGenerator($fqk, $value)
    {
        list($key, $fqk) = $this->splitFQK($fqk);

        if (!empty($fqk)) {
            $value = $this->unflattenGenerator($fqk, $value);
        }

        yield $key => $value;
    }

    private function splitFQK($fqk)
    {
        $splits = explode($this->separator, $fqk, 2);

        if (!isset($splits[1])) {
            $splits[1] = null;
        }

        return $splits;
    }

    private function canTraverse($var)
    {
        return !is_null($var) && (is_array($var) || ($var instanceof \Traversable));
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
