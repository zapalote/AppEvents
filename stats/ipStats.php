<?php
// ----------------- SINGLE IP for Popup------------------
function ipPopup($l) {
	// list queries for a single ip address
	global $db, $log_table;

	$l = base64_decode($l);
	$sql = "select lex, acc from {$log_table} 
		where src=INET_ATON('{$l}') or src6=INET6_ATON('{$l}') 
		order by upd desc limit 10";
	$res = $db->query($sql);
	printf("<table id='popdata' class='sttable'>\n");
	while ($e = $res->fetch_row()) {
		$w[] = [$e[0], $e[1]];
	}

	printf("<thead><tr><th>#%s <span class='close'>&times;</span></th></tr></thead><tbody>\n", 
		anon($l));
	foreach ($w as $ww) {
		switch ($ww[1]) {
			case '0':
				$cl = '';
				break; // desktop
			case '1':
				$cl = 'color: green';
				break; // mobile
		}
		printf("<tr><td style='{$cl}'>{$ww[0]}</td></tr>");
	}
	printf("</tbody></table>\n");
}
// ----------------- SINGLE IP ------------------
function ipStats($l) {
	// list single ip address records
	global $db, $log_table;

	$l = base64_decode($l);
	$sql = "select lex, acc, upd from {$log_table} where src=INET_ATON('{$l}') or src6=INET6_ATON('{$l}') order by upd desc";
	$res = $db->query($sql);
	printf("<h3>Requests from %s  <span class='btns'><a class='button' href='#' onclick='window.history.back();return false;'>back</a></span></h3>
		<table id='stats' class='sttable'>\n", anon($l));
	$total = $res->num_rows;
	$chart = [];
	while ($e = $res->fetch_row()) {
		if(!isset($chart[substr($e[2], 0, 10)])){
			$chart[substr($e[2], 0, 10)] = 1;
		} else {
			$chart[substr($e[2], 0, 10)]++;
		}
		switch ($e[1]) {
			case '0': $acc = 'read'; break;
			case '1': $acc = 'edit'; break;
			case '2': $acc = 'not found'; break;
			case '3': $acc = 'read (!)'; break;
		}
		$w[] = $e[0];
		$a[] = $acc;
		$d[] = $e[2];
	}

	if (count($chart) > 1) {
		ksort($chart);
		foreach ($chart as $k => $v) {
			$dd = date("j-m", strtotime($k));
			$chart[$dd] = $chart[$k];
			unset($chart[$k]);
		}
		echo "<div class='chart-container'></div>\n";
		echo "<script>var labels = ['".implode("','", array_keys($chart))."'];";
		echo "var values = ['".implode("','", array_values($chart))."'];";
		echo "
		var inv = parseInt(labels.length/10) + 1;
		//console.log(inv);
		var wh = '300px';
		var ctype = 'column';
		if($(window).width() <= 480) {
			ctype = 'bar';
			wh = '100%';
		}
		$('.chart-container').simpleChart({
		title: { text: 'by date', align: 'left' },
		type: ctype,
		layout: { width: '100%', height: wh },
		item: { label: labels, value: values, color: ['#00aeef'], labelInterval: inv,
                render: { margin: 0.3, size: 'relative' }
            }
		});
	</script>";
	}

	echo "<thead><tr><th data-sort='string'>▽ Lema</th><th data-sort='string'>▽ Acc</th>
		<th data-sort='string' data-sort-default='desc'>▽ Time</th></tr></thead><tbody>\n";
	for ($i=0; $i< count($d); $i++) {
		printf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", $w[$i], $a[$i], $d[$i]);
	}

	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td><td></td></tr></tfoot></table>\n", $total);
}
?>