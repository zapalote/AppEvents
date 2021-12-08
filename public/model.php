<?php

// Connect to the database
require_once('config.php');
if (!defined('STATS_LOCK')) {
	header("HTTP/1.0 404 Not Found");
	echo '
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL /db.php was not found on this server.</p>
</body></html>';

	exit();
}

?>
<!DOCTYPE html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
</head>
<body>
<?php

$log_sql = "
		CREATE TABLE IF NOT EXISTS `log` (
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
	echo "<p>log table created.....</p>";
} else {
	echo "<p>Error defining log table: {$db->error}</p>";
}

$db->close();

?>
</body>
</html>