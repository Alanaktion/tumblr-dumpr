<?php
	session_start();
	if(empty($_SESSION['token'])) {
		$_SESSION['token'] = uniqid();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Tumblr Dumpr</title>
	<meta name="description" value="A simple photo export tool for Tumblr">
	<meta name="author" value="Alan Hardman <alan@phpizza.com>">
	<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/darkly/bootstrap.min.css">
	<link rel="alternate stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/flatly/bootstrap.min.css">
	<link rel="alternate stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
	<style type="text/css">
	body {
		margin-top: 15px;
	}
	@media (min-width: 768px) {
		.form-inline .form-control {
			min-width: 320px;
		}
	}
	#msg {
		margin-top: 15px;
	}
	footer {
		color: #777;
		color: rgba(255, 255, 255, 0.5);
		font-size: 11px;
	}
	.nolink:not(:hover):not(:focus) {
		color: inherit;
	}

	/* Tumblr colors */
	body,
	.navbar-default,
	.btn-primary {
		background-color: #36465D;
	}
	.jumbotron {
		background-color: #fff;
		color: #444;
	}
	.jumbotron .form-control {
		border: 2px solid #A1A1A1;
	}
	.jumbotron .form-control:focus {
		border-color: #36465D;
	}
	</style>
</head>
<body>
	<div class="container">
		<nav class="navbar navbar-default hidden-xs">
			<a class="navbar-brand" href=".">Tumblr Dumpr</a>
		</nav>

		<div class="jumbotron">
			<h1>Tumblr Dumpr</h1>
			<p>Easily download photos from a Tumblr blog</p>
			<br>

			<form class="form-inline text-center" id="frm-dump" action="get-blog.php" method="post">
				<div class="form-group form-group-lg">
					<label for="blog" class="sr-only">Blog Domain</label>
					<input type="text" class="form-control" name="blog" id="blog" placeholder="alanaktion.tumblr.com" autocomplete="off" required autofocus>
				</div>
				<button type="submit" class="btn btn-primary btn-lg">Start Download</button>
			</form>

			<p class="text-center hidden" id="msg">
				Starting download&hellip;
			</p>
		</div>

		<footer>
			&copy; <a href="//phpizza.com" class="nolink">Alan Hardman</a> <?php echo date('Y'); ?>&ensp;&middot;&ensp;All photos &copy; their respective owners
		</footer>
	</div>

	<div class="hidden" id="progress-bar">
		<div class="progress">
			<div class="progress-bar" role="progressbar" style="width: 60%;"></div>
		</div>
	</div>

	<script type="text/javascript" src="//code.jquery.com/jquery-1.11.1.min.js"></script>
	<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
	<script type="text/javascript">
		// Popular blogs
		var blogs = ['tldrwikipedia', 'ghostphotographs', 'ifpaintingscouldtext', 'willitbeard', 'twitterthecomic', 'comedycentral', 'theworstroom', 'bridesthrowingcats', 'yachtcats', 'thischarmingcharlie', 'moviecode', 'worstcats', 'popsonnet', 'museumofselfies'];

		// Dumpr core class
		var Dumpr = {
			images: 0,
			posts: 0,
			getBlogAjax: function(data) {
				if(!data) {
					data = {
						blog: $('#blog').val(),
						offset: 0
					};
				}
				$.post('get-blog.php', data, function(data) {
					Dumpr.images += data.imgs;
					Dumpr.posts += data.posts;
					if(data.status == 'downloading') {
						// Update progress and continue download
						$('#progress-bar .progress-bar').css('width', ((data.offset + data.posts) / data.total_posts * 100) + '%');
						$('#msg').html($('#progress-bar').html());
						Dumpr.getBlogAjax(data);
					} else if(data.status == 'complete') {
						// Show completed status
						$('#msg').html('Download ready!&ensp;Posts: ' + Dumpr.posts + ' Images: ' + Dumpr.images + '<br>');
						$('<a />').attr('href', 'dl-zip.php?blog=' + encodeURIComponent($('#blog').val())).addClass('btn btn-success').text('Download Zip').appendTo('#msg');
						$('#blog').prop('disabled', false);
					}
				}, 'json');
			}
		};

		$(document).ready(function() {

			// Handle form submission
			$('#frm-dump').submit(function(e) {
				e.preventDefault();
				Dumpr.getBlogAjax();
				$('#blog').prop('disabled', true);
				$('#msg').removeClass('hidden');
			});

			// Rotate blog placeholders
			window.setInterval(function() {
				val = blogs[Math.floor(Math.random() * blogs.length)];
				$('#blog').attr('placeholder', val + '.tumblr.com');
			}, 2000);

		});
	</script>
</body>
</html>
