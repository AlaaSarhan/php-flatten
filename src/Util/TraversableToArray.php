<?php

namespace Sarhan\Flatten\Util;

/**
 * Traverse a generator and convert it into an array.
 *
 * Inner iterators, traversables and generators will be recursively converted into arrays too.
 *
 * Generators yielding duplicate keys with several values will cause an entry
 * in the equivalent array with the same key and the values collected as an array.
 */
class TraversableToArray
{
	/**
	 * @param Array|Traversable $traversable
	 * @return array
	 */
	public static function toArray($traversable)
    {
        if (!is_array($traversable) && !($traversable instanceof \Traversable)) {
            return $traversable;
        }

        $array = [];
        foreach ($traversable as $key => $value) {
            $value = self::toArray($value);

            if (isset($array[$key])) {
                if (!is_array($array[$key])) {
                    $array[$key] = [$array[$key]];
                }
                if (!is_array($value)) {
                	$value = [$value];
                }
                $value = self::array_merge_recursive_with_keys($array[$key], $value);
            }

            $array[$key] = $value;
        }
        return $array;
    }

	private static function array_merge_recursive_with_keys()
	{
		$arrays = func_get_args();
		$res = array_shift($arrays);

		while($arr = array_shift($arrays)) {
			foreach($arr as $key => $value) {
	        	if (!isset($res[$key])) {
	        		$res[$key] = $value;
	        	} else {
	        		$newValue = is_array($res[$key]) ? $res[$key] : [$res[$key]];

	        		if (is_array($value)) {
	        			$newValue = self::array_merge_recursive_with_keys($newValue, $value);
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
