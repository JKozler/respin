<?php

require_once __DIR__ . '/core.php';
require_once get_template_directory() . '/vendor/autoload.php'; // @TODO add to main script?

/** @return \Mioweb\Core\Core */
function core()
{
	return Core::getInstance()->getCore();
}

// @TODO detect core request when will be needed
//if (strpos($_SERVER['REQUEST_URI'], '/core/') !== false) {
//	core()->processRequest();
//	exit;
//}
