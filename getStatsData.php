<?php
// Connect to the database
require_once('config.php');
global $db, $log_table;
$log_table = STATS_LOG_TABLE;

//if(!isAllowed($_SERVER['REMOTE_ADDR'])) {
// die($_SERVER['REMOTE_ADDR']);
//}

preg_match('/(lex|db|ip|d|n|ua|e|30|u|md|m|bck|s|xip|pip|ref)=?(.*)/', $_SERVER['QUERY_STRING'], $arg);
$q = ($arg) ? $arg[1] : "";
$l = ($arg) ? $arg[2] : "";

// dispatcher
switch ($q) {
  case 'lex':
    // lex stats
    require('dbStats.php');
    lexStats($l);
    break;

  case 'db':
    // db stats
    require('dbStats.php');
    dbStats();
    break;

  case 'bck':
    require('dbStats.php');
    bckStats();
    break;

  case 'pip':
    require('ipStats.php');
    ipPopup($l);
    break;

  case 'ip':
    // single ip stats
    require('ipStats.php');
    ipStats($l);
    break;

  case 'ua':
    // mobile/desktop
    require('uaStats.php');
    uaStats();
    break;

  case 'n':
    // list "not found" queries
    require('notFound.php');
    notFound();
    break;

  case 'e':
    // edit stats
    require('editStats.php');
    editStats($q);
    break;

  case '30':
    // sessions stats 30 days
    require('thirtyStats.php');
    thirtyStats();
    break;

  case 'u':
    // user histogram 30 days
    require('userStats.php');
    userStats();
    break;

  case 'm':
    // user stats per month
    require('monthlyStats.php');
    monthlyStats();
    break;

  case 'md':
    // user stats per month details
    require('monthDetailStats.php');
    monthDetailStats($l);
    break;

  case 's':
    // search stats
    require('searchStats.php');
    searchStats();
    break;

  case 'xip':
    require('dbStats.php');
    xipStats($l);
    break;

  case 'ref':
    require('referStats.php');
    referStats();
    break;

  case 'd':
  default:
    // user stats 24hrs
    require('twentyfourStats.php');
    if (!twentyfourStats($q, $l)) {
      require('thirtyStats.php');
      thirtyStats();
    }
}

$db->close();

// ---------------- utility functions -------------------
function anon($ip) {
  global $log_table;
  if ($log_table == 'logbackup') return $ip;

  $hex = strstr($ip, ":");
  $s = preg_split("/[\:\.]/", $ip);
  $m = 0;
  for ($i = 0; $i < 4; $i++) {
    $a = ($hex) ? hexdec($s[$i]) : $s[$i];
    $m += (int)$a + ($i * 255);
  }
  return $m;
}

function getUserIpAddr() {
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    //ip from share internet
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    //ip pass from proxy
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }
  return $ip;
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
