<?php

namespace Sarhan\Flatten\Util;

class GeneratorToArray
{
	public function generatorToArray($unflattenGenerator)
    {
        if (!is_array($unflattenGenerator) && !($unflattenGenerator instanceof \Traversable)) {
            return $unflattenGenerator;
        }

        $array = [];
        foreach ($unflattenGenerator as $key => $value) {
            $value = static::generatorToArray($value);

            if (isset($array[$key])) {
                if (!is_array($array[$key])) {
                    $array[$key] = [$array[$key]];
                }
                if (!is_array($value)) {
                	$value = [$value];
                }
                $value = static::array_merge_recursive_with_keys($array[$key], $value);
            }

            $array[$key] = $value;
        }
        return $array;
    }

	public function array_merge_recursive_with_keys($arr1, $arr2)
	{
        foreach($arr2 as $key => $value) {
        	if (!isset($arr1[$key])) {
        		$arr1[$key] = $value;
        	} else {
        		$newValue = is_array($arr1[$key]) ? $arr1[$key] : [$arr1[$key]];

        		if (is_array($value)) {
        			$newValue = static::array_merge_recursive_with_keys($newValue, $value);
        		} else {
        			$newValue[] = $value;
        		}

        		$arr1[$key] = $newValue;
        	}
        }
	    return $arr1;
	}
}
