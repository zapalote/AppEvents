<?php
mb_internal_encoding("UTF-8");
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
// Connect to the database
require_once('config.php');
global $db, $log_table;

preg_match('/(d|30|md|m|s|pip|ref)=?(.*)/', $_SERVER['QUERY_STRING'], $arg);
$q = ($arg) ? $arg[1] : "";
$l = ($arg) ? $arg[2] : "";

// dispatcher
switch ($q) {
  case 'pip':
    ipPopup($l);
    break;
  case '30':
    // sessions stats 30 days
    thirtyStats('');
    break;
  case 'm':
    // user stats per month
    monthlyStats();
    break;
  case 'md':
    // user stats per month details
    thirtyStats($l);
    break;
  case 's':
    // search stats
    searchStats();
    break;
  case 'ref':
    referStats();
    break;
  case 'd':
  default:
    // 24hrs or given date
    twentyfourStats($l);
}

$db->close();


// ----------------- SEARCHES ------------------
function searchStats() {
  global $db, $log_table;

  # most clicked last 30 days
  $sql = "select lex as l, count(*) as c from {$log_table} 
				where upd >= date_sub(now(),interval 30 day) and acc != 9 group by l order by c desc";

  //  limit the results size to max_rows due to react mem-overflow
  $max_rows = 300;
  $res = $db->query($sql);
  $words = [];
  while ($e = $res->fetch_row()) {
    if ($max_rows-- == 0) break;
    $word = $e[0];
    if (isset($words[$word])) {
      $words[$word] += $e[1];
    } else {
      $words[$word] = $e[1];
    }
  }
  $res->close();
  arsort($words);

  # most clicked events
  $sql = "select lex as l, count(*) as c from {$log_table} 
				where acc != 9 group by l order by c desc";

  $all = [];
  $max_rows = 300;
  $res = $db->query($sql);
  while ($e = $res->fetch_row()) {
    if ($max_rows-- == 0) break;
    $word = $e[0];
    if (isset($all[$word])) {
      $all[$word] += $e[1];
    } else {
      $all[$word] = $e[1];
    }
  }
  arsort($all);

  $results = array(
    "top30days" => $words,
    "topall" => $all
  );
  echo json_encode($results);
}

// ----------------- REFERRALS ------------------
function referStats() {
  // referral stats
  global $db, $log_table;
  // referrals last 24 hrs 
  $sql = "select refer, landing, upd from {$log_table} 
				where upd >= date_sub(now(),interval 23 hour) and refer is not null and acc != 9
        order by upd desc";
  $res = $db->query($sql);

  $refs = [];
  $land = [];
  $upd = [];
  while ($e = $res->fetch_row()) {
    $refs[] = $e[0];
    $land[] = $e[1];
    $upd[] = $e[2];
  }

  $t = isset($upd[0])? $upd[0] : 'today midnight';
  $last = deriveDay(strtotime($t));

  # referrals ranking 30 days
  $sql = "select refer as r, count(*) as c from {$log_table} 
				where refer is not null and acc != 9 and upd >= date_sub(now(),interval 30 day) group by r order by c desc";

  $res = $db->query($sql);
  $allrefs = [];
  while ($e = $res->fetch_row()) {
    $allrefs[$e[0]] = $e[1];
  }

  //  robots last 24 hrs
  $sql = "select refer, landing, upd from {$log_table} 
				where upd >= date_sub(now(),interval 23 hour) and refer is not null and acc = 9
        order by upd desc";
  $res = $db->query($sql);

  $bots = [];
  $botsland = [];
  $botsupd = [];
  while ($e = $res->fetch_row()) {
    $bots[] = $e[0];
    $botsland[] = $e[1];
    $botsupd[] = $e[2];
  }

  $results = array(
    "last" => $last,
    "referrals" => $refs,
    "landing" => $land,
    "times" => $upd,
    "all" => $allrefs,
    "bots" => array("ref" => $bots, "land" => $botsland, "upd" => $botsupd)
  );
  echo json_encode($results);
}

// ----------------- MONTHLY ------------------
function monthlyStats() {
  // monthly stats
  global $db, $log_table;

  $month_beg = date('Y-m-01', time());
  $months18 = date('Y-m-d', strtotime("-17 months", strtotime($month_beg)));
  $sql = "select date_format(upd, '%Y-%m') as d, count(distinct INET6_NTOA(src)) as u, count(*) as c from {$log_table}
					where upd > '{$months18}' and acc != 9 group by d order by d desc";
  $res = $db->query($sql);
  while ($e = $res->fetch_row()) {
    $d[] = $e[0];
    $s[] = $e[1];
    $c[] = $e[2];
    $chart[$e[0]] = $e[2];
  }

  for ($i = 18; $i >= 0; $i--) {
    $dd =  date("M-y", strtotime('-' . $i . ' months'));
    $months[$dd] = 0;
  }

  foreach ($chart as $k => $v) {
    $dd = date("M-y", strtotime($k));
    $months[$dd] = $chart[$k];
  }

  $fc = forecastMonth();

  $sql = "select round(((data_length + index_length) / 1024 / 1024), 2)
		from information_schema.tables where table_name = 'log'";
  $res = $db->query($sql);
  $e = $res->fetch_row();

  $results = array(
    "months" => $d,
    "sessions" => $s,
    "hits" => $c,
    "forecast" => $fc,
    "chart" => $months,
    "logsize" => $e[0]
  );
  echo json_encode($results);
}

