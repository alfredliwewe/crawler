<?php

/**
 * String methods and extension
 */
class Strings
{
	
	public static function endsWith($str, $end)
	{
		if(strlen($str) >= strlen($end)){
			//
			$cut = substr($str, strlen($str) - strlen($end));
			return $cut == $end;
		}
		else{
			return false;
		}
	}

	public static function startsWith($str, $word)
	{
		if(strlen($str) >= strlen($word)){
			$cut = substr($str,0,strlen($word));
			return $cut == $word;
		}
		else{
			return false;
		}
	}

	public static function contains($str, $word){
		return strpos($str, $word) !== FALSE;
	}

	public static function multipleSplit($str, $chars, $space=true)
	{
		$strs = [strtolower(trim($str))];

		foreach($chars as $char){
			$array = [];

			foreach($strs as $string){
				$array = array_merge($array, explode($char.($space?" ":""), $string));
			}

			$strs = $array;
		}
		for ($i=0; $i < count($strs); $i++) { 
			$strs[$i] = trim($strs[$i]);
		}
		return $strs;
	}

	public static function cutBetween($str, $o, $f){
		$res = "";
		$can = false;

		for ($i=0; $i < strlen($str); $i++) {
			if ($str[$i] == $f) {
				$can =false;
			} 
			if ($can) {
				$res .= $str[$i];
			}

			if ($str[$i] == $o) {
				$can =true;
			}
		}
		return trim($res);
	}

	public static function cutBetweenStrings($str, $initial, $final)
	{
		$pos1 = strpos($str, $initial);
		$pos2 = strpos($str, $final);

		if ($pos1 AND $pos2 AND $pos2 > $pos1) {
			$cut = substr($str, strlen($initial)+$pos1,  $pos2 - (strlen($initial)+$pos1));
			return $cut;
		}
		else{
			return false;
		}
	}

	public static function filterAlphabetAndSpace($string) {
	    // Remove non-alphabet characters and space
	    $filteredString = preg_replace('/[^a-zA-Z ]/', '', $string);
	    
	    return $filteredString;
	}

	public static function trim($str, $count)
	{
		for ($i=0; $i < $count; $i++) { 
			$str = trim($str);
		}

		return $str;
	}

	public static function removeStrings($str, $list)
	{
		foreach ($list as $char) {
			$str = str_replace($char, "", $str);
			$str = str_replace("  ", " ", $str);
		}
		return $str;
	}

	public static function fixName($name){
		$articles = ["the", "of", "an"];
		$chars = explode(" ", $name);

		for ($i=0; $i < count($chars); $i++) { 
			if (in_array($chars[$i], $articles)) {
				$chars[$i] = ucfirst($chars[$i]);
			}
			else{
				if (strlen($chars[$i]) <= 3) {
					$chars[$i] = strtoupper($chars[$i]);
				}
				else{
					$chars[$i] = ucfirst($chars[$i]);
				}
			}
		}
		return trim(implode(" ", $chars));
	}

	public static function getNumber($str){
		$allowed = ['0','1','2','3','4','5','6','7','8','9','.'];
		$has_stated = false;
		$has_completed = false;
		$result = "";

		for ($i=0; $i < strlen($str); $i++) { 
			if (!$has_completed) {
				if (in_array($str[$i], $allowed)) {
					$has_stated = true;
					$result .= $str[$i];
				}
				else{
					if ($has_stated) {
						$has_completed = true;
					}
				}
			}
		}

		return $result;
	}

	public static function words($stmt, $length=6)
	{
		$chars = explode(" ", $stmt);
		if (count($chars) > $length) {
			$chars = array_slice($chars, 0, $length);
		}

		return implode(" ", $chars);
	}
}

//echo Strings::endsWith("alfred", "ed") ? "Yes" : "No";
//echo Strings::startsWith("alfred", "al") ? "Yes" : "No";
//echo Strings::contains("alfred", "red") ? "Yes" : "No";
//var_dump(Strings::multipleSplit("hello ft. robert", ["ft."]));
//echo Strings::cutBetween("SELECT REPEATED(link) FROM links", "(", ")");
//echo Strings::cutBetweenStrings("SELECT REPEATED(link) FROM links", "link", "links");
//echo Strings::fixName("post malone");