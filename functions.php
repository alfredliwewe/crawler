<?php

function time_ago($time){
    $time = (int)trim($time);
	$labels = [
		['s', 60],
		['min', 3600],
		['h', 3600 * 24],
		['d', 3600 * 24 * 7],
		['w', 3600 * 24 * 7 * 4],
		['mon', 3600 * 24 * 7 * 30],
		['y', 3600 * 24 * 7 * 30 * 12]
	];

	$dif = time() - $time;

	$can = true;
	$label = null;
	$div = 1;

	if ($dif == 0) {
		return "now";
	}

	for ($i=0; $i < count($labels); $i++) { 
		if ($dif < $labels[$i][1]) {
			if($can){
				$can = false;
				$label = $labels[$i][0];

				if($i != 0){
					$div = $labels[$i-1][1];
				}
			}
		}
	}

	if ($label == null) {
		return "Unknown";
	}
	else{
		return floor($dif/$div).$label;
	}
}

function getCountries($db)
{
	$countries = [];
	$sql = $db->query("SELECT * FROM countries");
	while ($row = $sql->fetch_assoc()) {
		$countries[$row['id']] = $row;
	}

	$countries[0] = ['id' => 0, 'name' => ""];

	return $countries;
}

function guidv4($data = null) {
    // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
    $data = $data ?? random_bytes(16);
    assert(strlen($data) == 16);

    // Set version to 0100
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    // Set bits 6-7 to 10
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // Output the 36 character UUID.
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

class SQLiteResult {
	function __construct($result,$isResult=true)
	{
		if ($isResult) {
			$count = 0;
			$store = [];

			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$count += 1;
				array_push($store, $row);
			}
			$this->store = $store;
			$this->num_rows = $count;
			$this->index = 0;
		}
		else{
			$this->store = $result;
			$this->num_rows = count($result);
			$this->index = 0;
		}
	}

	public function fetch_assoc()
	{
		if ($this->index < $this->num_rows) {
			$this->index += 1;
			return $this->store[$this->index - 1];
		}
		else{
			return false;
		}
	}

	public function fetchArray()
	{
		if ($this->index < $this->num_rows) {
			$this->index += 1;
			return $this->store[$this->index - 1];
		}
		else{
			return false;
		}
	}

	public function getColumnNames()
	{
		$names = [];
		if ($this->num_rows > 0) {
			foreach ($this->store[0] as $key => $value) {
				array_push($names, $key);
			}
		}

		return $names;
	}
}

class mysql_like extends sqlite3
{
	public $error = "";
	public $insert_id = 0;

	function __construct($file)
	{
		parent::__construct($file);
	}
	
	public function query($sql)
	{
		if (Strings::contains($sql,"REPEATED(")) {
			$sqlObject = new SqlParser($sql);
			$tdata = $sqlObject->getRepeatedInfo();

			$firstWord = strtolower(explode(" ", trim($sql))[0]);
			if ($firstWord == "select") {
				$data = [];

				$read = parent::query("SELECT {$tdata[1]} FROM {$tdata[0]} ");
				while($row = $read->fetchArray(SQLITE3_ASSOC)){
					if (isset($data[$row[$tdata[1]]])) {
						$data[$row[$tdata[1]]] += 1;
					}
					else{
						$data[$row[$tdata[1]]] = 1;
					}
				}

				//get the clean data
				$clean = [];
				foreach ($data as $key => $value) {
					if ($value > 1) {
						array_push($clean, [
							$tdata[0] => $key,
							'count' => $value
						]);
					}
				}

				return new SQLiteResult($clean, false);
			}
			elseif ($firstWord == "delete") {
				$data = [];
				$ids = [];

				$read = parent::query("SELECT {$tdata[1]},id FROM {$tdata[0]} ");
				while($row = $read->fetchArray(SQLITE3_ASSOC)){
					if (isset($data[$row[$tdata[1]]])) {
						$data[$row[$tdata[1]]] += 1;
						array_push($ids, $row['id']);
					}
					else{
						$data[$row[$tdata[1]]] = 1;
					}
				}

				if (count($ids) > 0) {
					$res = parent::query("DELETE FROM {$tdata[0]} WHERE id IN (".implode(",", $ids).")");
				}
				return true;
			}
			else{

			}
		}
		else{
			$result = parent::query($sql);
			$this->error = $this->lastErrorMsg();
			$chars = explode(" ", trim($sql));

			if (strtolower($chars[0]) == "select") {
				return new SQLiteResult($result);
			}
			elseif (strtolower($chars[0]) == "insert") {
				return $result;
			}
			else{
				return $result;
			}
		}
	}

