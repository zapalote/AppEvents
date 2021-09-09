<?php
// Connect to the database
require_once('config.php');
global $db, $log_table;
$log_table = ($_SERVER['REMOTE_ADDR'] == "::1") ? "logbackup" : "log";

//if(!isAllowed($_SERVER['REMOTE_ADDR'])) {
// die($_SERVER['REMOTE_ADDR']);
//}

preg_match('/(lex|db|ip|d|n|ua|e|30|u|md|m|bck|s|xip|pip)=?(.*)/', $_SERVER['QUERY_STRING'], $arg);
$q = ($arg) ? $arg[1] : "";
$l = ($arg) ? $arg[2] : "";

// dispatcher
switch ($q) {
  case 'lex':
    // lex stats
    require('stats/dbStats.php');
    lexStats($l);
    break;

  case 'db':
    // db stats
    require('stats/dbStats.php');
    dbStats();
    break;

  case 'bck':
    require('stats/dbStats.php');
    bckStats();
    break;

  case 'pip':
    require('stats/ipStats.php');
    ipPopup($l);
    break;

  case 'ip':
    // single ip stats
    require('stats/ipStats.php');
    ipStats($l);
    break;

  case 'ua':
    // mobile/desktop
    require('stats/uaStats.php');
    uaStats();
    break;

  case 'n':
    // list "not found" queries
    require('stats/notFound.php');
    notFound();
    break;

  case 'e':
    // edit stats
    require('stats/editStats.php');
    editStats($q);
    break;

  case '30':
    // sessions stats 30 days
    require('stats/thirtyStats.php');
    thirtyStats();
    break;

  case 'u':
    // user histogram 30 days
    require('stats/userStats.php');
    userStats();
    break;

  case 'm':
    // user stats per month
    require('stats/monthlyStats.php');
    monthlyStats();
    break;

  case 'md':
    // user stats per month details
    require('stats/monthDetailStats.php');
    monthDetailStats($l);
    break;

  case 's':
    // search stats
    require('stats/searchStats.php');
    searchStats();
    break;

  case 'xip':
    require('stats/dbStats.php');
    xipStats($l);
    break;

  case 'd':
  default:
    // user stats 24hrs
    require('stats/twentyfourStats.php');
    if(!twentyfourStats($q, $l)) {
      require('stats/thirtyStats.php');
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
  $now = time();
  if ($now - $d < 86400) {
    return strftime("today %A", $d);
  }
  if ($now - $d < 86400 * 2) {
    return strftime("yesterday %A", $d);
  }
  if ($now - $d < 86400 * 7) {
    return strftime("%a, %d %h", $d);
  }

  return strftime("%Y-%m-%d %T", $d);
}
