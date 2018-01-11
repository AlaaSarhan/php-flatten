<?php

namespace Sarhan;

/**
 * @author  Frank Koornstra
 * @license LGPL
 */
class Expand
{
    /**
     * Expands a one-dimensional array that has flattened keys to a (possibly) multi-dimensional array
     */
    public static function inflate(array $array, string $separator = '.'): array
    {
        $inflated = [];

        foreach ($array as $key => $value) {
            $parent = &$inflated;

            $partList = explode($separator, $key);
            $leafPart = array_pop($partList);

            foreach ($partList as $part) {
                if (! isset($parent[$part]) || ! is_array($parent[$part])) {
                    $parent[$part] = [];
                }
                $parent = &$parent[$part];
            }

            if (empty($parent[$leafPart])) {
                $parent[$leafPart] = $value;
            }
        }

        return $inflated;
    }
}
