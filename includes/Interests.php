<?php
/**
 * All summaries class
 */
class Interests
{
	
	function __construct()
	{
		// code...
	}

	public static function favArtists($db, $user)
	{
		$data = [];

		$read= $db->query("SELECT artist FROM user_artist WHERE user = '$user' ORDER BY value DESC");
		while($row = $read->fetch_assoc()){
			array_push($data, $row['artist']);
		}
		return $data;
	}

	public static function followedArtists($db, $user)
	{
		$data = [];

		$read= $db->query("SELECT artist FROM followers WHERE user = '$user'");
		while($row = $read->fetch_assoc()){
			array_push($data, $row['artist']);
		}
		return $data;
	}
}
?>