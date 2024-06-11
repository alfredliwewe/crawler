<?php

/**
 * Relations
 */
class Relations
{
	
	public static function userSong($db, $user, $song_id,$artist_id)
	{
		//save user song relations
		$check = $db->query("SELECT * FROM user_song WHERE user = '$user' AND song = '$song_id' ");
		if ($check->num_rows > 0) {
			$db->query("UPDATE user_song SET value = value + 1 WHERE user = '$user' AND song = '$song_id' ");
		}
		else{
			$db->query("INSERT INTO `user_song`(`id`, `user`, `song`, `value`,`artist`) VALUES (NULL, '$user', '$song_id', '1', '$artist_id')");
		}
	}

	public static function userArtist($db, $user,$artist_id)
	{
		//save user artist relations
		$check = $db->query("SELECT * FROM user_artist WHERE user = '$user' AND artist = '$artist_id' ");
		if ($check->num_rows > 0) {
			$db->query("UPDATE user_artist SET value = value + 1 WHERE user = '$user' AND artist = '$artist_id' ");
		}
		else{
			$db->query("INSERT INTO `user_artist`(`id`, `user`, `artist`, `value`) VALUES (NULL, '$user', '$artist_id', '1')");
		}
	}

	public static function getListened($db, $user)
	{
		$all = [];

		$read = $db->query("SELECT * FROM user_song WHERE user = '$user' ORDER BY value DESC");
		while ($row = $read->fetch_assoc()) {
			$all[$row['song']] = $row['value'];
		}

		return $all;
	}

	public static function getArtistSongs($db,$user)
	{
		$all = [];

		$read = $db->query("SELECT * FROM user_song WHERE user = '$user' ORDER BY value DESC");
		while ($row = $read->fetch_assoc()) {
			if (isset($all[$row['artist']])) {
				array_push($all[$row['artist']], $row['song']);
			}
			else{
				$all[$row['artist']] = [$row['song']];
			}
		}

		return $all;
	}
}