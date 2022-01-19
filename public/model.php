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
	echo "<p>{$log_table} table created.....</p>";
} else {
	echo "<p>Error defining log table: {$db->error}</p>";
}

$db->close();

// alter table log modify src varbinary(16) not null
// UPDATE log SET src = INET6_ATON(INET_NTOA(src)), upd=upd
// UPDATE log SET src = src6, upd=upd where src6 is not null
?>
</body>
</html>