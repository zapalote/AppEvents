<?php
// ----------------- 24 HOURS ------------------
function twentyfourStats($q, $l) {
	// last 24 hours stats
	global $db, $log_table;

	$dq = ($l)? "date(upd) = '{$l}'" : "upd >= date_sub(now(),interval 23 hour)";
	$dt = ($l)? "on {$l}" : "(last 24 hours)";
	$txt = "{$dt} <span class='btns'><a class='button' data-go='30'>30 days</a> <div class='button' data-go='ref'>referrals</div> <a class='button' data-go='s'>topics</a> <a class='button' data-go='m'>montlhy</a></span>";

	$sql = "select ip, cnt, u from (
				select INET_NTOA(src) as ip, count(*) as cnt, max(upd) as u from {$log_table} 
				where {$dq} and src != 0 
				group by ip
				union
				select INET6_NTOA(src6) as ip, count(*) as cnt, max(upd) as u from {$log_table} 
				where {$dq} and src6 is not null 
				group by ip
			) t order by u desc";
	$res = $db->query($sql);

	for($i = 0; $i < 24; $i++){
		$dhs= date("Y-m-d H:0:0", strtotime('-'.$i.' hours'));
		$hh = date("H", strtotime('-'.$i.' hours'));
		$chart[$hh] = 0;
	} 

	while ($e = $res->fetch_row()) {
		$ips[] = $e[0];
		$c[] = $e[1];
		$upd[] = $e[2];
		$ddd = substr($e[2], 11,2);
		$chart[$ddd] += $e[1];
	}
	$n = $res->num_rows;
	if(!$n) {
		return false;
	}

	$last = deriveDay(strtotime($upd[0]));
	echo "<div class='chart-container'></div>\n";
	echo "<script>var labels = ['".implode("','", array_keys($chart))."'];";
	echo "var values = ['".implode("','", array_values($chart))."'];";
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
            title: { text: 'Queries $last', align: 'left' },
            type: ctype,
            layout: { width: '100%', height: wh },
            item: { label: labels, value: values, color: ['#00aeef'], labelInterval: 1,
                render: { margin: 0.2, size: 'relative' }
            }
		});
	</script>";

	printf("<h3>Sessions &mdash; %s {$txt}</h3><table id='stats' class='sttable'>\n", $n);
	$total = 0; $users = 0;
	echo "<thead><tr><th data-sort='string' class='ip'>▽ Session</th><th data-sort='int' data-sort-default='desc'>▽ #</th>
		<th data-sort='string'>▽ Last</th></tr></thead><tbody>\n";
	$sep = 1; $u = date('Y-m-d');
	$i = 0;
	foreach ($ips as $ip) {
		$users++;
		$total += $c[$i];
		$class = '';
		if ($sep && strpos($upd[$i], $u) === false) {
			$sep = 0;
			$class = "sep";
		}
		$ses = anon($ip);
		printf("<tr class='drill-down %s' data-pop='%s'><td class='ip'>%s</td><td>%d</td><td>%s</td></tr>\n", 
			$class, $ip, $ses, $c[$i], substr($upd[$i], 11));

		$i++;
	}
	printf("</tbody><tfoot><tr><td>Sessions: %d</td><td>%d</td><td></td></tr></tfoot></table>\n", $users, $total);

	return true;
}
?>