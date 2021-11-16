<?php
// ----------------- MONTHLY ------------------
function monthlyStats() {
	// monthly stats
	global $db, $log_table;

	$sql = "select sum(u) from (
				select count(distinct INET_NTOA(src)) as u from {$log_table}
					where src != 0
				union
				select count(distinct INET6_NTOA(src6)) as u from {$log_table}
					where src6 is not null
			) t";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$u = $e[0];
	}
	$month_beg = date('Y-m-01', time());
	$months18 = date('Y-m-d', strtotime("-17 months", strtotime($month_beg)));
	$sql = "select d, sum(u), sum(c) from (
				select date_format(upd, '%Y-%m') as d, count(distinct INET_NTOA(src)) as u, count(*) as c from {$log_table}
					where src != 0 and upd > '{$months18}' group by d desc
				union
				select date_format(upd, '%Y-%m') as d, count(distinct INET6_NTOA(src6)) as u, count(*) as c from {$log_table}
					where src6 is not null and upd > '{$months18}' group by d desc
			) t group by d order by d desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$d[] = $e[0];
		$s[] = $e[1];
		$c[] = $e[2];
		$chart[$e[0]] = $e[2];
	}

	ksort($chart);
	foreach ($chart as $k => $v) {
		$dd = date("M-y", strtotime($k));
		$chart[$dd] = $chart[$k];
		unset($chart[$k]);
	}
	$color = '#00aeef';
	$color = 'repeating-linear-gradient(45deg,#606dbc,#606dbc 10px,#465298 10px,#465298 20px);';
	echo "<div class='chart-container'></div>\n";
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
            title: { text: 'Word queries per month', align: 'left' },
            type: ctype,
            layout: { width: '100%', height: wh },
            item: { label: labels, value: values, color: ['{$color}'], labelInterval: 1,
                render: { margin: 0.2, size: 'relative' }
            }
		});
	</script>";
	
	$month = date('F');
	$fc = forecastMonth();
	$txt = "{$u} &mdash; forecast {$month} {$fc} <span class='btns'><a class='button' data-go=''>24 hrs</a></span>";

	printf("<h3>Total users {$txt}</h3><table id='stats' class='sttable'>\n");
	$total = 0; $users = 0;
	echo "<thead><tr><th data-sort='string'>▽ Month</th><th data-sort='int' data-sort-default='desc'>▽ Users</th>
		<th data-sort='int' data-sort-default='desc'>▽ #</th></tr></thead><tbody>\n";
	for ($i=0; $i< count($d); $i++) {
		$users += $s[$i];
		$total += $c[$i];
		$x = $d[$i];
		printf("<tr class='drill-down' data-href='stats.php?md=%s'><td>%s</td><td>%d</td><td>%d</td></tr>\n", $x, $x, $s[$i], $c[$i]);
	}
	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td><td>%d</td></tr></tfoot></table>\n", $users, $total);

	$sql = "select round(((data_length + index_length) / 1024 / 1024), 2)
		from information_schema.tables where table_name = 'log'";
	$res = $db->query($sql);
	$e = $res->fetch_row();
	printf("<h3>Log size: %.2fMB;</h3>", $e[0]);

}

function forecastMonth(){
	global $db, $log_table;

	#echo "<pre>";
	$da = "upd >= date_sub(curdate(), interval 30 day)";
	$sql = "select d, sum(c) from (
				select date(upd) as d, count(*) as c from {$log_table}
					where {$da} and src != 0 group by d desc
				union
				select date(upd) as d, count(*) as c from {$log_table}
					where {$da} and src6 is not null group by d desc
			) t group by d order by d asc";
	$res = $db->query($sql);
	$days = 0; $wd = 0; $we = 0; $weavg = $wdavg = 0;
	$words = array_fill(0, 7, 0);
	while ($e = $res->fetch_row()) {
		$day = date("w", strtotime($e[0]));
		$words[$day] += $e[1];
		#echo "{$e[0]}\t{$day}\t{$words[$day]}\n";		
		if($day == 0 || $day == 6) { $we++; }
		else { $wd++; }
		$days++;
	}

	foreach ($words as $day => $v) {
		if($day == 0 || $day == 6){ 
			$weavg += $v;
		} else {
			$wdavg += $v;
		}
	}
	$weavg /= $we;
	$wdavg /= $wd;
	$days_in_month = date("t");
	$wemonth = 8;
	$wdmonth = $days_in_month - $wemonth;
	#echo "\n\n{$weavg}\t{$wdavg}\t{$days_in_month}";
	$forecast = round($weavg * $wemonth + $wdavg * $wdmonth);
	#echo "\n\n{$forecast}";
	return $forecast;	
}
?>