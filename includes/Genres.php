<?php

/**
 * Genres
 */
class Genres
{
	
	public static function related($db, $id)
	{
		$data = [];
		$read = $db->query("SELECT * FROM genre_relations WHERE genre1 = '$id' OR genre2 = '$id' ");
		while ($row = $read->fetch_assoc()) {
			$friend = $row['genre1'] == $id ? $row['genre2'] : $row['genre1'];
			if (!in_array($friend, $data)) {
				array_push($data, $friend);
			}
		}

		return $data;
	}
}