<?php

/**
 * Filtering Artists into Groups
 */
class Artists
{
	
	function __construct()
	{
		// code...
	}

	public static function byCountries($db, $artist_ids)
	{
		$data = [];
		if (count($artist_ids) > 0) {
			$read = $db->query("SELECT id,country FROM web_artists WHERE id IN (".implode(",", $artist_ids).")");
			while ($row = $read->fetch_assoc()) {
				if (isset($data[$row['country']])) {
					array_push($data[$row['country']], $row['id']);
				}
				else{
					$data[$row['country']] = [$row['id']];
				}
			}
		}

		return $data;
	}

	public static function byGenres($db, $artist_ids)
	{
		// code...
	}

	public static function relatedArtists($db, $artist){
		//get all users that listen to the artist, order by heighest on top
		$country = $db->query("SELECT country FROM web_artists WHERE id = '$artist' ")->fetch_assoc()['country'];

		$users = [];
		$read = $db->query("SELECT * FROM user_song WHERE artist = '$artist' order by value DESC LIMIT 1000");
		while ($row = $read->fetch_assoc()) {
			$users[$row['user']] = $row['value'];
		}

		//get other artist which these people listen to
		$people = array_keys($users);
		if (count($people) > 0) {
			$artists = [];
			$read = $db->query("SELECT artist,value FROM user_artist WHERE user IN (".implode(",", $people).") LIMIT 40000");
			while ($row = $read->fetch_assoc()) {
				if (isset($artists[$row['artist']])) {
					$artists[$row['artist']] += $row['value'];
				}
				else{
					$artists[$row['artist']] = $row['value'];
				}
			}
			arsort($artists);
			$artist_ids = array_keys($artists);
		}
		else{
			//get some random top artists
			$artist_ids = [];

			$r = $db->query("SELECT id FROM web_artists WHERE country = '$country' ORDER BY actions DESC LIMIT 10");
			while ($row = $r->fetch_assoc()) {
				array_push($artist_ids, $row['id']);
			}

			$r = $db->query("SELECT id FROM web_artists WHERE country != '$country' ORDER BY actions DESC LIMIT 10");
			while ($row = $r->fetch_assoc()) {
				array_push($artist_ids, $row['id']);
			}
		}

		//split them by countries
		$byCountries = Artists::byCountries($db, $artist_ids);

		$res = [];
		if (isset($byCountries[$country])) {
			foreach ($artist_ids as $id) {
				if (count($res) < 10) {
					if (in_array($id, $byCountries[$country])) {
						array_push($res, $id);
					}
				}
			}
			//finished added top artists from same country
		}

		//add four other from different countries
		foreach ($artist_ids as $id) {
			if (count($res) < 14) {
				if (!in_array($id, $byCountries[$country])) {
					array_push($res, $id);
				}
			}
		}

		return $res;
	}

	public static function disliked($db, $user)
	{
		$data = [];

		$read = $db->query("SELECT item FROM dislike WHERE user = '$user' AND type = 'artist' ");
		while ($row = $read->fetch_assoc()) {
			array_push($data, $row['item']);
		}

		return $data;
	}

	public static function findCountry($artist, $data)
	{
		$country = 0;
		foreach ($data as $country_id => $artists) {
			if (in_array($artist, $artists)) {
				$country = $country_id;
			}
		}

		return $country;
	}

	public static function trending($db, $already)
	{
		if (count($already) > 0) {
			$where = "WHERE id NOT IN(".implode(",", $already).")";
		}
		else{
			$where = "WHERE 1";
		}

		$selected_countries = [
			91 => 15,
			105=>5,
			209=>10,
			71=>5,
			60=>5,
			192=> 10
		];

		$ids = [];
		/*foreach ($selected_countries as $key => $value) {
			$read = $db->query("SELECT id FROM web_artists $where AND country = '$key' ORDER BY actions DESC LIMIT $value");
			while ($row = $read->fetch_assoc()) {
				array_push($ids, $row['id']);
			}
		}*/
		$read = $db->query("SELECT id FROM web_artists $where ORDER BY actions DESC LIMIT 50");
		while ($row = $read->fetch_assoc()) {
			array_push($ids, $row['id']);
		}
		return $ids;
	}

	public static function getArtists($db, $ids){
		$data = [];

		$ids_str = implode(",", $ids);

		$read = $db->query("SELECT * FROM web_artists WHERE id IN ($ids_str) ");
		while ($row = $read->fetch_assoc()) {
			$data[$row['id']] = $row;
		}

		return $data;
	}

	public static function sameGroups($db, $id){
		$data = [];

		$groups = [];
		$r = $db->query("SELECT DISTINCT category FROM artist_category WHERE artist = '$id' ");
		while ($row = $r->fetch_assoc()) {
			array_push($groups, $row['category']);
		}

		if(count($groups) > 0){
			$str = implode(",", $groups);
			$sql = $db->query("SELECT DISTINCT artist FROM artist_category WHERE category IN ($str) AND artist != '$id' ");
			while ($row = $sql->fetch_assoc()) {
				array_push($data, $row['artist']);
			}
		}

		return $data;
	}
}

//$db = new mysqli("localhost","root","","songs");
//echo json_encode(Artists::relatedArtists($db,63127));
?>