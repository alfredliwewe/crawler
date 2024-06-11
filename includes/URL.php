<?php

/**
 * URL
 */
class URL
{
	public $link = "";

	function __construct($link)
	{
		$this->link = $link;
		return $this;
	}

	public function getQuery()
	{
		$query = parse_url($this->link, PHP_URL_QUERY);
		$chars = explode("&", $query);
		$data = [];

		foreach ($chars as $part) {
			$dd = explode("=", $part);
			if (count($dd) > 1) {
				$data[$dd[0]] = $dd[1];
			}
			else{
				$data[$dd[0]] = "";
			}
		}

		return $data;
	}

	public static function purify($link){
		$link = str_replace("//", "/", $link);
		if (Strings::endsWith($link, "/index.php")) {
			$link = str_replace("/index.php", "/", $link);
		}
		$chars = explode("#", $link);
		
		return Strings::trim($chars[0],6);
	}

	public static function isImage($link){
		$image_extensions = ["jpg","png","jpeg","gif","webp"];

		$chars = explode(".", $link);
		$ext = $chars[count($chars)-1];

		return in_array(strtolower($ext), $image_extensions);
	}
}