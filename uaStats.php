<?php
// ----------------- UA Stats ------------------
function uaStats() {
	// mobile vs desktop
	global $db, $log_table;

	$sql = "select date(upd) as d, count(*) as c from {$log_table} 
		where upd >= date_sub(now(),interval 30 day)
		group by d order by d desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$total[$e[0]] = $e[1];
	}

	$sql = "select date(upd) as d, count(*) as c from {$log_table} 
		where upd >= date_sub(now(),interval 30 day) and acc='1'
		group by d order by d desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$x = (int)(($e[1]/$total[$e[0]])*100);
		$pct[$e[0]] = $e[1];
		$chart[$e[0]] = $x;
	}

	ksort($chart);
	foreach ($chart as $k => $v) {
		$dd = date("D j-M", strtotime($k));
		$chart[$dd] = $chart[$k];
		unset($chart[$k]);
	}
	echo "<div class='chart-container'></div>\n";
	echo "<style>.sc-bar { padding:20px 40px 20px 80px }</style>\n";
	echo "<script>var labels = ['".implode("','", array_keys($chart))."'];";
	echo "var values = ['".implode("','", array_values($chart))."'];";
	echo "
		var wh = '300px';
		var ctype = 'column';
		if($(window).width() <= 480) {
			ctype = 'bar';
			wh = '100%';
			labels = labels.reverse();
			values = values.reverse();
		}
		$('.chart-container').simpleChart({
            title: { text: 'Mobile ratios (%) last 30 days', align: 'left' },
            type: ctype,
            layout: { width: '100%', height: wh },
            item: { label: labels, value: values, color: ['#00aeef'], labelInterval: 5,
                render: { margin: 0.2, size: 'relative' }
            }
		});
	</script>";

	printf("<h3>Mobile UA ratios <span class='btns'><div class='button' data-go=''>back</div></span></h3><table id='stats' class='sttable'>\n");
	$tot = 0; $nft = 0;
	echo "<thead><tr><th data-sort='string'>▽ Date</th><th data-sort='int' data-sort-default='desc'>▽ Hits</th>
		<th data-sort='int' data-sort-default='desc'>▽ Mobile</th></tr></thead><tbody>\n";
	foreach ($total as $d => $val) {
		$x = $pct[$d];
		$tot += $val;
		$nft += $x;
		$day = date("D, j M", strtotime($d));
		printf("<tr class='drill-down' data-href='stats.php?d=%s'><td>%s</td><td>%d</td><td>%d</td></tr>\n", $d, $day, $val, $x);
	}
	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td><td>%d</td></tr></tfoot></table>\n", $tot, $nft);

	$sql = "select lex as sub, count(*) as c 
			from {$log_table} where acc='1' and upd >= date_sub(now(), interval 1 year)
			group by sub
			having c >= 20
			order by c desc";
	$res = $db->query($sql);
	echo "<h3>Top hits from mobile (>20) <span class='btns'><div class='button' data-go=''>back</div></span></h3>
			<table id='stats' class='sttable'>\n";
	echo "<thead><tr><th data-sort='string'>▽ Hits</th><th data-sort='int'>▽ Counts</th></tr>
			</thead><tbody>\n";
	while ($e = $res->fetch_row()) {
		printf("<tr><td>%s</td><td>%s</td></tr>", $e[0], $e[1]);
	}
	echo "</tbody><tfoot><tr><td></td><td></td></tr></tfoot></table>\n";

	// list the actual data	
	// mobile
	$sql = "select INET_NTOA(src), INET6_NTOA(src6), lex, upd
			from {$log_table} where acc='1' and upd >= date_sub(now(),interval 30 day)
			order by upd desc";
	$res = $db->query($sql);
	echo "<h3>Mobile last 30 days <span class='btns'><div class='button' data-go=''>back</div></span></h3>
			<table id='stats' class='sttable'>\n";
	$total = $res->num_rows;
	echo "<thead><tr><th data-sort='string' class='ip'>▽ User</th><th data-sort='string'>▽ Hits</th><th data-sort='string'>▽ Time</th></tr>
			</thead><tbody>\n";
	while ($e = $res->fetch_row()) {
		$ip = ($e[1])? $e[1]: $e[0];
		printf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", anon($ip), $e[2], $e[3]);
	}
	echo "</tbody><tfoot><tr><td></td><td></td><td></td></tr></tfoot></table>\n";
}
?>