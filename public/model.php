<?php
header('Content-Type: text/plain');

// Connect to the database
require_once('config.php');
global $db, $log_table;

$log_sql = "
		CREATE TABLE IF NOT EXISTS `{$log_table}` (
		`src` varbinary(16) NOT NULL,
		`lex` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		`upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`acc` tinyint NOT NULL DEFAULT 0,
		`refer` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NULL,
		`landing` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NULL,
		KEY (`src`),
		KEY (`upd`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

if($db->query($log_sql)) {
	echo "{$log_table} table created.....";
} else {
	echo "Error defining {$log_table} table: {$db->error}";
}

$db->close();

?>
