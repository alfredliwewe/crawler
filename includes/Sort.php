<?php


/**
 * Sorting Arrays
 */
class Sort
{
	public static function asc($array)
	{
		sort($array);
		return $array;
	}

	public static function desc($array)
	{
		rsort($array);
		return $array;
	}

	public static function keysAsc($array)
	{
		ksort($array);
		return $array;
	}

	public static function keysDesc($array)
	{
		krsort($array);
		return $array;
	}

	public static function valuesAsc($array)
	{
		asort($array);
		return $array;
	}

	public static function valuesDesc($array)
	{
		arsort($array);
		return $array;
	}

	public static function valuesDesc2($array)
	{
		$values = array_unique(array_values($array));

		$result = [];
		foreach ($values as $value) {
			foreach ($array as $key => $v) {
				if ($value == $v) {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}
}

/*echo json_encode(Sort::valuesDesc([
	'a' => 3,
	'z' => 4,
	'x' => 0,
	'b' => 5,
	'd' => 1,
	'c' => 8
]));*/
?>