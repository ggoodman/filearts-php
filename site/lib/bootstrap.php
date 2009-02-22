<?php

define ('DEBUG', TRUE);
define ('SALT', md5(42));
if (!defined('SITE_DIR')) define ('SITE_DIR', realpath(dirname(__FILE__) . '/../public'));

require_once dirname(__FILE__) . '/../../filearts/filearts.php';

require 'forms.php';
require 'records.php';
require 'visitor.php';
require 'markdown.php';

handle_request();

?>