	public function real_escape_string($str)
	{
		return $this->escapeString($str);
	}
}

function removeBlackBars($inputImagePath, $outputImagePath, $targetWidth, $targetHeight) {
    // Load the original image
    $originalImage = imagecreatefromjpeg($inputImagePath);
    
    // Get the dimensions of the original image
    $originalWidth = imagesx($originalImage);
    $originalHeight = imagesy($originalImage);
    
    // Calculate the aspect ratio of the original image
    $originalAspectRatio = $originalWidth / $originalHeight;
    
    // Calculate the aspect ratio of the target dimensions
    $targetAspectRatio = $targetWidth / $targetHeight;
    
    // Calculate the new dimensions while maintaining the target aspect ratio
    if ($originalAspectRatio > $targetAspectRatio) {
        $newWidth = $targetWidth;
        $newHeight = $targetWidth / $originalAspectRatio;
    } else {
        $newWidth = $targetHeight * $originalAspectRatio;
        $newHeight = $targetHeight;
    }
    
    // Calculate the x and y offsets to center the image
    $xOffset = ($targetWidth - $newWidth) / 2;
    $yOffset = ($targetHeight - $newHeight) / 2;
    
    // Create a new blank canvas
    $newImage = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // Fill the canvas with a white background
    $whiteColor = imagecolorallocate($newImage, 255, 255, 255);
    imagefill($newImage, 0, 0, $whiteColor);
    
    // Resize and copy the original image onto the new canvas
    imagecopyresampled($newImage, $originalImage, $xOffset, $yOffset, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
    
    // Save the modified image
    imagejpeg($newImage, $outputImagePath, 90);
    
    // Clean up memory
    imagedestroy($originalImage);
    imagedestroy($newImage);
}

function fileExtension($filename){
	$chars = explode(".", $filename);
	return strtolower($chars[count($chars)-1]);
}

function db_update($table, $cv, $where)
{
	global $db;

	$contentValues = [];
	foreach ($cv as $key => $value) {
		array_push($contentValues, "`$key` = '$value'");
	}

	$whereClause = [];
	foreach ($where as $key => $value) {
		array_push($whereClause, "`$key` = '$value'");
	}

	return $db->query("UPDATE `$table` SET ".implode(", ", $contentValues)." WHERE ".implode(" AND ", $whereClause));
}

function db_delete($table, $where)
{
	global $db;

	$whereClause = [];
	foreach ($where as $key => $value) {
		array_push($whereClause, "`$key` = '$value'");
	}

	return $db->query("DELETE FROM `$table` WHERE ".implode(" AND ", $whereClause));
}

function db_insert($table, $array)
{
	global $db;

	$columns = [];
	$values = [];
	$read = $db->query("SHOW COLUMNS FROM `$table`");
	while ($row = $read->fetch_assoc()) {
		array_push($columns, "`{$row['Field']}`");
		if ($row['Extra'] == "auto_increment") {
			array_push($values, "NULL");
		}
		else{
			$value = isset($array[$row['Field']]) ? $db->real_escape_string($array[$row['Field']]) : "0";
			array_push($values, "'$value'");
		}
	}

	$sql = "INSERT INTO `$table` (".implode(",",$columns).") VALUES (".implode(",",$values).")";
	$db->query($sql);
	return $db->insert_id;
}

function getData($table, $array){
	global $db;

	$wheres = [];

	foreach ($array as $key => $value) {
		array_push($wheres, "`$key` = '$value' ");
	}

	return $db->query("SELECT * FROM `$table` WHERE ".implode(" AND ", $wheres))->fetch_assoc();
}

function getAll($table, $ref=null){
	global $db;

	if ($ref == null) {
		$read = $db->query("SELECT * FROM `$table` ");
		$rows = [];
		while ($row = $read->fetch_assoc()) {
			array_push($rows, $row);
		}

		return $rows;
	}
	else{
		$read = $db->query("SELECT * FROM `$table` ");
		$rows = [];
		while ($row = $read->fetch_assoc()) {
			$rows[$row[$ref]] = $row;
		}

		return $rows;
	}
}

class Crypto{
	public static function uid($length)
	{
		$characters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '_', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		$str = "";
		for ($i=0; $i < $length; $i++) { 
			$str .= $characters[rand(0,count($characters)-1)];
		}
		return $str;
	}

	public static function letters_numbers($length)
	{
		$characters = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		$str = "";
		for ($i=0; $i < $length; $i++) { 
			$str .= $characters[rand(0,count($characters)-1)];
		}
		return $str;
	}

	public static function letters($length)
	{
		$characters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'];
		$str = "";
		for ($i=0; $i < $length; $i++) { 
			$str .= $characters[rand(0,count($characters)-1)];
		}
		return $str;
	}
}

function curl_post($url, array $post = NULL, array $options = array()) { 
	$defaults = array( 
		CURLOPT_POST => 1, 
		CURLOPT_HEADER => 0, 
		CURLOPT_URL => $url, 
		CURLOPT_FRESH_CONNECT => 1, 
		CURLOPT_RETURNTRANSFER => 1, 
		CURLOPT_FORBID_REUSE => 1, 
		CURLOPT_TIMEOUT => 4, 
		CURLOPT_POSTFIELDS => http_build_query($post) 
	); 

	$ch = curl_init(); 
	curl_setopt_array($ch, ($options + $defaults)); 
	if( ! $result = curl_exec($ch)) { 
		//trigger_error(curl_error($ch)); 
		return false;
	} 
	else{
		return $result;
	}
	curl_close($ch);
}

function sendEmail($to, $subject, $message)
{
	global $db;
	global $config;
	$to = $db->real_escape_string($to);
	$subject1 = $db->real_escape_string($subject);
	$message1 = $db->real_escape_string($message);
	$time = time();

	//$from = "ellentaniaphiri@gmail.com";
	$from = $config['email'];
	
	//$ins = $db->query("INSERT INTO `emails`(`id`, `receiver`, `subject`, `content`, `time`) VALUES (NULL, '$to', '$subject1', '$message1', '$time')");
	/*if (!$ins) {
		file_put_contents('email_log.txt', file_get_contents('email_log.txt')."||".$db->error);
	}*/
	return curl_post("https://amuzeemw.com/saved/mail.php", ['from' => $from, 'email' => $to, 'subject' => $subject, 'message' => $message], []);
}

function sendMessage($to, $message)
{
	global $db;
	$to = $db->real_escape_string($to);
	//$subject = $db->real_escape_string($subject);
	$message = $db->real_escape_string($message);
	$time = time();

	//$from = "ellentaniaphiri@gmail.com";

	$ins = $db->query("INSERT INTO `outgoing_messages`(`id`, `receiver`, `message`, `date`, `status`) VALUES (NULL, '$to', '$message', '$time', 'saved')");
	
	/*$ins = $db->query("INSERT INTO `messages`(`id`, `receiver`, `message`, `date`) VALUES (NULL, '$to', '$message', '$time')");
	if (!$ins) {
		file_put_contents('email_log.txt', file_get_contents('email_log.txt')."||".$db->error);
	}*/
	//return curl_post("https://adimo-shopping.com/messages/api/", ['phone' => $to, 'send_message' => $message], []);
}

function sendMessages($messages)
{
	global $db;
	$time = time();

	$values = [];
	foreach ($messages as $row) {
		$to = $db->real_escape_string($row['phone']);
		$message = $db->real_escape_string($row['message']);

		array_push($values, "(NULL, '$to', '$message', '$time', 'saved')");
	}

	if (count($messages)) {
		$ins = $db->query("INSERT INTO `outgoing_messages`(`id`, `receiver`, `message`, `date`, `status`) VALUES ".implode(", ", $values));
	}
}

function clearDirectory($dir) {
	if (!is_dir($dir)) {
        throw new InvalidArgumentException("$dir must be a directory");
    }
    if (substr($dir, -1) != '/') {
        $dir .= '/';
    }

    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        $path = $dir . $file;
        if (is_dir($path)) {
            clearDirectory($path);
            rmdir($path);
        } else {
            unlink($path);
        }
    }
}

function getFullUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $requestUri = $_SERVER['REQUEST_URI'];
    return $protocol . $host . $requestUri;
}

$words = ["Best of ", "Hot in ", "Trending ", "On from "];

$image_extensions = ["jpg","png","jpeg","gif","webp"];
?>