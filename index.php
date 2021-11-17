<?php

require_once('config.php');
$title = STATS_TITLE;

preg_match('/(lex|db|ip|d|n|ua|e|30|u|md|m|bck|s|xip|pip)=?(.*)/', $_SERVER['QUERY_STRING'], $arg);
$q = ($arg) ? $arg[1] : "";
$l = ($arg) ? $arg[2] : "";
$query = ($q) ? "?" . $_SERVER['QUERY_STRING'] : '';

?>
<!DOCTYPE html>
<html>

<head>
	<title><?php echo $title ?></title>
	<link href="stats.css" rel="stylesheet" type="text/css" />
	<script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
	<script src="stupidtable.min.js" type="text/javascript"></script>
	<link rel="stylesheet" href="simple-chart.css">
	<script src="simple-chart.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		$(document).ready(function() {
			// const query = '<?php echo $query ?>';
			go();
		});
		let currentPop = null;

		function popUp(sec, url) {
			if (currentPop) {
				popClose(currentPop);
			}
			$.get(url, function(response) {
				$(sec).html(response);
			});
			$(sec).click(function() {
				popClose(sec);
			})
			$(sec).fadeToggle();
			currentPop = sec;
		}
		function popClose(sec) {
			$(sec).fadeToggle();
			$(sec).prop("onclick", null).off("click");
			currentPop = null;
		}

		function go(dest) {
			query = (dest) ? '?' + dest : '';
			console.log('dest ', query);
			$(".loading").show();
			var showData = $('#data');
			showData.empty();
			$.get(`getStatsData.php${query}`, function(data) {
					showData.append(data);
					$(".loading").hide();
					$(".drill-down").click(function() {
						const p = $(this).data("pop");
						if (p) {
							popUp('#popup', 'getStatsData.php?pip=' + btoa(p));
						} else {
							go($(this).data("href"));
						}
					});
			$(".button").click(function(){
					go($(this).data("go"));
			});
			$(".poppable").click(function() {
					var p = "#" + $(this).data("id");
					$(p).toggle();
			});
			$('.slideout').click(function() {
					$(this).hide();
			});
			$('.close').click(function() {
					$(this).parent().hide();
			});
			// sortable cols
			$(".sttable").stupidtable();
			});
		}
	</script>
</head>

<body>
	<div class="loading"><img src="loading.gif" /></div>
	<div id="popup"></div>
	<div class="container">
		<h2><?php echo $title ?></h2>
		<div id="data"></div>
		<footer class="foot">
			<p><?php echo STATS_ID ?></p>
		</footer>
	</div>
	<link href="https://fonts.googleapis.com/css?family=Neuton:400,700,Lato:400" rel="stylesheet" type="text/css" />
</body>

</html>