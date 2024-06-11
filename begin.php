<?php
error_reporting(E_PARSE | E_ERROR);
require 'db.php';
require 'includes/String.php';
require 'includes/URL.php';
require 'functions.php';

$time = time();

$web = $db->query("SELECT * FROM news_sources LIMIT 1")->fetch_assoc(); //assuming we have something in the table
/*
$html = file_get_contents($web['url']);

file_put_contents('html', $html);

echo "done";
*/
$host = parse_url($web['url'], PHP_URL_HOST);

$dom = new DOMDocument;
$dom->loadHTML(file_get_contents("html.html"));

$links = [];
$external = [];
$values = [];

$sql = "INSERT INTO `links`(`id`, `website`, `link`, `text`, `title`, `time`, `status`, `type`) VALUES ";

foreach ($dom->getElementsByTagName("a") as $a) {
	$href = $a->getAttribute("href");
	if (!Strings::startsWith($href, "http")) {
		$href = $web['url'].$href;
	}

	$link_host = parse_url($href, PHP_URL_HOST);

	if ($link_host == $host) {
		$href = URL::purify($href);
		if (!in_array($href, $links) AND !URL::isImage($href)) {
			array_push($links, $href);
			array_push($values, "(NULL, '{$web['id']}', '$href', '', '', '$time', 'saved', 'home')");
		}
	}
	else{
		array_push($external, $href);
	}
}

$db->query($sql." ".implode(", ", $values));


header('Content-Type: application/json; charset=utf-8');
echo json_encode(['links'=> $links, 'external' => $external]);
?>