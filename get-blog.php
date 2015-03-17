<?php
require 'config.php';
session_start();

if(empty($_SESSION['token'])) {
	header("HTTP/1.1 403 Forbidden");
	exit("No token found in session.");
}

if(empty($_REQUEST['blog'])) {
	exit("No blog specified.");
}

// Set some initial variables
$offset = 0;
if(isset($_REQUEST['offset']) && isset($_REQUEST['posts'])) {
	$offset = $_REQUEST['offset'] + $_REQUEST['posts'];
}
$blog = $_REQUEST['blog'];
if(!strpos($blog, ".")) {
	$blog .= ".tumblr.com";
}

// Create blog name folder
if(!is_dir($blog)) {
	mkdir($blog);
}

// Additional required variables
$num_posts_seen = 0;
$imgs = array();
$base = "https://api.tumblr.com/v2/blog/{$blog}/posts/photo?api_key=" . urlencode($consumer_key);

$req = file_get_contents("{$base}&offset=" . intval($offset));
$obj = json_decode($req);

if($obj->meta->status != 200) {
	exit($obj->meta->msg);
}

foreach($obj->response->posts as $post) {
	$num_posts_seen++;
	foreach($post->photos as $photo) {
		$src = $photo->alt_sizes[0]->url;
		if(!is_file("$blog/" . basename($src))) {
			file_put_contents("$blog/" . basename($src), file_get_contents($src));
			usleep(100000);
		}
		$imgs[] = $src;
	}
}

$response = array(
	"status" => "complete",
	"total_posts" => $obj->response->total_posts,
	"posts" => $num_posts_seen,
	"offset" => $offset,
	"imgs" => count($imgs),
	"blog" => $blog
);

if($obj->response->total_posts > ($offset + 20)) {
	$response["status"] = "downloading";
}

header('Content-type: application/json');
echo json_encode($response);
