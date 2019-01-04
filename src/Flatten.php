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
     * @return 1-dimensional generator.
     */
    public function flatten($var)
    {
        foreach ($this->flattenGenerator($var, '') as $key => $value) {
            yield ($this->prefix . $key) => $value;
        }
    }

    public function unflatten($var)
    {
        if (!$this->canTraverse($var)) {
            yield $var;
        }

        foreach ($var as $key => $value) {
            if (!empty($this->prefix)
                && substr($key, 0, strlen($this->prefix)) === $this->prefix
            ) {
                $key = substr($key, strlen($this->prefix));
            }

            if (!empty($key)) {
                foreach ($this->unflattenGenerator($key, $value) as $k => $v) {
                    yield $k => $v;
                }
            } else {
                if ($this->canTraverse($value)) {
                    foreach ($value as $k => $v) {
                        yield $k => $v;
                    }
                } else {
                    yield $value;
                }
            }
        }
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
        $res = !empty($this->separator)
                ? explode($this->separator, $fqk, 2)
                : [substr($fqk, 0, 1), substr($fqk, 1)];

        if (!isset($res[1])) {
            $res[1] = null;
        }

        return $res;
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
