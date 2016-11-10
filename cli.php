<?php
define('IS_CLI', true);
define('IS_DEBUG', true);
define('IN_REGINX', true);
define("DS", DIRECTORY_SEPARATOR);
define('APP_NAME', 'default');
define('APP_PATH', realpath('./') . DS);
define('INC_PATH', realpath('./include') . DS);
define('BASE_PATH', realpath('./') . DS);
define('DATA_PATH', realpath('./data') . DS);
include ('./reginx/cli.php');
new reginx ();