<?php

define ('DEBUG', TRUE);
define ('SALT', md5(42));
define ('SITE_DIR', realpath(dirname(__FILE__) . '/../public'));

require_once dirname(__FILE__) . '/../../filearts/filearts.php';

require 'records.php';
require 'visitor.php';

handle_request();

?>