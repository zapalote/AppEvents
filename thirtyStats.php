<?php
// ----------------- 30 DAYS ------------------
function thirtyStats() {
	// last 30 days stats
	global $db, $log_table;

	$sql = "select sum(u) from (
				select count(distinct INET_NTOA(src)) as u from {$log_table}
					where upd >= date_sub(now(),interval 30 day) and src != 0
				union
				select count(distinct INET6_NTOA(src6)) as u from {$log_table}
					where upd >= date_sub(now(),interval 30 day) and src6 is not null
			) t";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$u = "(unique users: ".$e[0].")";
	}
	$txt = "in the last 30 days {$u}  <span class='btns'><a class='button' data-go=''>24 hrs</a> 
		<div class='button' data-go='ref'>referrals</div>
		<a class='button' data-go='m'>monthly</a></span>";

	$sql = "select d, sum(u), sum(c) from (
				select date(upd) as d, count(distinct INET_NTOA(src)) as u, count(*) as c from {$log_table}
					where upd >= date_sub(now(),interval 30 day) and src != 0 group by d desc
				union
				select date(upd) as d, count(distinct INET6_NTOA(src6)) as u, count(*) as c from {$log_table}
					where upd >= date_sub(now(),interval 30 day) and src6 is not null group by d desc
			) t group by d order by d desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$d[] = $e[0];
		$s[] = $e[1];
		$c[] = $e[2];
		$chart[$e[0]] = $e[1];
	}

	for($i = 0; $i < 30; $i++){
		$dd =  date("D j-M", strtotime('-'. $i .' days'));
	    $days[$dd] = 0;
	}
	ksort($chart);
	foreach ($chart as $k => $v) {
		$dd = date("D j-M", strtotime($k));
		$days[$dd] = $chart[$k];
		//unset($chart[$k]);
	}
	echo "<div class='chart-container'></div>\n";
	echo "<style>.sc-bar { padding:20px 40px 20px 80px }</style>\n";
	echo "<script>var labels = ['".implode("','", array_keys($days))."'];";
	echo "var values = ['".implode("','", array_values($days))."'];";
	echo "
		var wh = '300px';
		var ctype = 'column';
		if($(window).width() <= 480) {
			ctype = 'bar';
			wh = '100%';
		}
		labels = labels.reverse();
		values = values.reverse();
		$('.chart-container').simpleChart({
            title: { text: 'Sessions last 30 days', align: 'left' },
            type: ctype,
            layout: { width: '100%', height: wh },
            item: { label: labels, value: values, color: ['#00aeef'], labelInterval: 5,
                render: { margin: 0.2, size: 'relative' }
            }
		});
	</script>";

	printf("<h3>Sessions &mdash; {$txt}</h3><table id='stats' class='sttable'>\n");
	$total = 0; $users = 0;
	echo "<thead><tr><th data-sort='string'>▽ Date</th><th data-sort='int' data-sort-default='desc'>▽ Sessions</th>
		<th data-sort='int' data-sort-default='desc'>▽ #</th></tr></thead><tbody>\n";
	for ($i=0; $i< count($d); $i++) {
		$users += $s[$i];
		$total += $c[$i];
		$x = $d[$i];
		$day = date("D, j M", strtotime($x));
		printf("<tr class='drill-down' data-href='stats.php?d=%s'><td>%s</td><td>%d</td><td>%d</td></tr>\n", $x, $day, $s[$i], $c[$i]);
	}
	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td><td>%d</td></tr></tfoot></table>\n", $users, $total);
}
?>