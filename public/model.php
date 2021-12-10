<?php
// Connect to the database
require_once('config.php');
?>
<!DOCTYPE html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
</head>
<body>
<?php

$log_sql = "
		CREATE TABLE IF NOT EXISTS `{$log_table}` (
		`src` INT(10) UNSIGNED NOT NULL,
		`src6` binary(16) DEFAULT NULL,
		`lex` varchar(40) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
		`upd` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		`acc` tinyint NOT NULL DEFAULT 0,
		`refer` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NULL,
		`landing` varchar(100) CHARACTER SET utf8 COLLATE utf8_bin NULL,
		KEY (`src`),
		KEY (`upd`),
		KEY `src_6` (`src6`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

if($db->query($log_sql)) {
	echo "<p>{$log_table} table created.....</p>";
} else {
	echo "<p>Error defining log table: {$db->error}</p>";
}

$db->close();

?>
</body>
</html>