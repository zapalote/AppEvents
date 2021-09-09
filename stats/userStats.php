<?php
// ----------------- USAGE ------------------
function userStats() {
	// usage histogram
	global $db, $log_table;

	// histogram query
	$sql = "select
			bucket_floor,
			concat(bucket_floor, '—', bucket_ceiling) as bucket_name,
			count(*) as users
			from (
				select
				floor(c/5.00)*5 as bucket_floor,
				floor(c/5.00)*5 + 5 as bucket_ceiling
				from (
				    select INET_NTOA(src) as u, count(*) as c from {$log_table} 
				    	where upd >= date_sub(now(),interval 30 day) and src > 0 group by 1
					union
				    select INET6_NTOA(src6) as u, count(*) as c from {$log_table} 
				    	where upd >= date_sub(now(),interval 30 day) and src6 is not null 
				    	and INET6_NTOA(src6) not like '2a02:a450:8d4e:1:%' group by 1
				) t
			) a
			group by 1, 2
			order by 1";
	$res = $db->query($sql);
	$i = 0; $v = $l = $bucket = [];
	while ($e = $res->fetch_row()) {
		if($e[0]+1 > 50){
			$v[$i] += $e[2];
			$l[$i] = '> 50';
			$bucket['> 50'] += $e[2];
		} else {
			$v[$i] = $e[2];
			$l[$i] = $e[1];
			$bucket[$e[1]] = $e[2];
			$i++;
		}
	}
	echo "<div class='chart-container'></div>\n";

	echo "<script>var labels = ['".implode("','", $l)."'];";
	echo "var values = ['".implode("','", $v)."'];";
	echo "
		var wh = '300px';
		var ctype = 'column';
		if($(window).width() <= 480) {
			ctype = 'bar';
			wh = '100%';
		}
		$('.chart-container').simpleChart({
            title: { text: 'Users per hit range (last 30 days)',  align: 'left' },
            type: ctype,
            layout: { width: '100%', height: wh },
            item: { label: labels, value: values, color: ['#00aeef'], labelInterval: 1,
                render: { margin: 0.2, size: 'relative' }
            }
		});
	</script>";
	printf("<h3>Users per hit range (last 30 days) <span class='btns'><a class='button' href='stats.php'>back</a></span></h3><table id='stats' class='sttable'>\n");
	$total = 0; $users = 0;
	echo "<thead><tr><th>hits</th><th>users</th></tr></thead><tbody>\n";
	foreach ($bucket as $key => $val) {
		$total += $val;
		printf("<tr><td>%s</td><td>%d</td></tr>\n", $key, $val);
	}
	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td></tr></tfoot></table>\n", $total);

	# power users 30 days
	$sql = "select u, c from (
			    select INET_NTOA(src) as u, count(*) as c from {$log_table} where upd >= date_sub(now(),interval 30 day) and src > 0 group by 1
				union
			    select INET6_NTOA(src6) as u, count(*) as c from {$log_table} where upd >= date_sub(now(),interval 30 day) and src6 is not null
			    and INET6_NTOA(src6) not like '2a02:a450:8d4e:1:%'
			    group by 1
			) t where c > 50
			order by 2 desc";

	$res = $db->query($sql);

	echo "<h3>&nbsp;</h3><h3>Power users (>50 hits) last 30 days <span class='btns'><a class='button' href='stats.php'>back</a></span></h3><table id='stats' class='sttable'>\n";
	$users = 0;
	echo "<thead><tr><th data-sort='string'>▽ user</th><th data-sort='int' data-sort-default='desc'>▽ hits</th></tr>
			</thead><tbody>\n";
	while ($e = $res->fetch_row()) {
		$users++;
		printf("<tr><td class='drill-down' data-href='stats.php?ip=%s'>%s</td><td>%d</td></tr>\n", $e[0], anon($e[0]), $e[1]);
	}
	printf("</tbody><tfoot><tr><td>%d users</td><td></td></tr></tfoot></table>\n", $users);

	# power users since start
	$sql = "select u, c from (
			    select INET_NTOA(src) as u, count(*) as c from {$log_table} where src > 0 group by 1
				union
			    select INET6_NTOA(src6) as u, count(*) as c from {$log_table} where src6 is not null 
			    and INET6_NTOA(src6) not like '2a02:a450:8d4e:1:%'
			    group by 1
			) t where c > 50
			order by 2 desc";

	$res = $db->query($sql);

	echo "<h3>&nbsp;</h3><h3>Power users (>50 hits) since Jan 2017 <span class='btns'><a class='button' href='stats.php'>back</a></span></h3><table id='stats' class='sttable'>\n";
	$users = 0;
	echo "<thead><tr><th data-sort='string'>▽ user</th><th data-sort='int' data-sort-default='desc'>▽ hits</th></tr>
			</thead><tbody>\n";
	while ($e = $res->fetch_row()) {
		$users++;
		printf("<tr><td class='drill-down' data-href='stats.php?ip=%s'>%s</td><td>%d</td></tr>\n", $e[0], anon($e[0]), $e[1]);
	}
	printf("</tbody><tfoot><tr><td>%d users</td><td></td></tr></tfoot></table>\n", $users);
}
?>