<?php

// Connect to the database
require_once('config.php');

// log table 
$log_sql = "
CREATE TABLE IF NOT EXISTS `log` (
	`src` INT(10) UNSIGNED NOT NULL,
	`src6` binary(16) DEFAULT NULL,
	`event` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
	`upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	`acc` tinyint NOT NULL DEFAULT 0,
	KEY (`src`),
	KEY (`upd`),
	KEY `src_6` (`src6`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
if($db->query($log_sql)) {
	echo "<p>log table created.....</p>";
} else {
	echo "<p>Error defining log table: {$db->error}</p>";
}

// monthly stats cache table
$stats_sql = "
CREATE TABLE IF NOT EXISTS `monthly_stats` (
        `month` char(10) NOT NULL,
        `sessions` INT NOT NULL,
        `queries` INT NOT NULL,
        `upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";
if ($db->query($stats_sql)) {
	echo "<p>monthly stats table created.....</p>";
} else {
	echo "<p>Error creating monthly stats table: {$db->error}</p>";
}

?>
