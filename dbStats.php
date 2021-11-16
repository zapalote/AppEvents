<?php
// ----------------- BACKUPS ------------------
function bckStats() {
	// retrieve the backup log
	global $db, $log_table;

	echo "<h3>Backups  <span class='btns'><div class='button' data-go=''>back</div>
		<a class='button' data-go='e'>edits</a></span></h3>";
	// get log and split it in entries
	$log = file_get_contents('backup.log');
	$out = explode("#", $log);
	echo "<table id='stats' class='sttable'><thead><tr><th></th></tr></thead><tbody>";
	foreach ($out as $l) {
		if (!strlen($l)) continue;
		$l = preg_replace('/[\-]{5,}/', '', $l);
		$l = preg_replace('/\n/m', '<br />', $l);
		printf("<tr><td>%s</td></tr>", $l);
	}
	echo "</tbody><tfoot><tr><td></td></tr></tfoot></table>";
}

// ----------------- DB ------------------
function dbStats() {
	// db stats
	global $db, $log_table;

	$last = deriveDay(filemtime("backup.log"));
	echo "<h3>Last backup: {$last} <span class='btns'><div class='button' data-go=''>back</div></span></h3>";
	// get log and split it in entries
	$log = file_get_contents('backup.log');
	$out = explode("#", $log);
	echo "<table id='log'><tbody>";
	$l = $out[1];
	$l = preg_replace('/[\-]{5,}/', '', $l);
	$l = preg_replace('/\n/m', '<br />', $l);
	printf("<tr class='drill-down' data-href='bck'><td>%s</td></tr>", $l);
	echo "</tbody></table>";

	// get intruders, if any
	xipStats("log");

	$sql = "select round(((data_length + index_length) / 1024 / 1024), 2)
		from information_schema.tables where table_name = 'log'";
	$res = $db->query($sql);
	$e = $res->fetch_row();
	$size = sprintf("%.2f",  $e[0]);
	$sql = "select left(lex, 1), count(*), max(upd) from lexems group by left(lex, 1) order by max(upd) desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
		$lex[$e[0]] = $e[1];
		$upd[$e[0]] = $e[2];
	}

	$bck = "<a class='button' style='left:0;' data-go='s'>searches</a> 
		<a class='button' data-go='n'>not found</a> <a class='button' data-go='u'>usage</a>
		<a class='button' data-go='e'>edits</a>";
	printf("<h3>Log size: %sMB; <span class='btns'>%s</span></h3>", $size, $bck);
	$lexems = 0;
	$verba = 0;
	$nieuw = 0;
	echo "<table id='stats' class='sttable'><thead><tr><th data-sort='string'>▽ letter</th><th data-sort='int'>▽ lema</th>
		<th data-sort='int'>▽ verba</th><th data-sort='int'>▽ nieuw<sup>+</sup></th>
		<th class='fulldate' data-sort='string'>▽ updated</th></tr>
		</thead><tbody>";
	foreach ($lex as $x => $cnt) {
		if ($x == '') continue;

		$sql = "select json from lexems where lex like '{$x}%'";
		$res = $db->query($sql);
		$v = 0; $w = 0;
		while ($e = $res->fetch_row()) {
			preg_match_all('|<p class[^>]+>(.*)</p>|U', $e[0], $m, PREG_PATTERN_ORDER);
			foreach ($m[1] as $verb) {
				$verb = strip_tags($verb);
				if (preg_match('|^[1-9A-TV-Z]|', $verb)) continue;
				$v += count(explode('/', $verb));
			}
			$w += substr_count($e[0], '<sup>+</sup>');
		}
		printf("<tr class='drill-down' data-href='stats.php?lex=%s'><td>%s</td><td>%d</td><td>%d</td><td>%d</td><td>%s</td></tr>",
			$x, $x, $cnt, $v, $w, $upd[$x]);
		$verba += $v;
		$nieuw += $w;
		$lexems += $cnt;
	}
	printf("</tbody><tfoot><tr><td>totaal</td><td>%d</td><td>%d</td><td>%d</td><td></td></tr></tfoot></table>", $lexems, $verba, $nieuw);
	
	printf("<h3>Your IP: %s</h3>", getUserIpAddr());

}

// ----------------- XIP: Not allowed ------------------
function xipStats($l) {
	// list not allowed ip address records
	global $db, $log_table;

	$span = ($l == "log")? "and upd >= date_sub(now(),interval 1 day)" : ""; 
	$sql = "select INET_NTOA(src), INET6_NTOA(src6), upd from {$log_table} 
		where acc='4' {$span} order by upd desc";
	$res = $db->query($sql);
	$total = $res->num_rows;

	if($l == "log" && $total == 0) return;

	printf("<h3>Not allowed <span class='btns'><a class='button' data-go=''>back</a></span></h3>
		<table id='stats' class='sttable'>\n");
	while ($e = $res->fetch_row()) {
		$w[] = ($e[0])? $e[0] : $e[1];
		$d[] = $e[2];
	}

	echo "<thead><tr><th data-sort='string'>▽ IP</th>
		<th data-sort='string' data-sort-default='desc'>▽ Time</th></tr></thead><tbody>\n";
	for ($i=0; $i< count($d); $i++) {
		printf("<tr><td>%s</td><td>%s</td></tr>", $w[$i], $d[$i]);
	}

	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td></tr></tfoot></table>\n", $total);
}
// ------------ single letter stats ------------------
function lexStats($l) {
	// single letter stats
	global $db, $log_table;

	$q = mb_strtolower(substr($l, 0, 1));
	if (!preg_match("/[a-z]/", $q)) {
		$q = 'a';
	}
	$sql = "select lex, json, upd from lexems where lex like '{$q}%' order by upd desc";
	$res = $db->query($sql);
	printf("<h3>Letter ".mb_strtoupper($q)." &mdash; %s lema  <span class='btns'>
		<a class='button' data-go=''>back</a></span></h3>
		<table id='stats' class='sttable'>\n", $res->num_rows);
	$total = 0; $new = 0;
	$n = 0;
	echo "<thead><tr><th data-sort='string'>▽ lexem</th><th data-sort='int'>▽ verba</th><th data-sort='int'>▽ nieuw<sup>+</sup></th>
		<th data-sort='string'>▽ update</th></tr></thead><tbody>\n";
	while ($e = $res->fetch_row()) {

		if (preg_match_all('|<p class[^>]+>(.*)</p>|U', $e[1], $out, PREG_PATTERN_ORDER)) {
			$v = 0; $w = 0;
			$pop = "<div id='i".$n."' class='slideout'><span class='close'>x</span><h2>".$e[0]."</h2>\n";
			foreach ($out[1] as $verb) {
				$pop .= "<p>".$verb."</p>\n";
				$verb = strip_tags($verb);
				if (preg_match('|^[1-9A-TV-Z]|', $verb)) continue;
				$v += count(explode('/', $verb));
			}
			$w += substr_count($e[1], '<sup>+</sup>');
			$pop .= "</div>\n";
			$total += $v;
			$new += $w;
			printf("<tr class='poppable' data-id='i%s'><td>%s</td><td>%d</td><td>%d</td><td>%s</td></tr>\n%s\n",  $n, $e[0], $v, $w, $e[2], $pop);
			$n++;
		} else {
			printf("<tr><td>%s</td><td>0</td><td>0</td><td>%s</td></tr>\n", $e[0], $e[2]);
		}
	}
	printf("</tbody><tfoot><tr><td>Total</td><td>%d</td><td>%d</td><td></td></tr></tfoot></table>\n", $total, $new);
}
?>