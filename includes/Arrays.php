<?php
/**
 * Arrays
 */
class Arrays
{
	
	public static function getArrayByKey($array, $key)
	{
		$data = [];

		for ($i=0; $i < count($array); $i++) { 
			array_push($data, $array[$i][$key]);
		}

		return $data;
	}


	public static function findMissingValues($bigArray, $smallArray) {
	    $missingValues = [];

	    foreach ($smallArray as $value) {
	        if (!in_array($value, $bigArray)) {
	            $missingValues[] = $value;
	        }
	    }

	    return $missingValues;
	}
}
?>