// ----------------- Forecast #sessions to month end -----------------
function forecastMonth() {
  global $db, $log_table;

  $da = "upd >= date_sub(curdate(), interval 30 day)";
  $sql = "select date(upd) as d, count(*) as c from {$log_table}
					where {$da} and acc != 9 group by d order by d asc";
  $res = $db->query($sql);
  $days = 0;
  $wd = 0;
  $we = 0;
  $weavg = $wdavg = 0;
  $words = array_fill(0, 7, 0);
  while ($e = $res->fetch_row()) {
    $day = date("w", strtotime($e[0]));
    $words[$day] += $e[1];
    if ($day == 0 || $day == 6) {
      $we++;
    } else {
      $wd++;
    }
    $days++;
  }

  foreach ($words as $day => $v) {
    if ($day == 0 || $day == 6) {
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
  return round($weavg * $wemonth + $wdavg * $wdmonth);
}

// ----------------- IP popup -----------------
function ipPopup($l){
  // list queries for a single ip address
  global $db, $log_table;

  $l = base64_decode($l);
  $sql = "select lex, acc from {$log_table} 
		where src=INET6_ATON('{$l}') order by upd desc limit 10";
  $res = $db->query($sql);

  while ($e = $res->fetch_row()) {
    $ev[] = $e[0];
    switch($e[1]) {
      default:
      case 0:
        $typ[] = 'desktop';
        break;
      case 1:
        $typ[] = 'mobile';
        break;
      case 9:
        $typ[] = 'robot';
        break;
    }
  } 

  $results = array(
    "session" => anon($l),
    "events" => $ev,
    "type" => $typ
  );
  echo json_encode($results);
}

// ----------------- 30 DAYS ------------------
function thirtyStats($l) {
	// last 30 days stats
	global $db, $log_table;

  if($l){
    // specific month
    $month = substr($l, -2);
    $year = substr($l, 0, 4);
    $da = "month(upd) = '{$month}' and year(upd) = '{$year}'";
    $da = "upd like '{$year}-{$month}%'";
  } else {
    // last 30 days
    $t = explode(' ', date("YYYY MM", strtotime('-30 days')));
    $year = $t[0];
    $month = $t[1];
    $da = "upd >= date_sub(now(),interval 30 day)";
  }

	$sql = "select date(upd) as d, count(distinct INET6_NTOA(src)) as u, count(*) as c from {$log_table}
					where ${da} and acc != 9 group by d order by d desc";
	$res = $db->query($sql);
	while ($e = $res->fetch_row()) {
    $d[] = $e[0];
		$s[] = $e[1];
		$c[] = $e[2];
		$chart[$e[0]] = $e[1];
	}

  $n = ($l)? cal_days_in_month(CAL_GREGORIAN, $month, $year) : 30; 
  if($l) {
    for ($i = 1; $i <= $n; $i++) {
      $dd =  date("D j-M", strtotime("${year}-${month}-${i}"));
      $days[$dd] = 0;
    }
  } else {
    for ($i = $n; $i >= 0; $i--) {
      $dd =  date("D j-M", strtotime("-${i} days"));
      $days[$dd] = 0;
    }
  }

	foreach ($chart as $k => $v) {
		$dd = date("D j-M", strtotime($k));
		$days[$dd] = $chart[$k];
	}

  $results = array(
    "date" => $d,
    "sessions" => $s,
    "hits" => $c,
    "chart" => $days
  );
  echo json_encode($results);
}

// ----------------- last 24 hrs or given date -----------------
function twentyfourStats($l) {
  // last 24 hours stats
  global $db, $log_table;

  $dq = ($l) ? "date(upd) = '{$l}'" : "upd >= date_sub(now(),interval 23 hour)";
  $sql = "select INET6_NTOA(src) as ip, count(*) as cnt, max(upd) as u from {$log_table} 
				where {$dq} and acc != 9 group by ip order by u desc";
  $res = $db->query($sql);
  $n = $res->num_rows;
  if (!$n) {
    echo "{}";
    return;
  }

  for ($i = 23; $i >= 0; $i--) {
    $hh = date("H", strtotime('-' . $i . ' hours'));
    $chart['h'.$hh] = 0;
  }

  while ($e = $res->fetch_row()) {
    $ips[] = $e[0];
    $c[] = $e[1];
    $upd[] = $e[2];
    $ddd = substr($e[2], 11, 2);
    $chart['h'.$ddd] += $e[1];
  }
  $last = deriveDay(strtotime($upd[0]));
 
  $results = array(
      "last" => $last,
      "ips" => $ips,
      "hits"=> $c,
      "times" => $upd,
      "chart" => $chart
  );
  echo json_encode($results);
}

// ---------------- utility functions -------------------
function anon($ip) {
  global $log_table;
  if ($log_table == 'logbackup') return $ip;

  if($ip == "::1") return 1;

  $hex = strstr($ip, ":");
  $s = preg_split("/[\:\.]/", $ip);
  $m = 0;
  for ($i = 0; $i < 4; $i++) {
    $a = ($hex) ? hexdec($s[$i]) : $s[$i];
    $m += (int)$a + ($i * 255);
  }
  return $m;
}

function deriveDay($d) {
  $last_midnight = strtotime('today midnight');
  if ($d > $last_midnight) {
    return strftime("today %A", $d);
  }
  if ($d > $last_midnight - 86400) {
    return strftime("yesterday %A", $d);
  }
  if ($d > $last_midnight - (86400 * 7)) {
    return strftime("%a, %d %h", $d);
  }

  return strftime("%Y-%m-%d %T", $d);
}
