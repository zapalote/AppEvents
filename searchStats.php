<?php
// ----------------- SEARCHES ------------------
function searchStats() {
	global $db, $log_table;

	# most queried last 30 days
	$sql = "select l, sum(c) from (
				select lex as l, count(*) as c from {$log_table} 
				where upd >= date_sub(now(),interval 30 day) and src > 0 group by l
			union
				select lex as l, count(*) as c from {$log_table} 
				where upd >= date_sub(now(),interval 30 day) and src6 is not null and INET6_NTOA(src6) not like '2a02:a450:8d4e:1:%' 
				group by l
			) t group by l";

	$res = $db->query($sql);
	$words = [];
	while ($e = $res->fetch_row()) {
		$word = preg_replace('/([a-z\ ]+), (het|de).*/', '$1', $e[0]);
		if(isset($words[$word])){
			$words[$word] += $e[1];
		} else {
			$words[$word] = $e[1];
		}
	}
	arsort($words);

	echo "<h3>&nbsp;</h3><h3>Top 10 searches last 30 days <span class='btns'><div class='button' data-go=''>back</div></span></h3><table id='stats' class='sttable'>\n";
	echo "<thead><tr><th data-sort='string'>▽ word</th><th data-sort='int' data-sort-default='desc'>▽ searches</th></tr>
			</thead><tbody>\n";

	$i = 0;
	foreach ($words as $k => $w){
		if($i++ > 10) { break; }
		printf("<tr><td>%s</td><td>%d</td></tr>\n", $k, $w);
	}	
	printf("</tbody><tfoot><tr><td></td><td></td></tr></tfoot></table>\n");

	# most queried words
	$sql = "select l, sum(c) from (
				select lex as l, count(*) as c from {$log_table} 
				where src > 0 group by l
			union
				select lex as l, count(*) as c from {$log_table} 
				where src6 is not null and INET6_NTOA(src6) not like '2a02:a450:8d4e:1:%' group by l
			) t group by l";

	$words = [];
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$word = preg_replace('/([a-z\ ]+), (het|de).*/', '$1', $e[0]);
		if(isset($words[$word])){
			$words[$word] += $e[1];
		} else {
			$words[$word] = $e[1];
		}
	}
	arsort($words);

	echo "<h3>&nbsp;</h3><h3>Most searched words (>50) <span class='btns'><div class='button' data-go=''>back</div></span></h3><table id='stats' class='sttable'>\n";
	echo "<thead><tr><th data-sort='string'>▽ word</th><th data-sort='int' data-sort-default='desc'>▽ searches</th></tr>
			</thead><tbody>\n";

	foreach ($words as $k => $w){
		if($w < 50) { break; }
		printf("<tr><td>%s</td><td>%d</td></tr>\n", $k, $w);
	}	
	printf("</tbody><tfoot><tr><td>%d words</td><td></td></tr></tfoot></table>\n", count($words));

}
?>