<?php
// ----------------- Edits ------------------
function editStats($q) {
	// edit statistics
	global $db, $log_table;

	$sql = "select date(upd) as d, count(*) as c from {$log_table} 
		where upd >= date_sub(now(),interval 30 day) and acc='1'
		group by d order by d desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$chart[$e[0]] = $e[1];
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
            title: { text: 'Woordenboek updates last 30 days', align: 'left' },
            type: ctype,
            layout: { width: '100%', height: wh },
            item: { label: labels, value: values, color: ['#00aeef'], labelInterval: 5,
                render: { margin: 0.2, size: 'relative' }
            }
		});
	</script>";

	// list the actual data	
	$a = ($q == 'n')? 2 : 1;
	$t = ($q == 'n')? 'not found' : 'edits';
	// not found or edits
	$sql = "select lex, upd
			from {$log_table} where acc='1' and upd >= date_sub(now(),interval 30 day)
			order by upd desc";
	$res = $db->query($sql);
	echo "<h3>Lema edits <span class='btns'><a class='button' data-go=''>back</a></span></h3>
			<table id='stats' class='sttable'>\n";
	$total = $res->num_rows;
	echo "<thead><tr><th data-sort='string'>▽ Lema</th><th data-sort='string'>▽ Time</th></tr>
			</thead><tbody>\n";
	while ($e = $res->fetch_row()) {
		printf("<tr><td>%s</td><td>%s</td></tr>", $e[0], $e[1]);
	}
	echo "</tbody><tfoot><tr><td></td><td></td></tr></tfoot></table>\n";
}
?>