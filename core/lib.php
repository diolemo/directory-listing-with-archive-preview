<?php

$__able_lib_callbacks = array();

require_once('core/lib/functions.inc.php');
// require_once('core/lib/__interface.user.php');
// require_once('core/lib/blowfish.inc.php');
// require_once('core/lib/cookie.inc.php');
// require_once('core/lib/data_cache/memcache.inc.php');
require_once('core/lib/date.inc.php');
require_once('core/lib/feedback.inc.php');
// require_once('core/lib/mysql_db.inc.php');
require_once('core/lib/request.inc.php');
// require_once('core/lib/session.inc.php');
// require_once('core/lib/sqloader.inc.php');
require_once('core/lib/str.inc.php');
require_once('core/lib/upload.inc.php');
require_once('core/lib/url.inc.php');

for ($i = 0, $c = count($__able_lib_callbacks); $i < $c; $i++)
   call_user_func($__able_lib_callbacks[$i]);

require_once('core/local.lib.php');

