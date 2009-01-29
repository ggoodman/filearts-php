<?php

require_once '../../filearts/filearts.php';

function site_init($registry, $request, $response) {

	$registry->dba = database('mysql://root@localhost/filearts');	
}

?>