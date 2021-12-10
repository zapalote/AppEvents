<?php
if(!defined('STATS_LOCK')) {
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

function db_connect() {

    // define connection as a static, global variable, for re-use
    global $db, $log_table;

    // connect, if not yet done
    if(!isset($db)) {
        // Load config as an array
        if (file_exists(STATS_DB_PROD_INI)) {
            # PROD
            $cf = parse_ini_file(STATS_DB_PROD_INI);
        } else {
            # DEV
            $cf = parse_ini_file(STATS_DB_DEV_INI);
        }
        $db = new mysqli($cf['servername'], $cf['username'], $cf['password'], $cf['dbname']);

        // exit if on error
        if($db->connect_errno) {
            printf("<h2>Connect status: %s</h2>", $db->connect_error);
            exit();
        }
        $log_table = $cf['log_table'];
    }

    return $db;
}
?>
