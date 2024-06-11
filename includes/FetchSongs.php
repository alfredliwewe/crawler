<?php
class FetchSongs{
	public static function topSongs($db, $user, $artist)
	{
		$data = [];

		$read = $db->query("SELECT song FROM user_song WHERE user = '$user' AND artist = '$artist' ORDER BY value DESC LIMIT 3 ");
		while ($row = $read->fetch_assoc()) {
			array_push($data, $row['song']);
		}

		if(count($data) == 0){
			$read = $db->query("SELECT id FROM web_songs WHERE artist = '$artist' ORDER BY plays DESC LIMIT 3 ");
			while ($row = $read->fetch_assoc()) {
				array_push($data, $row['id']);
			}
		}

		return $data;
	}

	public static function isAdded($db, $artist, $title){
		$read = $db->query("SELECT web_songs.id FROM web_songs JOIN web_artists ON web_songs.artist = web_artists.id WHERE web_songs.title = '$title' AND web_artists.name = '$artist' ");
		return $read->num_rows > 0;
	}

	public static function disliked($db, $user){
		$data = [];

		$read = $db->query("SELECT item FROM dislike WHERE user = '$user' AND type = 'song' ");
		while ($row = $read->fetch_assoc()) {
			array_push($data, $row['item']);
		}

		return $data;
	}

	/*public static function artistsTopSongs($db, $artists){
		$data = [];

		foreach ($artists as $artist) {
			$songs = [];
			$read = $db->query("SELECT id FROM web_songs WHERE artist = '$artist' ORDER BY plays DESC LIMIT 5");
			while ($row = $read->fetch_assoc()) {
				array_push($songs, $row['id']);
			}
			$data[$artist] = $songs;
		}

		return $data;
	}*/

	public static function artistsTopSongs($db, $artists){
		$data = [];

		
		$read = $db->query("SELECT id,artist FROM web_songs WHERE artist IN (".implode(",", $artists).") ORDER BY plays DESC");
		while ($row = $read->fetch_assoc()) {
			if (isset($data[$row['artist']])) {
				if (count($data[$row['artist']]) < 5) {
					array_push($data[$row['artist']], $row['id']);
				}
			}
			else{
				$data[$row['artist']] = [$row['id']];
			}
		}

		return $data;
	}
}

//testing
/*
require '../db.php';
require 'Artists.php';

$trending = Artists::trending($db, []);

echo json_encode(FetchSongs::artistsTopSongs($db, $trending));
*/
?>