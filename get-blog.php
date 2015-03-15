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
$limit = 1000;
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

while($num_posts_seen < $limit) {
	$remaining = $limit - $num_posts_seen;

	set_time_limit(60);

	$req = file_get_contents($base . "&limit=" . ($remaining > 20 ? 20 : $remaining) . "&offset={$num_posts_seen}");
	$obj = json_decode($req);

	if($obj->meta->status != 200) {
		exit($obj->meta->msg);
	}

	if($obj->response->total_posts < $limit) {
		$limit = $obj->response->total_posts;
		$remaining = $limit - $num_posts_seen;
	}

	foreach($obj->response->posts as $post) {
		$num_posts_seen++;
		foreach($post->photos as $photo) {
			$src = $photo->alt_sizes[0]->url;
			if(!is_file("$blog/" . basename($src))) {
				file_put_contents("$blog/" . basename($src), file_get_contents($src));
			}
			$imgs[] = $src;
			usleep(100000);
		}
	}

	usleep(500000);

}

$response = array(
	"posts" => $num_posts_seen,
	"imgs" => count($imgs)
);

header('Content-type: application/json');
echo json_encode($response);
