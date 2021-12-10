<?php
mb_internal_encoding("UTF-8");
date_default_timezone_set ("Europe/Amsterdam");
setlocale(LC_TIME, 'en_NL');

// App wide constants, these will get overwritten
define('STATS_DB_DEV_INI', "../private/inspira_stats.ini");
define('STATS_DB_PROD_INI', "../../inspira.ini");
define('STATS_SITE', "inspiratree.com");

// Don't change beyond here
define('STATS_LOCK', true);

// Connect to the database
require_once('db.php');
$db = db_connect();
